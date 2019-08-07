<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller;

use ILIAS\Plugin\Announcements\AccessControl\Exception\PermissionDenied;

/**
 * Class Subscription
 * @package ILIAS\Plugin\Announcements\Frontend\Controller
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Subscription extends Base
{
    /**
     * @inheritDoc
     */
    protected function init()
    {
        if (!$this->accessHandler->mayReadEntries()) {
            $this->errorHandler->raiseError($this->lng->txt('permission_denied'), $this->errorHandler->MESSAGE);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCommand() : string
    {
        return 'getRssChangeModalContentCmd';
    }

    /**
     * @return string
     */
    public function getRssChangeModalContentCmd() : string
    {
        return $this->renderModalDialogue(
            $this->getCoreController()->getPluginObject()->txt('rss_subscription_title'),
            $this->getCoreController()->getPluginObject()->txt('rss_subscription_info'),
            $this->getCoreController()->getPluginObject()->txt('label_rss_url')
        );
    }

    /**
     * @return string
     */
    public function getRssRoomChangeModalContentCmd() : string
    {
        return $this->renderModalDialogue(
            $this->getCoreController()->getPluginObject()->txt('rss_subscription_room_change_title'),
            $this->getCoreController()->getPluginObject()->txt('rss_subscription_room_change_info'),
            $this->getCoreController()->getPluginObject()->txt('label_rss_room_change_url'),
            [
                'room_change_related' => 1
            ]
        );
    }

    /**
     * @param string $title
     * @param string $info
     * @param string $urlLabel
     * @param array $urlParams
     * @return string
     * @throws PermissionDenied
     */
    private function renderModalDialogue(string $title, string $info, string $urlLabel, array $urlParams = []) : string
    {
        $rssToken = $this->service->getAuthenticationToken();

        $tpl = $this->getCoreController()->getPluginObject()->getTemplate('tpl.subscription_dialog.html');

        $tpl->setVariable('TXT_SUBSCRIPTION_INFO', $info);

        $url = implode('/', [
            ILIAS_HTTP_PATH,
            rtrim(ltrim($this->getCoreController()->getPluginObject()->getDirectory(), './'), '/'),
            'feed.php'
        ]);
        $urlParams = array_merge($urlParams, [
            'client_id' => CLIENT_ID,
            'usr_id' => $this->user->getId(),
            'hash' => $rssToken
        ]);
        foreach ($urlParams as $param => $value) {
            $url = \ilUtil::appendUrlParameterString($url, $param . '=' . $value);
        }

        $tpl->setVariable('TXT_PERMA', $urlLabel);
        $tpl->setVariable('LINK', $url);

        $modal = $this->uiFactory->modal()->roundtrip(
            $title,
            $this->uiFactory->legacy($tpl->get())
        );

        return $this->uiRenderer->render($modal);
    }
}