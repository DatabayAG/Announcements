<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\MajorEvents\Frontend\Controller;


use ILIAS\Plugin\Announcements\Administration\Controller\Base;

/**
 * Class News
 * @package ILIAS\Plugin\MajorEvents\Frontend\Controller
 */
class News extends Base
{
    /** @var array */
    protected $validMessages = [
        'saved_successfully'
    ];

    /**
     * @inheritDoc
     */
    protected function init()
    {
       
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCommand(): string
    {
        return 'showNewsForm';
    }


    /**
     * @return string
     */
    public function showMajorEventsListCmd(): string
    {
        
        $message = (string) ($this->request->getQueryParams()['message'] ?? '');
        if (strlen($message) > 0 && in_array($message, $this->validMessages)) {
            $htmlParts[] = $this->uiFactory->messageBox()->success($this->lng->txt('saved_successfully'));
        }

        $htmlParts[] = $this->uiFactory->legacy('gdfgsdfg dfsgsdfg');

        return $this->uiRenderer->render($htmlParts);
    }
}