<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ILIAS\Plugin\Announcements\AccessControl\Acl\Impl;
use ILIAS\Plugin\Announcements\AccessControl\Acl\Resource\GenericResource;
use ILIAS\Plugin\Announcements\AccessControl\Acl\Role\GenericRole;
use ILIAS\Plugin\Announcements\AccessControl\Acl\Role\Registry;
use ILIAS\Plugin\Announcements\AccessControl\Handler\Cached;
use ILIAS\Plugin\Announcements\AccessControl\Handler\RoleBased;
use ILIAS\Plugin\Announcements\Administration\GeneralSettings\Settings;
use ILIAS\Plugin\Announcements\Entry\Service;
use ILIAS\Plugin\Announcements\Storage\KeyValue\Session;

/**
 * Class ilAnnouncementsPlugin
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAnnouncementsPlugin extends ilUserInterfaceHookPlugin
{
    /** @var string */
    const CTYPE = 'Services';

    /** @var string */
    const CNAME = 'UIComponent';

    /** @var string */
    const SLOT_ID = 'uihk';

    /** @var string */
    const PNAME = 'Announcements';

    /** @var self */
    private static $instance = null;

    /** @var bool */
    protected static $initialized = false;

    /** @var bool[] */
    protected static $activePluginsCheckCache = [];

    /** @var ilPlugin[] */
    protected static $activePluginsCache = [];

    /**
     * @inheritdoc
     */
    public function getPluginName()
    {
        return self::PNAME;
    }

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->registerAutoloader();

        if (!self::$initialized) {
            self::$initialized = true;

            $GLOBALS['DIC']['plugin.announcements.kvstore'] = function (Container $c) {
                return new Session($this->getId());
            };

            $GLOBALS['DIC']['plugin.announcements.accessHandler'] = function (Container $c) {
                return new Cached(
                    new RoleBased(
                        $c->user(),
                        $c['plugin.announcements.settings'],
                        $c->rbac()->review(),
                        $c['plugin.announcements.acl']
                    )
                );
            };

            $GLOBALS['DIC']['plugin.announcements.service'] = function (Container $c) {
                return new Service(
                    $c->user(),
                    $c->database(),
                    $c['plugin.announcements.accessHandler']
                );
            };

            $GLOBALS['DIC']['plugin.announcements.settings'] = function (Container $c) {
                return new Settings(
                    new \ilSetting($this->getId()),
                    $c['plugin.announcements.acl']
                );
            };

            $GLOBALS['DIC']['plugin.announcements.acl'] = function (Container $c) {
                $acl = new Impl(new Registry());

                $acl
                    ->addRole(new GenericRole('reader'))
                    ->addRole(new GenericRole('creator'))
                    ->addRole(new GenericRole('manager'))
                    ->addResource(new GenericResource('entry'))
                    ->addResource(new GenericResource('list'))
                    ->allow('reader', 'list', 'read')
                    ->allow('creator', 'list', 'read')
                    ->allow('creator', 'list', 'readUnpublished')
                    ->allow('creator', 'list', 'readExpired')
                    ->allow('creator', 'entry', 'create')
                    ->allow('manager', 'list', 'read')
                    ->allow('manager', 'list', 'readUnpublished')
                    ->allow('manager', 'list', 'readExpired')
                    ->allow('manager', 'entry', 'create')
                    ->allow('manager', 'entry', 'modify')
                    ->allow('manager', 'entry', 'delete')
                    ->allow('manager', 'entry', 'makeSticky');

                return $acl;
            };
        }
    }

    /**
     * Registers the plugin autoloader
     */
    public function registerAutoloader()
    {
        require_once dirname(__FILE__) . '/../autoload.php';
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (null === self::$instance) {
            return self::$instance = ilPluginAdmin::getPluginObject(
                self::CTYPE,
                self::CNAME,
                self::SLOT_ID,
                self::PNAME
            );
        }

        return self::$instance;
    }

    /**
     * @param string $component
     * @param string $slot
     * @param string $plugin_class
     * @return bool
     */
    public function isPluginInstalled($component, $slot, $plugin_class) : bool
    {
        if (isset(self::$activePluginsCheckCache[$component][$slot][$plugin_class])) {
            return self::$activePluginsCheckCache[$component][$slot][$plugin_class];
        }

        foreach (
            $GLOBALS['ilPluginAdmin']->getActivePluginsForSlot(IL_COMP_SERVICE, $component, $slot) as $plugin_name
        ) {
            $plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, $component, $slot, $plugin_name);
            if (class_exists($plugin_class) && $plugin instanceof $plugin_class) {
                return (self::$activePluginsCheckCache[$component][$slot][$plugin_class] = true);
            }
        }

        return (self::$activePluginsCheckCache[$component][$slot][$plugin_class] = false);
    }

    /**
     * @param string $component
     * @param string $slot
     * @param string $plugin_class
     * @return ilPlugin
     * @throws ilException
     */
    public function getPlugin($component, $slot, $plugin_class) : ilPlugin
    {
        if (isset(self::$activePluginsCache[$component][$slot][$plugin_class])) {
            return self::$activePluginsCache[$component][$slot][$plugin_class];
        }

        foreach (
            $GLOBALS['ilPluginAdmin']->getActivePluginsForSlot(IL_COMP_SERVICE, $component, $slot) as $plugin_name
        ) {
            $plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, $component, $slot, $plugin_name);
            if (class_exists($plugin_class) && $plugin instanceof $plugin_class) {
                return (self::$activePluginsCache[$component][$slot][$plugin_class] = $plugin);
            }
        }

        throw new ilException($plugin_class . ' plugin not installed!');
    }

    /**
     * @inheritdoc
     */
    public function promoteGlobalScreenProvider() : AbstractStaticPluginMainMenuProvider
    {
        $this->includeClass('class.ilAnnouncementsGlobalScreenProviderPlugin.php');
        return new ilAnnouncementsGlobalScreenProviderPlugin($GLOBALS['DIC'], $this);
    }

    /**
     * @inheritDoc
     */
    protected function beforeUninstall()
    {
        global $DIC;

        if ($DIC->database()->tableExists('pl_announcements')) {
            $DIC->database()->dropTable('pl_announcements');
        }

        if ($DIC->database()->sequenceExists('pl_announcements')) {
            $DIC->database()->dropSequence('pl_announcements');
        }

        return parent::beforeUninstall();
    }
}
