<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleXml extends JFormRule
{
    /**
     * Method to check if given xml is valid xml
     *
     * @param SimpleXMLElement $element The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param mixed $value The form field value to validate.
     * @param null $group The field name group control value. This acts as as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     * @param JRegistry|null $input An optional JRegistry object with the entire data set to validate against the entire form.
     * @param JForm|null $form The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @throws Exception
     * @since   11.1
     */
    public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
    {
        try {
            $strXml = $this->strToXml($value);
            libxml_use_internal_errors(true);
            $valid = simplexml_load_string($strXml);
            if (is_object($valid)) {
                return true;
            }

            /*get errors in case of failed test*/
            $arrErrors = libxml_get_errors();
            $xmlErrors = '<ul>';
            foreach ($arrErrors as $v) {
                $xmlErrors .= '<li>' . htmlentities($this->xmlToStr($v->message)) . '</li>';
            }
            $xmlErrors .= '</ul>';
            $msgError = sprintf(Text::_('MOD_QLFORM_MSG_XMLINVALID'), $xmlErrors);
            throw new Exception($msgError);
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage());
            return false;
        }

    }

    /**
     * replace [ => {, ] => }
     * to generale valid xml
     * @param $str
     * @return array|string|string[]|null
     */
    function strToXml($str)
    {
        $str = preg_replace("/\[/", '<', $str);
        $str = preg_replace("/\]/", '>', $str);
        return $str;
    }

    /**
     * replace { => [, { => ]
     * to generale a string, that can be displayed in html without being mistaken as tags
     * @param $str
     * @return array|string|string[]|null
     */
    function xmlToStr($str)
    {
        $str = preg_replace("/\</", '[', $str);
        $str = preg_replace("/\>/", ']', $str);
        return $str;
    }
}
