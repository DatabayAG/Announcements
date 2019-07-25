<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Plugin\Announcements\AccessControl\Acl;
use ILIAS\Plugin\Announcements\Administration\Controller\Base;
use ILIAS\Plugin\Announcements\Administration\GeneralSettings\UI\Form;

/**
 * Class ilAnnouncementsConfigGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAnnouncementsConfigGUI extends Base
{
	/** @var Acl */
	private $acl;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(\ilAnnouncementsPlugin $plugin_object = null)
	{
		parent::__construct($plugin_object);

		$this->acl = $GLOBALS['DIC']['plugin.announcements.acl'];
	}

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
		$content = [];

		if (isset($this->request->getQueryParams()['saved'])) {
			$content[] = $this->uiRenderer->render(
				$this->uiFactory->messageBox()->success($this->lng->txt('saved_successfully'))
			);
		}

		$form = new Form($this->plugin_object, $this, $this->settings, $this->objectCache, $this->rbacReview, $this->acl);
		$content[] = $form->getHTML();

		$this->tpl->setContent(implode($content));
	}

	/**
	 *
	 */
	public function saveSettings()
	{
		$form = new Form($this->plugin_object, $this, $this->settings, $this->objectCache, $this->rbacReview, $this->acl);
		if ($form->saveObject()) {
			$this->ctrl->setParameter($this, 'saved', 1);
			$this->ctrl->redirect($this);
		}

		$this->tpl->setContent($form->getHTML());
	}
}