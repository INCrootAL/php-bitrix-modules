<?php

namespace Incrootal\Migration\Builders;

use Incrootal\Migration\AbstractBuilder;
use Incrootal\Migration\Locale;
use function BXClearCache;

class CacheCleanerBuilder extends AbstractBuilder
{
    protected function isBuilderEnabled()
    {
        return true;
    }

    protected function initialize()
    {
        $this->setTitle(Locale::getMessage('BUILDER_CacheCleaner1'));
        $this->setDescription(Locale::getMessage('BUILDER_CacheCleaner2'));
        $this->setGroup('Tools');
    }

    protected function execute()
    {
        if (BXClearCache(true)) {
            $this->outSuccess('Success');
        } else {
            $this->outError('Error');
        }
    }
}
