<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Administration\GeneralSettings;

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

	/**
	 * Settings constructor.
	 * @param \ilSetting $settings
	 */
	public function __construct(\ilSetting $settings)
	{
		$this->settings = $settings;

		$this->read();
	}

	protected function read()
	{
		$this->rssChannelTitle       = (string) $this->settings->get('rss_channel_title', '');
		$this->rssChannelDescription = (string) $this->settings->get('rss_channel_desc', '');
	}

	/**
	 * @inheritdoc
	 */
	public function bindForm(\ilPropertyFormGUI $form)
	{
		$this->rssChannelTitle       = $form->getInput('rss_channel_title');
		$this->rssChannelDescription = $form->getInput('rss_channel_desc');
	}

	/**
	 * @return string
	 */
	public function getRssChannelTitle() : string
	{
		return $this->rssChannelTitle;
	}

	/**
	 * @param string $rssChannelTitle
	 */
	public function setRssChannelTitle(string $rssChannelTitle)
	{
		$this->rssChannelTitle = $rssChannelTitle;
	}

	/**
	 * @return string
	 */
	public function getRssChannelDescription() : string
	{
		return $this->rssChannelDescription;
	}

	/**
	 * @param string $rssChannelDescription
	 */
	public function setRssChannelDescription(string $rssChannelDescription)
	{
		$this->rssChannelDescription = $rssChannelDescription;
	}

	/**
	 * @inheritdoc
	 */
	public function onFormSaved() : void
	{
		$this->settings->set('rss_channel_title', $this->rssChannelTitle);
		$this->settings->set('rss_channel_desc', $this->rssChannelDescription);
	}

	/**
	 * @inheritdoc
	 */
	public function toArray() : array
	{
		return [
			'rss_channel_title' => $this->rssChannelTitle,
			'rss_channel_desc'  => $this->rssChannelDescription,
		];
	}
}