<?php

namespace Incrootal\Migration\Helpers;

use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Main\Localization\Loc;
use CBPWorkflowTemplateLoader;
use CLang;
use CModule;
use CSite;
use Exception;
use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Helper;
use Incrootal\Migration\Locale;
use Incrootal\Migration\Traits\HelperManagerTrait;

class WorkFlowTemplateHelper extends Helper
{
    use HelperManagerTrait;

    protected $userTypeEntityHelper;

    public function __construct()
    {
        parent::__construct();
        $this->userTypeEntityHelper = new UserTypeEntityHelper();
    }

    public function isEnabled()
    {
        return true;
    }

    public static function getList(array $select = ['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME', 'DESCRIPTION'], array $filter = []): array
    {
        $query = WorkflowTemplateTable::query()
            ->setSelect($select)
            ->setFilter($filter);

        $templatesResult = $query->exec();
        $groupedTemplates = [];

        while ($template = $templatesResult->fetch()) {
            $documentType = $template['DOCUMENT_TYPE'];
            if (!isset($groupedTemplates[$documentType])) {
                $groupedTemplates[$documentType] = [
                    'title' => $documentType,
                    'items' => []
                ];
            }
            $groupedTemplates[$documentType]['items'][] = [
                'title' => $template['NAME'],
                'value' => $template['ID']
            ];
        }

        return $groupedTemplates;
    }


    private function importBP($path)
    {
        CModule::IncludeModule('bizproc');
        CModule::IncludeModule('iblock');

        //Get iBlock id for which BP is created
        $this->arBPFields['DOCUMENT_TYPE'][2] .= $this->getIblockId();

        // Get BP id by the CODE
        $result = \CBPWorkflowTemplateLoader::GetList(
            [],
            [
                'CODE' => $this->arBPFields['CODE'],
                'MODULE_ID' => 'lists'
            ]
        );

        if ($arFields = $result->GetNext()) {
            $id = $arFields['ID'];
        } else {
            $id = 0;
        }

        //read file to a variable
        $f = fopen($path, 'rb');
        $datum = fread($f, filesize($path));
        fclose($f);

        //Update BP if id>0, otherwise add BP
        \CBPWorkflowTemplateLoader::ImportTemplate(
            $id,
            $this->arBPFields['DOCUMENT_TYPE'],
            $this->arBPFields['AUTO_EXECUTE'],
            $this->arBPFields['NAME'],
            '',
            $datum,
            $this->arBPFields['CODE']
        );

        return $arFields['ID'];
    }

    public static function exportBP($id)
    {
        CModule::IncludeModule('bizproc');
        CModule::IncludeModule('iblock');


        $result = \CBPWorkflowTemplateLoader::exportTemplate($id);
        $packer = new \Bitrix\Bizproc\Workflow\Template\Packer\Bpt();

        $unpackedResult = $packer->unpack($result);
        \Bitrix\Main\Diag\Debug::writeToFile(
            [$unpackedResult],
            'webForm',
            "/local/requests.log");

        return $unpackedResult;

    }

    public function commit()
    {

        $pathBPElement = __DIR__ . '/files/bp-94-approve-task.bpt';
        $id = $this->importBP($pathBPElement);
    }

    public function getIblockId()
    {

        $pathBPElement = __DIR__ . '/files/bp-94-approve-task.bpt';

        return $id = $this->importBP($pathBPElement);

    }

}
