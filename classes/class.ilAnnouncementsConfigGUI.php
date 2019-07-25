<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Plugin\Announcements\Administration\Controller\Base;
use ILIAS\Plugin\Announcements\Administration\GeneralSettings\UI\Form;

/**
 * Class ilAnnouncementsConfigGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAnnouncementsConfigGUI extends Base
{
	/**
	 * @inheritDoc
	 */
	protected function getDefaultCommand() : string
	{
		return 'showSettings';
	}

	/**
	 *
	 */
	public function showSettings()
	{
		$form = new Form($this->plugin_object, $this, $this->settings);
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 *
	 */
	public function saveSettings()
	{
		$form = new Form($this->plugin_object, $this, $this->settings);
		if ($form->saveObject()) {
			\ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
			$this->ctrl->redirect($this);
		}

		$this->tpl->setContent($form->getHTML());
	}
}