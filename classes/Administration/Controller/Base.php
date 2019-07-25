<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Administration\Controller;

use ILIAS\Plugin\Announcements\Administration\GeneralSettings\Settings;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Base
 * @package ILIAS\Plugin\Announcements\Administration\Controller
 * @author  Michael Jansen <mjansen@databay.de>
 */
abstract class Base extends \ilPluginConfigGUI
{
	/** @var Settings */
	protected $settings;

	/** @var \ilCtrl */
	protected $ctrl;

	/** @var \ilLanguage */
	protected $lng;

	/** @var \ilTemplate */
	protected $tpl;

	/** @var \ilObjUser */
	protected $user;

	/** @var \ILIAS\UI\Factory */
	protected $uiFactory;

	/** @var \ILIAS\UI\Renderer */
	protected $uiRenderer;
	
	/** @var ServerRequestInterface */
	protected $request;

	/** @var \ilAnnouncementsPlugin */
	protected $plugin_object;

	/** @var \ilRbacReview */
	protected $rbacReview;

	/** @var \ilObjectDataCache */
	protected $objectCache;

	/**
	 * Base constructor.
	 * @param \ilAnnouncementsPlugin $plugin_object
	 */
	public function __construct(\ilAnnouncementsPlugin $plugin_object = null)
	{
		global $DIC;

		$this->ctrl       = $DIC->ctrl();
		$this->lng        = $DIC->language();
		$this->tpl        = $DIC->ui()->mainTemplate();
		$this->user       = $DIC->user();
		$this->uiFactory  = $DIC->ui()->factory();
		$this->uiRenderer = $DIC->ui()->renderer();
		$this->request    = $DIC->http()->request();
		$this->rbacReview = $DIC->rbac()->review();
		$this->objectCache = $DIC['ilObjDataCache'];
		$this->settings   = $DIC['plugin.announcements.settings'];

		$this->plugin_object = $plugin_object;
	}

	/**
	 * @param string $cmd
	 */
	public function performCommand($cmd)
	{
		switch (true) {
			case method_exists($this, $cmd):
				$this->{$cmd}();
				break;

			default:
				$this->{$this->getDefaultCommand()}();
				break;
		}
	}

	/**
	 * @return string
	 */
	abstract protected function getDefaultCommand() : string;
}