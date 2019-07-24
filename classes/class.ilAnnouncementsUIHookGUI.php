<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @ilCtrl_isCalledBy ilAnnouncementsUIHookGUI: ilUIPluginRouterGUI
 */
class ilAnnouncementsUIHookGUI extends ilUIHookPluginGUI
{
	/** @var Container */
	protected $dic;

	/** @var ilCtrl */
	protected $ctrl;

	/** @var ilObjUser */
	protected $actor;

	/** @var \ILIAS\DI\HTTPServices */
	protected $http;

	/**
	 * ilAnnouncementsUIHookGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->dic = $DIC;

		$this->http = $DIC->http();
		$this->ctrl = $DIC->ctrl();
		$this->actor = $DIC->user();
	}

	/**
	 * 
	 */
	public function executeCommand()
	{
		if ($this->actor->isAnonymous() || 0 === (int) $this->actor->getId()) {
			$target = '';
			$entryId = (int) ($this->http->request()->getQueryParams()['entry_id'] ?? 0);
			if ($entryId > 0) {
				$target = '&target=announcements_' . $entryId;
			}
			$this->ctrl->redirectToURL('login.php?cmd=force_login&client_id=' . CLIENT_ID . $target);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getHTML($a_comp, $a_part, $a_par = [])
	{
		$unmodified = ['mode' => ilUIHookPluginGUI::KEEP, 'html' => ''];

		if ('Services/Utilities' === $a_comp && 'redirect' === $a_part) {
			$url = (string) ($a_par['html'] ?? '');
			$pluginDirectory = basename(dirname(__DIR__));
			if ('error.php' === basename($url) && strpos($url, $pluginDirectory) !== false) {
				$correctUrl = preg_replace(
					'/(.*?)\/(Customizing\/(.*?))(\/error.php)$/',
					'$1$4',
					$url
				);
				return ['mode' => ilUIHookPluginGUI::REPLACE, 'html' => $correctUrl];
			}
		}

		return $unmodified;
	}

	/**
	 * @inheritDoc
	 */
	public function checkGotoHook($a_target)
	{
		global $DIC;

		$parts = explode('_', $a_target);
		if ($parts[0] == 'announcements') {
			$_GET['baseClass'] = ilUIPluginRouterGUI::class;
			$DIC->ctrl()->setTargetScript('ilias.php');
			// TODO: Permanent links should be created with ilLink::_getLink($entry->getId(), 'announcements')
			if (isset($parts[1]) && is_numeric($parts[1])) {
				$DIC->ctrl()->setParameterByClass(self::class, 'entry_id', $parts[1]);
				$DIC->ctrl()->redirectByClass([
					ilUIPluginRouterGUI::class, self::class],
					'###A command for the detail view###' //TODO
				);
			}
		}

		return parent::checkGotoHook($a_target);
	}
}