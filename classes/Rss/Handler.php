<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Rss;

use ILIAS\DI\HTTPServices;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Plugin\Announcements\Administration\GeneralSettings\Settings;
use ILIAS\Plugin\Announcements\Entry\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Handler
 * @package ILIAS\Plugin\Announcements\Rss
 * @author Michael Jansen <mjansen@databay.de>         
 */
class Handler implements RequestHandlerInterface
{
	/** @var HTTPServices */
	private $httpServices;
	/**
	 * @var Service
	 */
	private $service;

	/** @var Settings */
	private $generalSettings;

	/**
	 * Exporter constructor.
	 * @param HTTPServices $httpServices
	 * @param Service      $service
	 * @param Settings     $generalSettings
	 */
	public function __construct(
		HTTPServices $httpServices,
		Service $service,
		Settings $generalSettings
	) {
		$this->httpServices = $httpServices;
		$this->service = $service;
		$this->generalSettings = $generalSettings;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	private function adjustUrl(string $url) : string 
	{
		$plugin = basename(dirname(__DIR__, 2));

		return preg_replace(
			'/(.*?)\/Customizing\/.*?(' . preg_quote($plugin) . '(\/.*?\.php.*?)?)$/',
			'$1$3',
			$url
		);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	private function entities(string $string) : string
	{
		return str_replace(
			['&', '<', '>'],
			['&amp;', '&lt;', '&lt;'],
			$string
		);
	}

	/**
	 * @inheritDoc
	 */
	public function handle(ServerRequestInterface $request) : ResponseInterface
	{
		$response = $this->httpServices->response();

		$hash = (string) ($request->getQueryParams()['hash'] ?? '');
		if (0 === strlen($hash)) {
			return $response->withStatus(401);
		}

		$usrId = (string) ($request->getQueryParams()['usr_id'] ?? '');
		if (!is_numeric($usrId) || $usrId < 0) {
			return $response->withStatus(401);
		}

		if (!\ilObjUser::_lookupLogin($usrId)) {
			return $response->withStatus(401);
		}

		$userHash = \ilObjUser::_lookupFeedHash($usrId);
		if ($userHash !== $hash) {
			return $response->withStatus(401);
		}

		$this->service = $this->service->withActor(new \ilObjUser($usrId));

		$rssTemplate = new \ilTemplate('tpl.rss_2_0.xml', true, true, 'Services/Feeds');
		$rssTemplate->setVariable('XML', 'xml');
		$rssTemplate->setVariable('CONTENT_ENCODING', 'UTF-8');
		$rssTemplate->setVariable('CHANNEL_TITLE', $this->entities($this->generalSettings->getRssChannelTitle()));
		$rssTemplate->setVariable('CHANNEL_DESCRIPTION', $this->entities($this->generalSettings->getRssChannelDescription()));
		$rssTemplate->setVariable('CHANNEL_LINK', $this->adjustUrl(\ilUtil::_getHttpPath()));

		$entries = $this->service->findAllValid();

		foreach ($entries as $entry) {
			$rssTemplate->setCurrentBlock('item');
			$rssTemplate->setVariable('ITEM_TITLE', $this->entities($entry->getTitle()));
			$rssTemplate->setVariable('ITEM_DESCRIPTION', $this->entities($entry->getContent()));
			$rssTemplate->setVariable('ITEM_LINK', $this->entities($this->adjustUrl(\ilLink::_getLink($entry->getId(), 'announcements'))));
			$rssTemplate->setVariable('ITEM_ABOUT', $this->entities($this->adjustUrl(\ilLink::_getLink($entry->getId(), 'announcements', [
				'il_about_feed' => $entry->getId()
			]))));
			$rssTemplate->setVariable('ITEM_DATE', $this->entities((new \DateTimeImmutable(
				'@' . $entry->getPublishTs(),
				new \DateTimeZone('UTC')
			))->format('r')));
			$rssTemplate->parseCurrentBlock();
		}

		$response = $response
			->withStatus(200)
			->withHeader('Content-Type', 'text/xml; charset=UTF-8;')
			->withBody($stream = Streams::ofString(
				$rssTemplate->get()
			));

		return $response;
	}
}