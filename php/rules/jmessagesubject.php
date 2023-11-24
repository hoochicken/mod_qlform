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
class JFormRuleJmessagesubject extends JFormRule
{
    /**
     * Method to test the email address and optionally check for uniqueness.
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
            if ('' == $value) {
                return true;
            }
            $arrFieldnames = explode(',', $value);
            $arrNotFound = [];
            $xml = $input->get('params')->xml;
            //print_r($arrFieldnames );die;
            foreach ($arrFieldnames as $k => $v) {
                $v = trim($v);
                $search = 'name="' . $v . '"';
                if (false === strpos($xml, $search)) {
                    $arrNotFound[] = $v;
                }
            }
            if (0 >= count($arrNotFound)) {
                return true;
            }
            throw new Exception(Text::sprintf('MOD_QLFORM_MSG_JMESSAGESUBJECT2FIELDNAMESMISSING', implode(', ', $arrNotFound)));
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage());
            return false;
        }
    }
}
