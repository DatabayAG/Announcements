<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAnnouncementsConfigGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAnnouncementsConfigGUI extends ilPluginConfigGUI
{
	public function performCommand($cmd)
	{
		global $DIC;

		$DIC->ui()->mainTemplate()->setContent('Hello World');
	}
}