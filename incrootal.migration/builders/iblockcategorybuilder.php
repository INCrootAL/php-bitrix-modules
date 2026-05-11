<?php

namespace Incrootal\Migration\Builders;

use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Exceptions\MigrationException;
use Incrootal\Migration\Exceptions\RebuildException;
use Incrootal\Migration\Locale;
use Incrootal\Migration\Module;
use Incrootal\Migration\VersionBuilder;

class IblockCategoryBuilder extends VersionBuilder
{
    /**
     * @return bool
     */
    protected function isBuilderEnabled()
    {
        return $this->getHelperManager()->Iblock()->isEnabled();
    }

    protected function initialize()
    {
        $this->setTitle(Locale::getMessage('BUILDER_IblockCategoryExport1'));
        $this->setDescription(Locale::getMessage('BUILDER_IblockCategoryExport2'));
        $this->setGroup('Iblock');

        $this->addVersionFields();
    }

    /**
     * @throws HelperException
     * @throws RebuildException
     * @throws MigrationException
     */
    protected function execute()
    {
        $helper = $this->getHelperManager();

        $iblockId = $this->addFieldAndReturn(
            'iblock_id',
            [
                'title'       => Locale::getMessage('BUILDER_IblockCategoryExport_IblockId'),
                'placeholder' => '',
                'width'       => 250,
                'items'       => $this->getHelperManager()->IblockExchange()->getIblocksStructure(),
            ]
        );

        $iblock = $helper->Iblock()->exportIblock($iblockId);
        if (empty($iblock)) {
            $this->rebuildField('iblock_id');
        }

        $sectionTree = $helper->Iblock()->exportSectionsTree($iblockId);

        $this->createVersionFile(
            Module::getModuleDir() . '/templates/IblockCategoryExport.php',
            [
                'iblock'      => $iblock,
                'sectionTree' => $sectionTree,
            ]
        );
    }
}
