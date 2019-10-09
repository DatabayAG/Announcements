<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Entry;

/**
 * Class Purifier
 * @package ILIAS\Plugin\Announcements\Entry
 * @author Michael Jansen <mjansen@databay.de>
 */
class Purifier extends \ilHtmlPurifierAbstractLibWrapper
{
    /**
     * ilHtmlForumPostPurifier constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getPurifierConfigInstance()
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', 'ilias announcment');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('Cache.SerializerPath', \ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');

        $allowElements = [
            'strong', 'em', 'u', 'ol', 'li', 'ul', 'p', 'div',
            'i', 'b', 'code', 'sup', 'sub', 'pre', 'strike',
        ];// see: \ilTextAreaInputGUI

        $allowElements = $this->makeElementListTinyMceCompliant($allowElements);
        $config->set('HTML.AllowedElements', $this->removeUnsupportedElements($allowElements));
        $config->set('HTML.ForbiddenAttributes', 'div@style');

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        return $config;
    }
}