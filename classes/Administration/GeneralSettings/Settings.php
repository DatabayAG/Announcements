<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Administration\GeneralSettings;

use ILIAS\Plugin\Announcements\AccessControl\Acl;
use ILIAS\Plugin\Announcements\UI\Form\Bindable;

/**
 * Class Settings
 * @package ILIAS\Plugin\Announcements\GeneralSettings
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Settings implements Bindable
{
	/** @var \ilSetting */
	private $settings;

	/** @var string */
	private $rssChannelTitle = '';

	/** @var string */
	private $rssChannelDescription = '';

	/** @var Acl */
	private $acl;

	/** @var array */
	protected $aclRoleToGlobalRoleMappings = [];

	/**
	 * Settings constructor.
	 * @param \ilSetting $settings
	 * @param Acl        $acl
	 */
	public function __construct(\ilSetting $settings, Acl $acl)
	{
		$this->settings = $settings;

		$this->read();
		$this->acl = $acl;
	}

	/**
	 *
	 */
	protected function read()
	{
		$this->rssChannelTitle             = (string) $this->settings->get('rss_channel_title', '');
		$this->rssChannelDescription       = (string) $this->settings->get('rss_channel_desc', '');
		$this->aclRoleToGlobalRoleMappings = unserialize(
			$this->settings->get('aclr_to_role_mapping', serialize([])),
			[
				'allowed_classes' => false
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function bindForm(\ilPropertyFormGUI $form)
	{
		$this->rssChannelTitle       = $form->getInput('rss_channel_title');
		$this->rssChannelDescription = $form->getInput('rss_channel_desc');

		$mappingByRole = [];
		foreach ($this->acl->getRoles() as $role) {
			$mapping                           = array_filter(array_map('intval',
				(array) $form->getInput('role_mapping_' . $role->getRoleId())
			));
			$mappingByRole[$role->getRoleId()] = $mapping;
		}
		$this->aclRoleToGlobalRoleMappings = $mappingByRole;
	}

	/**
	 * @return string
	 */
	public function getRssChannelTitle() : string
	{
		return $this->rssChannelTitle;
	}

	/**
	 * @return string
	 */
	public function getRssChannelDescription() : string
	{
		return $this->rssChannelDescription;
	}

	/**
	 * @return array
	 */
	public function getAclRoleToGlobalRoleMappings() : array
	{
		return $this->aclRoleToGlobalRoleMappings;
	}

	/**
	 * @inheritdoc
	 */
	public function onFormSaved()
	{
		$this->settings->set('rss_channel_title', $this->rssChannelTitle);
		$this->settings->set('rss_channel_desc', $this->rssChannelDescription);
		$this->settings->set('aclr_to_role_mapping', serialize($this->aclRoleToGlobalRoleMappings));
	}

	/**
	 * @inheritdoc
	 */
	public function toArray() : array
	{
		$data = [
			'rss_channel_title' => $this->rssChannelTitle,
			'rss_channel_desc'  => $this->rssChannelDescription,
		];

		foreach ($this->acl->getRoles() as $role) {
			$data['role_mapping_' . $role->getRoleId()] = [];
			if (isset($this->aclRoleToGlobalRoleMappings[$role->getRoleId()])) {
				$data['role_mapping_' . $role->getRoleId()] = $this->aclRoleToGlobalRoleMappings[$role->getRoleId()];
			}
		}

		return $data;
	}
}