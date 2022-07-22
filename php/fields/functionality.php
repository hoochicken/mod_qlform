<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2022 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.html.html');
//import the necessary class definition for formfield
jimport('joomla.form.formfield');

class JFormFieldFunctionality extends JFormField
{
    protected function getInput()
    {
        $input = JFactory::getApplication()->input;
        $params = $this->getModuleData($input->get('id'), 'params');

        $params = json_decode($params);

        $arr_actions = array
        (
            'fileemail_enabled' => false,
            'fileupload_enabled' => false,
            'todoEmail' => false,
            'todoDatabase' => false,
            'todoDatabaseExternal' => false,
            'todoJmessage' => false,
            'todoSomethingElse' => 'modQlformSomethingElse',
            'todoSomethingCompletelyDifferent' => 'modQlformSomethingCompletelyDifferent',
            'validate' => 'modQlformValidation',
            'processData' => 'modQlformPreprocessData',
        );
        $used = array();
        $filesNeeded = array();
        foreach ($arr_actions as $k => $v) if (isset($params->$k) && (('validate' != $k && 1 == $params->$k) || ('validate' == $k && (2 == $params->$k || 3 == $params->$k)))) {
            $used[] = JText::_('MOD_QLFORM_' . strtoupper(str_replace('_enabled', '', $k)) . '_LABEL');
            if (false == $v) continue;
            $file = 'modules/mod_qlform/php/classes/' . $v . '.php';
            if (!file_exists(JPATH_ROOT . '/' . $file)) $filesNeeded[$k] = $file;
        }
        foreach ($filesNeeded as $k => $v) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('MOD_QLFORM_MSG_FUNCTIONALITY_FILESNEEDED', $k, $v), 'error');
        }
        //else echo '<br />'.$v.' is no functionality';
        if (0 >= count($used)) $msg = JText::_('MOD_QLFORM_FUNCTIONALITY_UNUSED');
        else {
            $strUl = '<ul>';
            foreach ($used as $k => $v) $strUl .= '<li>' . $v . '</li>';
            $strUl .= '</ul>';
            $msg = sprintf(JText::_('MOD_QLFORM_FUNCTIONALITY_USAGES'), count($used), $strUl);
        }
        return $msg;
    }

    function getModuleData($id, $selector = '*')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($selector);
        $query->from('`#__modules`');
        $query->where('`id`=\'' . $id . '\'');
        $db->setQuery($query);
        $data = $db->loadObject();
        if ('*' == $selector || !isset($data->$selector)) return $data;
        return $data->$selector;
    }

    function checkIfFileExists()
    {

    }

}