<?php

namespace Incrootal\Migration\Builders;

use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Exceptions\MigrationException;
use Incrootal\Migration\Exceptions\RebuildException;
use Incrootal\Migration\Exceptions\RestartException;
use Incrootal\Migration\Locale;
use Incrootal\Migration\Module;
use Incrootal\Migration\VersionBuilder;

class MedialibElementsBuilder extends VersionBuilder
{
    /**
     * @return bool
     */
    protected function isBuilderEnabled()
    {
        return (!Locale::isWin1251() && $this->getHelperManager()->MedialibExchange()->isEnabled());
    }

    protected function initialize()
    {
        $this->setTitle(Locale::getMessage('BUILDER_MedialibElements1'));
        $this->setDescription(Locale::getMessage('BUILDER_MedialibElements2'));
        $this->setGroup('Medialib');

        $this->addVersionFields();
    }

    /**
     * @throws RebuildException
     * @throws HelperException
     * @throws MigrationException
     */
    protected function execute()
    {
        $medialibExchange = $this->getHelperManager()->MedialibExchange();
        $collectionIds = $this->addFieldAndReturn(
            'collection_id',
            [
                'title'       => Locale::getMessage('BUILDER_MedialibElements_CollectionId'),
                'placeholder' => '',
                'width'       => 250,
                'select'      => $medialibExchange->getCollectionStructure(
                    $medialibExchange::TYPE_IMAGE
                ),
                'multiple'    => true,
            ]
        );

        $this->getExchangeManager()
             ->MedialibElementsExport()
             ->setLimit(20)
             ->setCollectionIds($collectionIds)
             ->setExchangeFile(
                 $this->getVersionResourceFile(
                     $this->getVersionName(),
                     'medialib_elements.xml'
                 )
             )->execute();

        $this->createVersionFile(
            Module::getModuleDir() . '/templates/MedialibElementsExport.php'
        );
    }
}
