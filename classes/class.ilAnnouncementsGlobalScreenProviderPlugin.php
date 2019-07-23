<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;

/**
 * Class ilAnnouncementsGlobalScreenProviderPlugin
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAnnouncementsGlobalScreenProviderPlugin extends AbstractStaticPluginMainMenuProvider
{
	/**
	 * @inheritDoc
	 */
	public function getStaticTopItems() : array
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getStaticSubItems() : array
	{
		return [];
	}
}
