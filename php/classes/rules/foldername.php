<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2014 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleFoldername extends JFormRule
{
    /**
     * The regular expression to use in testing a form field value.
     *
     * @var    string
     * @since  11.1
     */

    /**
     * Method to test the email address and optionally check for uniqueness.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value. This acts as as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     * @param   JRegistry         $input    An optional JRegistry object with the entire data set to validate against the entire form.
     * @param   JForm             $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   11.1
     */
    public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
    {
        try
        {
            if(!preg_match('/^[a-zA-Z0-9_\-]*$/',$value)) throw new Exception(JText::_('MOD_QLFORM_MSG_FOLDERNAMEINVALID'));
            $config=JFactory::getConfig();
            $tmp=$config->get('tmp_path');
            $path=$tmp.'/'.$value;
            if (!is_dir($path) AND 0==$dirTrial=mkdir($path)) throw new Exception(JText::_('MOD_QLFORM_MSG_FOLDERMKDIRFAILURE'));
            return true;
        }
        catch (Exception $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
            return false;
        }
    }
}
