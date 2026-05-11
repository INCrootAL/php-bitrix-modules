<?php

namespace Incrootal\Migration\Helpers;

use Incrootal\Migration\Helper;
use Incrootal\Migration\Helpers\Traits\Iblock\IblockElementTrait;
use Incrootal\Migration\Helpers\Traits\Iblock\IblockFieldTrait;
use Incrootal\Migration\Helpers\Traits\Iblock\IblockPropertyTrait;
use Incrootal\Migration\Helpers\Traits\Iblock\IblockSectionTrait;
use Incrootal\Migration\Helpers\Traits\Iblock\IblockTrait;
use Incrootal\Migration\Helpers\Traits\Iblock\IblockTypeTrait;

class IblockHelper extends Helper
{
    use IblockPropertyTrait;
    use IblockFieldTrait;
    use IblockElementTrait;
    use IblockSectionTrait;
    use IblockTypeTrait;
    use IblockTrait;

    /**
     * IblockHelper constructor.
     */
    public function isEnabled()
    {
        return $this->checkModules(['iblock']);
    }

}
