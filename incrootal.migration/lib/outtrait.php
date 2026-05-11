<?php

namespace Incrootal\Migration;

use Throwable;

trait OutTrait
{

    protected function out($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'out'], func_get_args());
    }

    protected function outIf($cond, $msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outIf'], func_get_args());
    }

    protected function outProgress($msg, $val, $total)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outProgress'], func_get_args());
    }

    protected function outNotice($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outNotice'], func_get_args());
    }

    protected function outNoticeIf($cond, $msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outNoticeIf'], func_get_args());
    }

    protected function outInfo($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outInfo'], func_get_args());
    }

    protected function outInfoIf($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outInfoIf'], func_get_args());
    }

    protected function outSuccess($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outSuccess'], func_get_args());
    }

    protected function outSuccessIf($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outSuccessIf'], func_get_args());
    }

    protected function outWarning($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outWarning'], func_get_args());
    }

    protected function outWarningIf($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outWarningIf'], func_get_args());
    }

    protected function outError($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outError'], func_get_args());
    }

    protected function outErrorIf($msg, ...$vars)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outErrorIf'], func_get_args());
    }

    protected function outDiff($arr1, $arr2)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outDiff'], func_get_args());
    }

    protected function outDiffIf($cond, $arr1, $arr2)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outDiffIf'], func_get_args());
    }

    protected function outMessages($messages = [])
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outMessages'], func_get_args());
    }
    protected function outException(Throwable $exception)
    {
        call_user_func_array(['Incrootal\Migration\Out', 'outException'], func_get_args());
    }
}
