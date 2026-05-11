<?php

namespace Incrootal\Migration\Traits;

use Incrootal\Migration\HelperManager;

trait HelperManagerTrait
{
    public function getHelperManager()
    {
        return HelperManager::getInstance();
    }
}
