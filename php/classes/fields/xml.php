<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2014 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.html.html');
//import the necessary class definition for formfield
jimport('joomla.form.formfield');

class JFormFieldXml extends JFormField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since 1.6
     */
    protected $type = 'xml'; //the form field type see the name is the same

    /**
     * Method to retrieve the lists that resides in your application using the API.
     *
     * @return array The field option objects.
     * @since 1.6
     */
    protected function getInput()
    {
        $options = array();
        $html='';
        $html.='<textarea style="width:400px;height:400px;" name="'.$this->name.'" id="'.$this->id.'">';
        $html.=$this->value;
        $html.='</textarea>';
        return $html;
    }

}

?>