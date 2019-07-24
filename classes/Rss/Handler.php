<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Rss;

use ILIAS\DI\HTTPServices;
use ILIAS\Filesystem\Stream\Streams;
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

	/**
	 * Exporter constructor.
	 * @param HTTPServices $httpServices
	 * @param Service      $service
	 */
	public function __construct(
		HTTPServices $httpServices,
		Service $service
	) {
		$this->httpServices = $httpServices;
		$this->service = $service;
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

		$userHash = \ilObjUser::_lookupFeedHash($usrId);
		if ($userHash !== $hash) {
			return $response->withStatus(401);
		}

		$actor = new \ilObjUser($usrId);
		$this->service = $this->service->withActor($actor);

		$rssTemplate = new \ilTemplate('tpl.rss_2_0.xml', true, true, 'Services/Feeds');
		$rssTemplate->setVariable('XML', 'xml');
		$rssTemplate->setVariable('CONTENT_ENCODING', 'UTF-8');
		$rssTemplate->setVariable('CHANNEL_TITLE', 'ILIAS KH Freiburg'); // TODO maybe
		$rssTemplate->setVariable('CHANNEL_DESCRIPTION', 'ILIAS KH Freiburg'); // TODO maybe
		$rssTemplate->setVariable('CHANNEL_LINK', \ilUtil::_getHttpPath());

		$entries = $this->service->findAllValid();

		$formatter = function(string $string) {
			return str_replace(
				['&', '<', '>'],
				['&amp;', '&lt;', '&lt;'],
				$string
			);
		};

		foreach ($entries as $entry) {
			$rssTemplate->setCurrentBlock('item');
			$rssTemplate->setVariable('ITEM_TITLE', $formatter($entry->getTitle()));
			$rssTemplate->setVariable('ITEM_DESCRIPTION', $formatter($entry->getContent()));
			$rssTemplate->setVariable('ITEM_LINK', $formatter(\ilLink::_getLink($entry->getId(), 'announcements')));
			$rssTemplate->setVariable('ITEM_ABOUT', $formatter(\ilLink::_getLink($entry->getId(), 'announcements', [
				'&il_about_feed' => $entry->getId()
			])));
			// TODO: Use the published_ts instead
			$rssTemplate->setVariable('ITEM_DATE', $formatter((new \DateTimeImmutable(
				'@' . $entry->getCreatedTs(),
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