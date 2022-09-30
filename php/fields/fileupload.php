<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2022 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;
jimport('joomla.html.html');
//import the necessary class definition for formfield
jimport('joomla.form.formfield');

class JFormFieldFileupload extends JFormField
{
    protected function getInput()
    {
        try {
            $this->errors = [];

            $config = JFactory::getConfig();
            $input = JFactory::getApplication()->input;
            $params = json_decode($this->getModuleData($input->get('id'), 'params'));
            if (!is_object($params) || !isset($params->fileupload_enabled)) return $this->errors[] = JText::_('MOD_QLFORM_MSG_QLFORMCANTFINDITSPARAMS');;
            /*if deactivated anyway=>no checking*/
            if (0 == $params->fileupload_enabled && 0 == $params->fileemail_enabled) return;

            /*else check phpinfo anmd plugin stuff*/
            if (true != $this->checkPhpfileinfo()) $this->errors[] = array(JText::_('MOD_QLFORM_MSG_PHPEXTENSIONFILEINFONOTLOADED'), 'notice');
            if (true != $this->checkPluginQlform()) $this->errors[] = array(JText::_('MOD_QLFORM_MSG_PLGQLFORMREQUIRED'), 'warning');
            if (true != $this->checkPluginQlformEnabled()) $this->errors[] = array(JText::_('MOD_QLFORM_MSG_PLGQLFORMTOBEENABLED'), 'warning');
            if (0 < count($this->errors)) throw new Exception('');
        } catch (Exception $e) {
            foreach ($this->errors as $k => $v) {
                JFactory::getApplication()->enqueueMessage($v[0], $v[1]);
            }
            if (isset($this->plgSystemQlformInstalled) && false == $this->plgSystemQlformInstalled) echo '<br />' . JText::_('MOD_QLFORM_FILEUPLOADCOMMENT');
            return JText::sprintf('MOD_QLFORM_MSG_YOURTMPFOLDERIS', $config->get('tmp_path'));
        }
        return JText::sprintf('MOD_QLFORM_MSG_YOURTMPFOLDERIS', $config->get('tmp_path'));
    }

    function getModuleData($id, $selector = '*')
    {
        $db = ((int)JVERSION >= 4) ? Factory::getContainer()->get('DatabaseDriver') : JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($selector);
        $query->from('`#__modules`');
        $query->where('`id`=\'' . $id . '\'');
        $db->setQuery($query);
        $data = $db->loadObject();
        if ('*' === $selector || !isset($data->$selector)) return $data;
        return $data->$selector;
    }

    private function checkPhpfileinfo()
    {
        if (extension_loaded('fileinfo')) return true;
        return false;
    }

    private function checkPluginQlform()
    {
        $db = ((int)JVERSION >= 4) ? Factory::getContainer()->get('DatabaseDriver') : JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('`#__extensions`');
        //$query->where('`name`=\'qlformuploader\'');
        $query->where('`element`=\'qlformuploader\'');
        $db->setQuery($query);
        $this->plg_data = $db->loadObject();
        if (empty($this->plg_data)) {
            $this->plgSystemQlformInstalled = false;
            return false;
        }
        return true;
    }

    private function checkPluginQlformEnabled()
    {
        if (!isset($this->plg_data) || !isset($this->plg_data->enabled) || (isset($this->plg_data->enabled) && 1 != $this->plg_data->enabled)) return false;
        return true;
    }
}