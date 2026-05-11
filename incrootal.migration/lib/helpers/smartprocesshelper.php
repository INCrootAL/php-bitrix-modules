<?php

namespace Incrootal\Migration\Helpers;

use Bitrix\Bizproc\Copy\Implement\WorkflowTemplate;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Crm\StatusTable;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\UserTable;
use CBPWorkflowTemplateLoader;
use Exception;
use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Helper;
use Bitrix\Crm\Model\Dynamic\TypeTable;
use Incrootal\Migration\Traits\HelperManagerTrait;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\ORM\EntityError;


class SmartProcessHelper extends Helper
{
    use HelperManagerTrait;
    private string $prefixUserLogin = 'IMPORT_USER_LOGIN_';
    private string $prefixIblockCode = 'IMPORT_IBLOCK_CODE_';
    private string $prefixGroupCode = 'IMPORT_GROUP_CODE_';
    private string $prefixDepartmentCode = 'IMPORT_DEPARTMENT_CODE_';
    public function isEnabled()
    {
        return true;
    }


    protected $userTypeEntityHelper;

    public function __construct()
    {
        parent::__construct();
        $this->userTypeEntityHelper = new UserTypeEntityHelper();
    }

    public static function getTableName(): string
    {
        return 'b_crm_item_category';
    }

    /**
     * Экспортирует поля для указанного смарт-процесса
     *
     * @param int $smartProcessId
     *
     * @return array
     * @throws HelperException
     */
    public function exportFields($smartProcessId)
    {
        $this->getHelperManager();
        $allFields = $this->getHelperManager()->UserTypeEntity()->getList(['ENTITY_ID' => $smartProcessId]);

        $entities = [];
        foreach ($allFields as $fieldId) {
            $entity = $this->userTypeEntityHelper->exportUserTypeEntity($fieldId['ID']);
            if (!empty($entity)) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }
    /**
     * Функция для получения списка пользователей с заданными в параметрах префиксами.
     */

    public static function getUsersForImport(
        string $prefixUserId,
        string $prefixUserLogin,
        bool $groupById = true
    ): array {
        $query = UserTable::query();
        $query->addSelect('ID');
        $query->addSelect('LOGIN');
        $query->addFilter('ACTIVE', 'Y');
        $usersResult = $query->exec();
        $users = [];
        while ($user = $usersResult->fetch()) {
            if ($groupById) {
                $users[$prefixUserId . $user['ID']] = $prefixUserLogin . $user['LOGIN'];
            } else {
                $users[$prefixUserLogin . $user['LOGIN']] = $prefixUserId . $user['ID'];
            }
        }
        return $users;
    }
    public function getList(array $select = ['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME', 'DESCRIPTION'], array $filter = []): array
    {
        $query = WorkflowTemplateTable::query()
            ->setSelect($select)
            ->setFilter($filter);
        $templatesResult = $query->exec();

        return $templatesResult->FetchAll();
    }

    public function getWorkflowTemplates($entityTypeId): array
    {
        if (!Loader::includeModule('bizproc') || !Loader::includeModule('crm')) {
            return [];
        }
        new WorkflowTemplate();
        $documentType = 'DYNAMIC_'. $entityTypeId;

        $query = WorkflowTemplateTable::query()
            ->setSelect(['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME', 'DESCRIPTION','DOCUMENT_STATUS','AUTO_EXECUTE','NAME','TEMPLATE', 'PARAMETERS','VARIABLES','CONSTANTS',
                'MODIFIED','IS_MODIFIED','USER_ID','SYSTEM_CODE','ACTIVE','ORIGINATOR_ID','ORIGIN_ID','IS_SYSTEM','SORT'])
            ->setFilter(['=DOCUMENT_TYPE' => $documentType]);
        $templatesResult = $query->exec();
        \Bitrix\Main\Diag\Debug::writeToFile(
            $templatesResult->fetchAll(),
            'webForm',
            "/local/requests.log");
        return $templatesResult->fetchAll();
    }

    /**
     * Функция возвращает описание шаблона бп для экспорта по идентификатору шаблона.
     *
     * @throws Exception
     */
    public function exportWorkflowTemplateEntity(int $templateId): array
    {
        // метод получает массив всех замен, которые были определены во вспомогательных методах
        $this->replace = $this->getParamsForReplace(true);
        // получение шаблона БП
        $exportTemplate = CBPWorkflowTemplateLoader::exportTemplate($templateId);
        // приведение шаблона БП к массиву
        $data = unserialize(gzuncompress($exportTemplate));

        // рекурсивный обход массива с заменой переменных с идентификаторами переменными с символьными кодами
        return $this->recursivelyVariableReplace(
            [
                'templateParams' => $this->getInfoAboutTemplateById($templateId),
                'templateData' => $data,
            ]
        );
    }

    /**
     * Метод импортирует шаблон БП по описанию его шаблона.
     */
    public function importWorkflowTemplate(array $template): void
    {
        // определение параметров для замены
        $this->replace = $this->getParamsForReplace(false);
        // рекурсивная замена символьных кодов идентификаторами
        $template = $this->recursivelyVariableReplace($template, false);
        // перевод шаблона БП из массива в формат импорта
        $templateData = gzcompress(serialize($template['templateData']));
        // получение шаблона по его параметрам
        $templateInfo = $this->getInfoAboutTemplateByParams($template['templateParams']);

        // Если такой шаблон уже существует, то перезаписать его
        if ($templateInfo) {
            CBPWorkflowTemplateLoader::importTemplate(
                $templateInfo['ID'],
                $templateInfo['DOCUMENT_TYPE'],
                $templateInfo['AUTO_EXECUTE'],
                $templateInfo['NAME'],
                $template['templateParams']['DESCRIPTION'],
                $templateData,
            );
        } else { // иначе создать новый шаблон
            CBPWorkflowTemplateLoader::importTemplate(
                0, // для создания новой сделки необходимо в параметр id отправить 0
                $template['templateParams']['DOCUMENT_TYPE'],
                $template['templateParams']['AUTO_EXECUTE'],
                $template['templateParams']['NAME'],
                $template['templateParams']['DESCRIPTION'],
                $templateData,
            );
        }
    }

    /**
     * Функция для обхода ДФСом массива с шаблоном БП и замены жестко заданных переменных.
     *
     * @throws Exception
     */
    public function recursivelyVariableReplace(array $data, bool $isExport = true): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->recursivelyVariableReplace($value, $isExport);
            } elseif (is_string($value)) {
                $data[$key] = $this->replaceAll($value);
            }
        }

        // Для активити с запуском шаблона БП сделаем замену
        if ($data['Type'] === 'StartWorkflowActivity') {
            if ($isExport) {
                $data['Properties']['IMPORT_WORKFLOW_TEMPLATE'] =
                    $this->getInfoAboutTemplateById($data['Properties']['TemplateId']);
            } else {
                $templateParams = $this->getInfoAboutTemplateByParams($data['Properties']['IMPORT_WORKFLOW_TEMPLATE']);
                if (!$templateParams) {
                    throw new Exception(
                        Loc::getMessage('WORKFLOW_TEMPLATE_ERROR_VARIABLE_NOT_FOUND')
                        . json_encode($data['Properties']['IMPORT_WORKFLOW_TEMPLATE'])
                    );
                }
                $data['Properties']['TemplateId'] = $templateParams['ID'];
                $data['Properties']['IMPORT_WORKFLOW_TEMPLATE'] = null;
                unset($data['Properties']['IMPORT_WORKFLOW_TEMPLATE']);
            }
        }

        return $data;
    }

    //TODO Метод для экспорта прав группы
    public function exportGroupPermissions($smartProcessId)
    {
        // Здесь должна быть логика для экспорта прав группы смарт-процесса.
        // Ниже приведен пример, вам нужно заменить его на реальную логику экспорта.
        return [
            // Пример данных
            ['GROUP_ID' => 1, 'PERMISSION' => 'R'],
            ['GROUP_ID' => 2, 'PERMISSION' => 'W'],

        ];
    }

    public function createSmartProcess($typeData)
    {
        Loader::includeModule('crm');

        $result = TypeTable::add($typeData);
        if (!$result->isSuccess()) {
            $errors = $result->getErrorMessages();
            echo "Ошибка создания смарт процесса: " . implode(", ", $errors);
            return;
        }

        $typeId = $result->getId();

        return [
            'id' => $typeId,
            'name' => $typeData['NAME'],
        ];
    }
    public function getSmartProcessesStructure()
    {
        $params = [
            'select' => ['ID', 'TITLE', 'ENTITY_TYPE_ID', 'CODE'],
            'order' => ['ID' => 'ASC']
        ];

        $result = TypeTable::getList($params);
        $res = [];

        while ($row = $result->fetch()) {
            if (empty($row['CODE'])) {
                continue;
            }

            $res[] = [
                'title' => $row['TITLE'],
                'value' => $row['ID'],
                'ENTITY_TYPE_ID' => $row['ENTITY_TYPE_ID'],
            ];
        }

        return $res;
    }

    public function getSmartProcessesId( string $code)
    {
        $params = [
            'filter' => ['CODE' => $code],
            'select' => ['ID'],
        ];

        $result = TypeTable::getList($params);
        $res = [];

        while ($row = $result->fetch()) {
            $res = $row['ID'];
        }

        return $res;
    }

    public function getSmartProcessesEntityTypeId( string $code)
    {
        $params = [
            'filter' => ['CODE' => $code],
            'select' => ['ENTITY_TYPE_ID'],
        ];

        $result = TypeTable::getList($params);
        $res = [];

        while ($row = $result->fetch()) {
            $res = $row['ENTITY_TYPE_ID'];
        }

        return $res;
    }

    public function getSmartProcesses($smartProcessId)
    {
        $params = [
            'filter' => ['ID' => $smartProcessId],
            'select' => [
                'CODE',
                'NAME',
                'TITLE',
                'ENTITY_TYPE_ID',
                'IS_CATEGORIES_ENABLED',
                'IS_STAGES_ENABLED',
                'IS_BEGIN_CLOSE_DATES_ENABLED',
                'IS_CLIENT_ENABLED',
                'IS_LINK_WITH_PRODUCTS_ENABLED',
                'IS_CRM_TRACKING_ENABLED',
                'IS_MYCOMPANY_ENABLED',
                'IS_DOCUMENTS_ENABLED',
                'IS_SOURCE_ENABLED',
                'IS_USE_IN_USERFIELD_ENABLED',
                'IS_OBSERVERS_ENABLED',
                'IS_RECYCLEBIN_ENABLED',
                'IS_AUTOMATION_ENABLED',
                'IS_BIZ_PROC_ENABLED',
                'IS_SET_OPEN_PERMISSIONS',
                'IS_PAYMENTS_ENABLED',
                'IS_COUNTERS_ENABLED',
            ],
        ];

        $result = TypeTable::getList($params);
        $res = [];

        while ($row = $result->fetch()) {
            $res = $row;
        }

        return $res;
    }


    public function getSmartProcessesStatus($fields)
    {
        $entityIdPattern = 'DYNAMIC_' . $fields['ENTITY_TYPE_ID'] . '_STAGE_%';
        $params = [
            'filter' => ['ENTITY_ID' => $entityIdPattern],
            'select' => [
                'ENTITY_ID',
                'STATUS_ID',
                'NAME',
                'NAME_INIT',
                'SORT',
                'SYSTEM',
                'COLOR',
                'SEMANTICS',
                'CATEGORY_ID',
            ],
        ];

        $result = StatusTable::getList($params);
        $res = [];

        while ($row = $result->fetch()) {
            $res[] = $row;
        }
        \Bitrix\Main\Diag\Debug::writeToFile(
            $res,
            'webForm',
            "/local/requests.log");
        return $res;
    }

    public function getSmartProcessesTransferFunnels($fields)
    {
        $params = [
            'filter' => ['ENTITY_TYPE_ID' => $fields['ENTITY_TYPE_ID']],
            'select' => [
                'IS_DEFAULT',
                'IS_SYSTEM',
                'CODE',
                'NAME',
                'SORT',
                'SETTINGS',
                'ENTITY_TYPE_ID'
            ],
        ];

        $result = ItemCategoryTable::getList($params);
        $res = [];

        while ($row = $result->fetch()) {
            if ($row['NAME'] != 'Общая')
                $res[] = $row;
        }

        return $res;
    }

    public function addTunell($field)
    {
        try {
            Application::getConnection()->add(
                self::getTableName(),
                [
                    'CODE' => $field['CODE'],
                    'IS_SYSTEM' => $field['IS_SYSTEM'],
                    'ENTITY_TYPE_ID' => $field['ENTITY_TYPE_ID'],
                    'CREATED_DATE' => new DateTime(),
                    'NAME' => $field['NAME'],
                    'SORT' => $field['SORT'],
                    'SETTINGS' => Json::encode($field['SETTINGS']),
                ]
            );
        } catch (SqlQueryException $exception) {
            $result = new \Bitrix\Main\Result();
            $result->addError(new Error($exception->getMessage(), $exception->getCode()));
            return $result;
        }

        return true;
    }
    public function addStatus(array $fields): Result
    {
        $result = new Result();

        if (!isset($fields['ENTITY_ID'], $fields['STATUS_ID'], $fields['NAME'], $fields['SORT'])) {
            $result->addError(new EntityError(Loc::getMessage('CRM_STATUS_MISSING_REQUIRED_FIELDS')));
            return $result;
        }

        // Проверяем на наличие статуса с такой же SEMANTICS = 'SUCCESS' в данной ENTITY_ID
        if (isset($fields['SEMANTICS']) && $fields['SEMANTICS'] === 'S' && $this->isSuccessStatusExists($fields['ENTITY_ID'])) {
            $result->addError(new EntityError(Loc::getMessage('CRM_STATUS_MORE_THAN_ONE_SUCCESS_ERROR')));
            return $result;
        }

        // Добавляем новый статус
        $addResult = StatusTable::add($fields);
        if (!$addResult->isSuccess()) {
            $result->addErrors($addResult->getErrors());
            return $result;
        }

        return $result;
    }

    protected function isSuccessStatusExists(string $entityId): bool
    {
        $existingSuccessStatus = StatusTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=ENTITY_ID' => $entityId,
                '=SEMANTICS' => 'S',
            ],
            'limit' => 1,
        ])->fetch();

        return $existingSuccessStatus !== false;
    }


}