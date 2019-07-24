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

	/**
	 * ilKHFreiburgUIHookGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->dic = $DIC;
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
}