<?php
/**
 * @package        mod_form
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldAdminstyle extends JFormField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since 1.6
     */
    protected $type = 'adminstyle'; //the form field type see the name is the same

    /**
     * Method to retrieve the lists that resides in your application using the API.
     *
     * @return array The field option objects.
     * @since 1.6
     */
    protected function getInput()
    {

        if (0 == $this->getAttribute('value')) Factory::getDocument()->addStyleSheet(Uri::root() . '/modules/mod_qlform/css/adminBasic.css');
        elseif (1 == $this->getAttribute('value')) Factory::getDocument()->addStyleSheet(Uri::root() . '/modules/mod_qlform/css/adminPro.css');
        return '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '"/>';
    }
}