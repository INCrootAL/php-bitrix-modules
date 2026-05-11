<?php

namespace Incrootal\Migration;

use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Helpers\AgentHelper;
use Incrootal\Migration\Helpers\DeliveryServiceHelper;
use Incrootal\Migration\Helpers\EventHelper;
use Incrootal\Migration\Helpers\FormHelper;
use Incrootal\Migration\Helpers\HlblockExchangeHelper;
use Incrootal\Migration\Helpers\HlblockHelper;
use Incrootal\Migration\Helpers\IblockExchangeHelper;
use Incrootal\Migration\Helpers\IblockHelper;
use Incrootal\Migration\Helpers\LangHelper;
use Incrootal\Migration\Helpers\MedialibExchangeHelper;
use Incrootal\Migration\Helpers\MedialibHelper;
use Incrootal\Migration\Helpers\OptionHelper;
use Incrootal\Migration\Helpers\SiteHelper;
use Incrootal\Migration\Helpers\SmartProcessHelper;
use Incrootal\Migration\Helpers\SqlHelper;
use Incrootal\Migration\Helpers\UserGroupHelper;
use Incrootal\Migration\Helpers\UserOptionsHelper;
use Incrootal\Migration\Helpers\UserTypeEntityHelper;
use Incrootal\Migration\Helpers\WorkFlowTemplateHelper;

/**
 * @method IblockHelper             Iblock()
 * @method HlblockHelper            Hlblock()
 * @method AgentHelper              Agent()
 * @method EventHelper              Event()
 * @method LangHelper               Lang()
 * @method SiteHelper               Site()
 * @method UserOptionsHelper        UserOptions()
 * @method UserTypeEntityHelper     UserTypeEntity()
 * @method UserGroupHelper          UserGroup()
 * @method OptionHelper             Option()
 * @method FormHelper               Form()
 * @method DeliveryServiceHelper    DeliveryService()
 * @method SqlHelper                Sql()
 * @method MedialibHelper           Medialib()
 * @method MedialibExchangeHelper   MedialibExchange()
 * @method IblockExchangeHelper     IblockExchange()
 * @method HlblockExchangeHelper    HlblockExchange()
 * @method SmartProcessHelper       SmartProcess()
 * @method WorkFlowTemplateHelper   WorkFlowTemplate()
 * 
 */
class HelperManager
{
    private static $instance   = null;
    private array  $registered = [];
    private array  $cache      = [];

    public static function getInstance(): HelperManager
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @throws HelperException
     * @return Helper
     */
    public function __call($name, $arguments)
    {
        return $this->callHelper($name);
    }

    public function registerHelper($name, $class)
    {
        $this->registered[$name] = $class;
    }

    /**
     * @throws HelperException
     */
    protected function callHelper(string $name): Helper
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $default = '\\Incrootal\\Migration\\Helpers\\' . $name . 'Helper';

        $class = $this->registered[$name] ?? $default;

        if (class_exists($class)) {
            $ob = new $class;
            if ($ob instanceof Helper) {
                $this->cache[$class] = $ob;
                return $ob;
            }
        }

        throw new HelperException("Helper \"$name\" in \"$class\" not found");
    }
}
