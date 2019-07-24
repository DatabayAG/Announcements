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

		$body = "";

		$entries = $this->service->findAllValid();
		foreach ($entries as $entry) {
			// TODO: Write to RSS/XML
		}

		$response = $response
			->withStatus(200)
			->withHeader('Content-Type', 'text/xml; charset=UTF-8;')
			->withBody($stream = Streams::ofString(
				$body
			));

		return $response;
	}
}