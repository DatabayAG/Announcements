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

		return $unmodified;
	}
}