<?php

namespace Incrootal\Migration\Helpers;

use CLanguage;
use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Helper;
use Incrootal\Migration\Locale;

class LangHelper extends Helper
{

    /**
     * @throws HelperException
     * @return mixed
     */
    public function getDefaultLangIdIfExists()
    {
        $by = 'def';
        $order = 'desc';

        $item = CLanguage::GetList($by, $order, ['ACTIVE' => 'Y'])->Fetch();

        if ($item) {
            return $item['LID'];
        }

        throw new HelperException(
            Locale::getMessage(
                'ERR_DEFAULT_LANGUAGE_NOT_FOUND'
            )
        );
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getLangs($filter = [])
    {
        $by = 'def';
        $order = 'desc';

        $lids = [];
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $dbres = CLanguage::GetList($by, $order, $filter);
        while ($item = $dbres->Fetch()) {
            $lids[] = $item;
        }

        return $lids;
    }

    /**
     * @throws HelperException
     * @return array
     */
    public function getLangsIfExists()
    {
        $items = $this->getLangs(['ACTIVE' => 'Y']);
        if (!empty($items)) {
            return $items;
        }

        throw new HelperException(
            Locale::getMessage(
                'ERR_ACTIVE_LANGUAGES_NOT_FOUND'
            )
        );
    }
}
