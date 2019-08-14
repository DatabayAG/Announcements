<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\UI\Form;

/**
 * Interface Bindable
 * @package ILIAS\Plugin\Announcements\UI\Form
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Bindable
{
    /**
     * @param \ilPropertyFormGUI $form
     */
    public function bindForm(\ilPropertyFormGUI $form);

    /**
     *
     */
    public function onFormSaved();

    /**
     * A key value map of form values mapped to the respective element name
     * @return array
     */
    public function toArray() : array;
}