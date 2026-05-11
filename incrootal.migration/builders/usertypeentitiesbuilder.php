<?php

namespace Incrootal\Migration\Builders;

use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Exceptions\MigrationException;
use Incrootal\Migration\Exceptions\RebuildException;
use Incrootal\Migration\Locale;
use Incrootal\Migration\Module;
use Incrootal\Migration\VersionBuilder;

class UserTypeEntitiesBuilder extends VersionBuilder
{
    protected function isBuilderEnabled()
    {
        return true;
    }

    protected function initialize()
    {
        $this->setTitle(Locale::getMessage('BUILDER_UserTypeEntities1'));
        $this->setGroup('Main');

        $this->addVersionFields();
    }

    /**
     * @throws RebuildException
     * @throws HelperException
     * @throws MigrationException
     */
    protected function execute()
    {
        $helper = $this->getHelperManager();

        $allFields = $this->getHelperManager()->UserTypeEntity()->getList();

        $entityIds = $this->addFieldAndReturn(
            'entity_id',
            [
                'title'       => Locale::getMessage('BUILDER_UserTypeEntities_EntityIds'),
                'placeholder' => '',
                'width'       => 250,
                'select'      => $this->createSelect($allFields, 'ENTITY_ID', 'ENTITY_ID'),
                'multiple'    => 1,
                'value'       => [],
            ]
        );

        $selectFields = array_filter($allFields, function ($item) use ($entityIds) {
            return in_array($item['ENTITY_ID'], $entityIds);
        });
/*        \Bitrix\Main\Diag\Debug::writeToFile(
            [$selectFields,$entityIds, $allFields],
            'webForm',
            "/local/requests.log");*/
        $items = $this->addFieldAndReturn(
            'entity_fields',
            [
                'title'       => Locale::getMessage('BUILDER_UserTypeEntities_EntityFields'),
                'placeholder' => '',
                'width'       => 250,
                'multiple'    => 1,
                'items'       => $this->createSelectWithGroups($selectFields, 'ID', 'FIELD_NAME', 'ENTITY_ID'),
                'value'       => [],
            ]
        );

        $entities = [];
        foreach ($items as $fieldId) {
            $entity = $helper->UserTypeEntity()->exportUserTypeEntity($fieldId);
            if (!empty($entity)) {
                $entities[] = $entity;
            }
        }
        \Bitrix\Main\Diag\Debug::writeToFile(
            [$items],
            '$items',
            "/local/requests.log");
        $this->createVersionFile(
            Module::getModuleDir() . '/templates/UserTypeEntities.php',
            [
                'entities' => $entities,
            ]
        );
    }
}
