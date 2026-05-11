<?php

namespace Incrootal\Migration\Traits;

trait CurrentUserTrait
{
    public function getCurrentUserLogin(): string
    {
        if (isset($GLOBALS['USER'])) {
            return $GLOBALS['USER']->GetLogin();
        }
        return '';
    }

    public function getCurrentUserId(): string
    {
        if (isset($GLOBALS['USER'])) {
            return $GLOBALS['USER']->GetId();
        }
        return '';
    }
}
