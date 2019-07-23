<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;

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
}
