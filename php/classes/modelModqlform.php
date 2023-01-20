<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace QlformNamespace\Module\Qlform\Site\Helper;

use JModelForm;

defined('_JEXEC') or die;
jimport('joomla.application.component.modelform');

class modelModqlform extends JModelForm
{
    public string $str_xml;

    /**
     * Method for getting the form from the model.
     *
     * @param array $data Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @since   11.1
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        if (!isset($this->form_name)) $this->form_name = 'form' . md5(rand(0, 1000));
        $form = $this->loadForm($this->form_name, $this->str_xml, ['control' => $this->formControl, 'load_data' => false]);
        //echo '<pre>';print_r($form);die;
        if (empty($form)) return false;
        return $this->form = $form;
    }

    /**
     * Method to check data via jForm->validate
     *
     * @param array $data An array of data (post data) to be validated
     * @return    bool            true on success, false on failure
     * @since    1.6
     */
    function check($data)
    {
        $form = $this->getForm();
        if (1 == $form->validate($data) && 0 === count($form->getErrors())) return true;
        return false;
    }

    public function setFormControl($str)
    {
        $this->formControl = $str;
    }
}