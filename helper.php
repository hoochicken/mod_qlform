<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2022 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.form.form');

$arr_files = array('modelModqlform', 'modQlformCaptcha', 'modQlformMailer', 'modQlformDatabase', 'modQlformDatabaseExternal', 'modQlformMessager', 'modQlformSomethingElse', 'modQlformSomethingCompletelyDifferent', 'modQlformFiler', 'modQlformJmessages', 'modQlformValidation', 'modQlformPreprocessData',);
foreach ($arr_files as $k => $v) if (!class_exists($v) AND file_exists($file = dirname(__FILE__) . '/php/classes/' . $v . '.php')) require_once($file);

class modQlformHelper
{

    public $arrMessages = array();
    public $params;
    public $form;
    public $module;
    public $formControl;
    public $objDatabase;
    public $objDatabaseexternal;
    public $captchaToBeUsed;
    /** @var modelModqlform */
    public $obj_form;

    /**
     * constructor
     * @param object $params
     * @param $module
     */
    function __construct($params, $module)
    {
        $this->params = $params;
        $this->module = $module;
        $this->arrMessages = [];
    }


    /**
     * @return array|bool|false|string|null
     * @throws Exception
     */
    public static function recieveQlformAjax()
    {
        include_once(__DIR__ . '/mod_qlform.php');
    }


    /**
     * method to do something else,
     * that the developer of this module could never have guessed
     * @param object $data
     * @param object $module
     * @param $form
     * @return bool true on success, false on failure
     */
    function doSomethingElse($data, $module, $form)
    {
        if (false == $this->checkIfCustomExists('modQlformSomethingElse')) return false;
        $obj = new modQlformSomethingElse($data, $this->params, $module, $form);
        if (1 == $obj->doSomethingElse()) $this->arrMessages[] = array('warning' => 0, 'str' => JText::_('MOD_QLFORM_SOMETHINGELSEWORKEDOUTFINE'));
        else $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_SOMETHINGELSEDIDNOTWORK'));
    }

    /**
     * method to do something completely different,
     * @param $data
     * @param $module
     * @param $form
     * @return bool
     */
    function doSomethingCompletelyDifferent($data, $module, $form)
    {
        if (false == $this->checkIfCustomExists('modQlFormSomethingCompletelyDifferent')) return false;
        $obj = new modQlFormSomethingCompletelyDifferent($data, $this->params, $module, $form);
        if (1 == $obj->doSomethingCompletelyDifferent()) $this->arrMessages[] = array('warning' => 0, 'str' => JText::_('MOD_QLFORM_SOMETHINGCOMPLETELYDIFFERENTWORKEDOUTFINE'));
        else $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_SOMETHINGCOMPLETELYDIFFERENTDIDNOTWORK'));
    }

    /**
     * method to turn content vor array or object to string
     * that the developer of this module could never have guessed
     *
     * @param $data
     * @param string $type
     * @return false|string
     */
    function dump($data, $type = 'var_dump')
    {
        if ('var_dump' == $type) {
            ob_start();
            var_dump($data);
            $str_data = ob_get_contents();
            ob_end_clean();
        } elseif ('foreachstring' == $type) {
            $str_data = '';
            foreach ($data as $k => $v) {
                $str_data .= 'col[' . $k . ']=>' . $v . '<br />';
            }
        }
        return $str_data;
    }

    /**
     * method to transform string with [ and ] to xml with < and >
     *
     * @param string $str_content of param cell
     * @return string $str_xml
     */
    function transformText($str_content)
    {
        $str_xml = $str_content;
        $str_xml = preg_replace("/\[/", "<", $str_xml);
        $str_xml = preg_replace("/\]/", ">", $str_xml);
        return $str_xml;
    }

    /**
     * method to get and manipulize server data
     */
    function getServerData($ipSecure)
    {
        if (1 != $ipSecure) return $_SERVER;
        $this->arrServer = $_SERVER;
        $arrIp = preg_split('/\./', $_SERVER['REMOTE_ADDR']);
        unset($arrIp[count($arrIp) - 1]);
        unset($arrIp[count($arrIp) - 1]);
        $this->arrServer['REMOTE_ADDR'] = (implode('.', $arrIp) . '.x.x');
        return $this->arrServer;
    }

    /**
     *
     */
    function createAdditionalFields()
    {
        /*adding fields*/
        if (1 == $this->params->get('user_id', 0)) $this->arrFields[] = array('name' => 'user_id', 'type' => 'hidden', 'default' => $this->getUserData('id'),);
        if (1 == $this->params->get('user_email', 0)) $this->arrFields[] = array('name' => 'user_email', 'type' => 'hidden', 'default' => $this->getUserData('email'),);
        if (1 == $this->params->get('article_id', 0)) $this->arrFields[] = array('name' => 'article_id', 'type' => 'hidden', 'default' => $this->getArticleData('id'),);
        if (1 == $this->params->get('article_title', 0)) $this->arrFields[] = array('name' => 'article_title', 'type' => 'hidden', 'default' => $this->getArticleData('title'),);
        if (1 == $this->params->get('captcha2', 0)) $this->arrFields[] = array('name' => 'captcha', 'type' => 'captcha', 'label' => JText::_($this->params->get('captchalabel', 'MOD_QLFORM_CAPTCHA_LABEL')), 'validate' => 'captcha');
        if (1 != $this->params->get('todoSendcopy')) return;
        switch ($this->params->get('sendcopyType', 1)) {
            /*checkbox, unchecked*/
            case 1:
                $this->arrFields[] = array('name' => 'sendcopy', 'type' => 'checkbox', 'label' => JText::_('MOD_QLFORM_SENDCOPY_LABEL'), 'value' => '1',);
                break;
            /*checkbox checked*/
            case 2:
                $this->arrFields[] = array('name' => 'sendcopy', 'type' => 'checkbox', 'label' => JText::_('MOD_QLFORM_SENDCOPY_LABEL'), 'value' => '1', 'checked' => 'true',);
                break;
            /*hidden field, always*/
            case 3:
                $this->arrFields[] = array('name' => 'sendcopy', 'type' => 'hidden', 'default' => '1',);
                break;
        }
    }

    /**
     * method to add field to xml
     *
     * @param string $str_xml
     * @param string $arrFields array of fields to add
     * @param string $class string of fieldset class
     * @return string $str_xml
     */
    function addFieldsToXml($str_xml, $arrFields, $class = '')
    {
        if (is_array($arrFields)) {
            $formCloseTag = '</form>';
            $str_xml = str_replace($formCloseTag, '<fieldset class="' . $class . '" name="qlform' . md5(rand(0, 100)) . '">' . $formCloseTag, $str_xml);
            foreach ($arrFields as $k => $v) {
                $str_fieldtag = '<field ';
                foreach ($v as $k2 => $v2) $str_fieldtag .= $k2 . '="' . $v2 . '" ';
                $str_fieldtag .= ' />' . "\n";
                $str_xml = str_replace($formCloseTag, $str_fieldtag . $formCloseTag, $str_xml);
            }
            $str_xml = str_replace($formCloseTag, '</fieldset>' . $formCloseTag, $str_xml);
        }
        return $str_xml;
    }

    /**
     * method to get fields from current article
     * @param string $field
     * @return result field value
     * see http://forum.joomla.org/viewtopic.php?t=525350
     * @throws Exception
     */
    function getArticleData($field)
    {
        $app = JFactory::getApplication();
        $option = $app->input->getData('option');
        $view = $app->input->getData('view');
        $article_id = (string)$app->input->getData('id');
        if ($option == 'com_content' && $view == 'article') {
            $article = JTable::getInstance('content');
            $article->load($article_id);
            return $article->get($field);
        } else return false;
    }

    /**
     * method to get fields from current user
     * @param string $field
     * @return result field value
     */
    function getUserData($field)
    {
        $user = JFactory::getUser();
        if ("" != $user->get($field)) return $user->get($field);
        else return false;
    }

    /**
     * method to transform xml string to xml object
     * @param string $str_xml
     * @return object xml
     */
    function getXml($str_xml)
    {
        return simplexml_load_string($str_xml);
    }

    /**
     * Method to get the form based on str_xml.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array $str_xml An optional array of data for the form to interogate.
     * @return    mixed    $form        JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($str_xml, $id)
    {
        $this->obj_form = new modelModqlform();
        $this->obj_form->form_name = 'qlform' . $id;
        $this->obj_form->setFormControl($this->formControl);
        $this->obj_form->str_xml = $str_xml;
        $form = $this->obj_form->getForm();
        if (is_object($form)) return $this->form = $form;
        else $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_NO_FORM_GIVEN'));
        return false;
    }

    /**
     * Method to get the form based on str_xml.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array $data An array of data (post data) to be validated
     * @return    mixed    $form        JForm object on success, false on failure
     * @since    1.6
     */
    public function validate($data)
    {
        /*no validation due to params*/
        if (0 == $this->params->get('validate', 1)) return true;
        if (1 == $this->params->get('validate', 1) OR 3 == $this->params->get('validate', 1)) $validated = $this->obj_form->check($data);
        if (2 == $this->params->get('validate', 1) OR 3 == $this->params->get('validate', 1)) {
            if (false == $this->checkIfCustomExists('modQlformValidation')) return false;
            $obj_validator = new modQlformValidation($data, $this->params, $this->module, $this->form);
            $validatedCustom = $obj_validator->validate();
        }
        if ((isset($validated) AND false == $validated) OR (isset($validatedCustom) AND false == $validatedCustom)) {
            $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_VALIDATION_FAILED'));
            foreach ($this->form->getErrors() as $k => $v) $this->arrMessages[] = array('warning' => 1, 'str' => $v->getMessage());
            $validated = false;
        }
        return $validated;
    }

    private function checkIfCustomExists($str)
    {
        if (class_exists($str)) return true;
        $this->arrMessages[] = array('warning' => 1, 'str' => JText::sprintf('MOD_QLFORM_MSG_CLASSNOTFOUND', $str));
        return false;
    }

    /**
     * Method to raise Errors
     *
     * @since    1.6
     */
    public function raiseFormErrors()
    {
        if (isset($this->obj_form->formErrors) AND is_array($this->obj_form->formErrors))
            foreach ($this->obj_form->formErrors as $k => $v) $this->arrMessages[] = array('warning' => 1, 'str' => $v->getMessage());
    }

    /**
     * Method to raise Errors
     *
     * @param    int $type type of error displayed, either via joomla or text displayes
     * @since    1.6
     * @return
     */
    public function displayMessages($type)
    {
        $obj_messager = new modQlformMessager($this->arrMessages, $type);
        if (isset($obj_messager->message) AND !empty($obj_messager->message)) return $obj_messager->message;
    }

    /**
     * Method to raise Errors
     *
     * @param $strMessage
     * @return void
     * @since    1.6
     */
    public function setMessage($strMessage)
    {
        $this->arrMessages[] = ['str' => $strMessage];
    }

    /**
     * Method for checking database
     *
     * @param array $paramsDatabaseExternal
     * @return  mixed    array with table cols on success, false on failure
     */
    public function connectToDatabase($paramsDatabaseExternal = array())
    {
        if (0 == count($paramsDatabaseExternal)) {
            if (!class_exists('modQlformDatabase')) return false;
            $this->objDatabase = new modQlformDatabase();
        } else {
            if (!class_exists('modQlformDatabase') OR !class_exists('modQlformDatabaseExternal')) return false;
            $this->objDatabaseexternal = new modQlformDatabaseExternal($paramsDatabaseExternal);
        }
        return true;
    }

    /**
     * Method for saving data in database
     *
     * @param string $table Name of table to save data in
     * @param $data
     * @param array $paramsDatabaseExternal
     * @return  bool    true on success, false on failure
     */
    public function saveToDatabase($table, $data, $paramsDatabaseExternal = array())
    {
        $data = array_intersect_key($data, $this->arrTableFields);
        if (0 == count($paramsDatabaseExternal)) $this->objDatabase->save($table, $data);
        else $this->objDatabaseexternal->save($table, $data);;
        return true;
    }

    /**
     * Method for checking database
     *
     * @param $objDatabase
     * @param   string $table Name of table to save data in
     * @param $str_xml
     * @param $showErrors
     * @param $fieldCreated
     * @return  mixed    array with table cols on success, false on failure
     */
    public function checkDatabase($objDatabase, $table, $str_xml, $showErrors, $fieldCreated)
    {
        //echo "<pre>"; print_r($objDatabase->getDatabaseName());
        $mixCheckTable = $this->checkTableExists($objDatabase, $table, $showErrors);
        $this->compareTableToData($this->arrTableFields, $str_xml, $showErrors, $table, $fieldCreated, $objDatabase->getDatabaseName());
        return $mixCheckTable;
    }

    /**
     * Method for checking if table exists
     *
     * @param   string $table Name of table to save data in
     * @param   bool $showErrors If to raise Errors or let be
     *
     * @return  bool    truewith table cols on success, false on failure
     *
     */
    public function checkTableExists($objDatabase, $table, $showErrors)
    {
        $strDatabase = $objDatabase->getDatabaseName();
        if (false == $objDatabase->databaseExists($strDatabase)) {
            $this->arrMessages[] = array('warning' => 1, 'str' => sprintf(JText::_('MOD_QLFORM_DBNOTFOUND'), $strDatabase));
            return false;
        }
        $table = $objDatabase->getTableName($table);
        $tableExists = $objDatabase->tableExists($strDatabase, $table);

        $this->arrTableFields = array();
        if (false == $tableExists AND 1 == $showErrors) {
            $this->arrMessages[] = array('warning' => 1, 'str' => sprintf(JText::_('MOD_QLFORM_DBTABLENOTFOUND'), $table, $strDatabase));
        }
        if (false == $tableExists) return false;
        $arrDatabase = $objDatabase->getDatabaseFields($strDatabase, $table);
        $this->arrTableFields = $objDatabase->databaseFieldsObjectToArray($arrDatabase);
        return true;
    }

    /**
     * Method for checking if table exists
     *
     * @param   string $table Name of table to save data in
     * @param   bool $showErrors If to raise Errors or let be
     *
     * @return  bool    true on success, false on failure
     *
     */
    public function compareTableToData($arrTableFields, $str_xml, $showErrors, $table, $fieldCreated, $strDatabase)
    {
        $xml = $this->getXML($str_xml);
        $arrFormFields = array_flip($this->getFields($xml));
        if (1 == $fieldCreated) $arrFormFields['created'] = 0;
        $arrDifference1 = array_diff_key($arrFormFields, $arrTableFields);
        $arrDifference2 = array_diff_key($arrTableFields, $arrFormFields);
        $this->arrClean = array_intersect_key($arrFormFields, $arrTableFields);
        if (1 == $showErrors) {
            if (1 <= count($arrDifference1) OR 1 <= count($arrDifference2)) $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_DBFORM_ERROR_TITLE'));
            foreach ($arrDifference1 as $k => $v) $this->arrMessages[] = array('warning' => 1, 'str' => sprintf(JText::_('MOD_QLFORM_DBFORM_ERROR_DATABASE'), $k, $table, $strDatabase));
            foreach ($arrDifference2 as $k => $v) $this->arrMessages[] = array('warning' => 1, 'str' => sprintf(JText::_('MOD_QLFORM_DBFORM_ERROR_FORM'), $k, $table, $strDatabase));
            if (1 <= count($arrDifference1) OR 1 <= count($arrDifference2)) $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_DBFORM_ERROR_GENERAL'));
        }
        return true;
    }

    /**
     * Method for checking database
     *
     * @param $xml
     * @return  mixed    array with table cols on success, false on failure
     */
    public function getFields($xml)
    {
        if (is_object($xml->fieldset)) foreach ($xml->fieldset as $k => $v) {
            if (is_object($v->field)) foreach ($v->field as $k2 => $v2) {
                if (isset($v2['type']) AND 'spacer' != $v2['type']) $arr[] = (string)$v2['name'];
            }
        }
        return $arr;
    }

    /**
     * Method to mail
     *
     * @param string $recipient email address of recipient
     * @param string $subject of email
     * @param array $data array of post data to be sent
     * @param $form
     * @param string $pretext
     * @param int $labels
     * @param int $copy
     * @return bool
     * @since    1.6
     */
    public function mail($recipient, $subject, $data, $form, $pretext = '', $labels = 1, $copy = 0)
    {
        $paramsMail = $this->mailPrepareParams($data, $copy);
        $data = $this->prepareDataWithXml($data, $form, $labels);
        $obj_mailer = new modQlformMailer();
        if (2 == $this->params->get('emailseparator', '1')) $obj_mailer->separator = "\n";
        if (2 == $this->params->get('emailseparator2', '1')) $obj_mailer->separator2 = "\n\n";

        if ('' != trim($pretext)) $pretext = $this->preparePretext(JText::_($pretext), $data);
        if (1 == $this->params->get('fileemail_enabled', 0) AND isset($this->files)) $obj_mailer->files = $this->files;

        $subject .= $paramsMail['emailsubject2'];
        $mailSent = $obj_mailer->mail($recipient, $subject, $data, $paramsMail, $pretext, $this->params->get('emaildisplay'));
        foreach ($obj_mailer->arrMessages as $strMsg) {
            $this->arrMessages[] = ['str' => $strMsg];
        }
        if (1 == $mailSent) return true;
        else {
            $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_MAIL_SENT_ERROR'));
            return false;
        }
    }

    /**
     * Method to mail
     *
     * @param $data
     * @param object $form J! form object
     * @param int $labels boolean, if labels of form oder fireldnames shall be set
     * @return  array   $dataWithLabel
     */
    public function prepareDataWithXml($data, $form, $labels = 1)
    {
        $dataWithLabel = array();
        foreach ($data as $k => $v) {
            $label = $k;
            if ('' != $form->getLabel($k) AND 1 == $labels) $label = str_replace('&#160;', '', htmlspecialchars_decode(strip_tags($form->getLabel($k))));
            $dataWithLabel[$k]['name'] = $k;
            $dataWithLabel[$k]['label'] = trim($label);
            $data = $v;
            if (is_object($data) || is_array($data)) $data = json_encode($data);
            $dataWithLabel[$k]['data'] = JText::_((string)$data);
        }
        return $dataWithLabel;
    }

    /**
     * Method to mail
     *
     * @param object or array(?)    $data object with data inserted in form
     * @param int $copy
     * @return  array   $dataWithLabel
     */
    public function mailPrepareParams($data, $copy = 0)
    {
        $config = new JConfig();
        $arrMailParams = array();

        $arrMailParams['emailrecipient'] = $this->params->get('emailrecipient', $config->mailfrom);
        $arrMailParams['emailsubject'] = $this->params->get('emailsubject', $config->sitename);

        /*generate subject from field values*/
        $subjectGenerated = $this->params->get('emailsubject2', '');
        if ('' != trim($subjectGenerated)) {
            $arrSubjectGenerated = explode(',', $subjectGenerated);
            $subjectGenerated = '';
            foreach ($arrSubjectGenerated as $k => $v) if (isset($data[trim($v)])) {
                $dataForSubject = $data[trim($v)];
                if (is_array($dataForSubject) OR is_object($dataForSubject)) $subjectGenerated .= $this->params->get('emailsubjectseparator', '') . (string)json_encode($dataForSubject);
                else $subjectGenerated .= $this->params->get('emailsubjectseparator', '') . (string)$dataForSubject;
                unset($dataForSubject);
            }
        } else $subjectGenerated = '';
        $arrMailParams['emailsubject2'] = $subjectGenerated;

        /*set e-mail sender*/
        $emailSender = $this->params->get('emailsender', '');
        if ('' != trim($emailSender) AND isset($data[trim($emailSender)]) AND true == $this->checkEmail($data[$emailSender])) $emailSender = $data[$emailSender];
        else $emailSender = $config->mailfrom;
        $arrMailParams['emailsender'] = $emailSender;

        /*set replyTo*/
        if (0 == $copy) {
            $emailReplyTo = $this->params->get('emailreplyto', '');
            if ('' != trim($emailReplyTo) AND isset($data[$emailReplyTo]) AND true == $this->checkEmail($data[$emailReplyTo])) $emailReplyTo = $data[$emailReplyTo];
            else $emailReplyTo = $config->mailfrom;
            $arrMailParams['emailreplyto'] = $emailReplyTo;
        } else {
            $emailReplyTo = $this->params->get('sendcopyemailreplyto', '');
            if (true != $this->checkEmail($emailReplyTo)) $emailReplyTo = $config->mailfrom;
            $arrMailParams['emailreplyto'] = $emailReplyTo;
        }
        return $arrMailParams;
    }

    /**
     * Get captcha instance or null if not available
     *
     * @return  JCaptcha|null
     * @throws Exception
     */
    public function getCaptcha()
    {
        $plgn = $this->params->get('captcha', JFactory::getApplication()->get('captcha', '0'));
        $objCaptcha = JCaptcha::getInstance($plgn, array('namespace' => 'mod_qlform'));
        if (!$objCaptcha instanceof JCaptcha) $this->arrMessages[] = JText::_('MOD_QLFORM_MSG_CAPTCHANOTFOUND');
        return $objCaptcha;
    }

    public function captchaDefault()
    {
        $config = JFactory::getConfig();
        $objCaptchaToBeUsed = $objCaptchaConfig = $config->get('captcha');
        $showCaptcha = 0;
        if ('' != $objCaptchaConfig AND '' == $this->params->get('captcha', '0')) {
            $showCaptcha = 1;
            $objCaptchaToBeUsed = $objCaptchaConfig;
        } elseif ('' == $this->params->get('captcha', '0')) {
            $showCaptcha = 1;
            $objCaptchaToBeUsed = $objCaptchaConfig;
        } elseif ('0' !== $this->params->get('captcha', '0')) {
            $showCaptcha = 1;
            $objCaptchaToBeUsed = $this->params->get('captcha', '0');
        }
        $this->captchaToBeUsed = $objCaptchaToBeUsed;
        return $showCaptcha;
    }


    /**
     * Method to check if folder exists and generates it eventually
     */
    public function checkTmpQlform($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder);
            chmod($folder, 0755);
        }
        if (!is_file($folder . '/index.html')) touch($folder . '/index.html');
        $this->checkTmpQlformFiles($folder);
    }

    /**
     * Method to check for old files and to remove them
     */
    public function checkTmpQlformFiles($folder)
    {
        $handle = opendir($folder);
        while ($file = readdir($handle)) {
            if ('.' != $file AND '..' != $file AND 'index.html' != $file) {
                $arr = preg_split('?_?', $file);
                $dateFile = substr(array_pop($arr), 0, 6);
                if ($dateFile + 1 < date('ymd') AND file_exists($folder . '/' . $file)) unlink($folder . '/' . $file);
            }
        }
        closedir($handle);
    }

    /**
     * Method to check captcha
     *
     * @param int $type type of error displayed, either via joomla or text displayes
     * @return bool
     * @since    1.6
     */
    public function checkCaptcha($objCaptcha, $data)
    {
        if (!$objCaptcha instanceof JCaptcha) return false;
        if (!$objCaptcha->checkAnswer(isset($data['captcha']) ? $data['captcha'] : null)) {
            $this->arrMessages[] = array('str' => JText::_('MOD_QLFORM_MSG_CAPTCHAVALIDATIONFAILED'));
            return false;
        }
        return true;
    }

    /**
     * method to merge two subarray given
     *
     * @param array $array multidimensional array
     * @param string $index1 index of subarray to be merged
     * @param string $index2 index of second subarray to be merged
     * @return mixed $mergedArray array containing elements of former subarray on success, false on failure
     */
    function mergeSubarrays($array, $index1, $index2)
    {
        if (isset($array[$index1]) && is_array($array[$index1]) && isset($array[$index2]) && is_array($array[$index2])) $mergedArray = array_merge($array[$index1], $array[$index2]);
        else if (isset($array[$index1]) && is_array($array[$index1])) $mergedArray = $array[$index1];
        else if (isset($array[$index2]) && is_array($array[$index2])) $mergedArray = $array[$index2];
        else return false;
        return $mergedArray;
    }

    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function subarrayToJson($array)
    {
        if (!is_array($array)) {
            return [];
        }
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = json_encode($v);
            }
        }
        return $array;
    }

    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function subarrayOffJson($array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $array[$k] = json_decode($v);
            }
        }
        return $array;
    }

    /**
     * method to strip quotes in values of array
     *
     * @param array $array array whose values has quotes
     * @return array $array array containing subarray as jsonified strings
     */
    function stripQuotesInArrayValue($array)
    {
        //if (is_array($array)) while (list($k,$v)=each($array)) $array[$k]=preg_replace("/\"/","'",$v);
        if (is_array($array)) foreach ($array as $k => $v) $array[$k] = preg_replace("/\"/", "'", $v);
        return $array;
    }

    /**
     * method to strip quotes in values of array
     *
     * @param $data
     * @return bool $array array containing subarray as jsonified strings
     */
    function sendJmessage($data)
    {
        $recipient = $this->params->get('jmessagerecipient', 0);
        $sender = $this->params->get('jmessagesender', 0);
        $obj_jmessager = new modQlformJmessages;
        $data = $this->prepareDataWithXml($data, $this->form, $this->params->get('jmessagelabels', 1));
        if (0 == $sender OR 0 == $recipient) {
            if (0 == $sender) $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_JMESSAGENOSENDER'));
            if (0 == $recipient) $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_JMESSAGENORECIPIENT'));
            return false;
        }
        $obj_jmessager = new modQlformJmessages;
        $message = $obj_jmessager->getDataAsString($data, $this->params->get('jmessagestringtype', 'json'), $this->params->get('jmessagestringseparator', '#'));
        $subject = JText::_($this->params->get('jmessagesubject', 'qlform message'));
        $subject = $obj_jmessager->getSubject($subject, $data, $this->params->get('jmessagesubject2', ''));
        $dataToSave = $obj_jmessager->getData($recipient, $sender, $subject, $message);
        $obj_jmessager->save($dataToSave);
        return true;
    }

    /**
     * method to prepare pretext by addingparagraphs and replace data value for placeholders
     *
     * @param string $str given as message
     * @return array $data is array containing the data
     */
    function preparePretext($str, $data)
    {
        $str .= "\n\n";
        if (!isset($data) OR !is_array($data)) return $str;

        foreach ($data as $k => $v) {
            if (isset($v['data'])) $str = str_replace('{*' . $k . '*}', (string)$v['data'], $str);

        }
        return $str;
    }

    /**
     * Method to check validation of e-mail address
     *
     * @param    string $str wouldbe-email address
     * @return  bool    true on success; false on failure
     */
    public function checkEmail($str)
    {
        $obj_mailer = new modQlformMailer();
        return $obj_mailer->checkEmail($str);
    }

    /**
     * Method to save files to webspace
     *
     * @return  bool    true on success; false on failure
     */
    public function saveFiles()
    {
        /*No, you cannot make this work without the plugin.
        The plugin does the upload, not the module.
        But thanks for having a look at my code:-)*/
        $obj_plgQlformuploaderFiler = new plgQlformuploaderFiler($this->module, $this->params, $this->form);
        if (!is_array($this->files)) {
            $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_FILEUPLOAD_FILESARRAYISNOARRAY'));
            return false;
        }
        if (0 == count($this->files)) return true;
        //$tmp='tmp';
        //$destination=$tmp.'/'.$this->params->get('fileupload_destination','qlformuploader');
        $destination = $this->params->get('fileupload_destination', 'qlformuploader');

        $filesUploaded = 0;;
        foreach ($this->files as $k => $v) {
            if (!isset($v['fileChecked']) OR true !== $v['fileChecked']) {
                $obj_plgQlformuploaderFiler->logg($v, $destination, $this->module, $this->params);
                continue;
            }
            $arr_fileuploaded = $obj_plgQlformuploaderFiler->saveFile($v, $destination);
            $filesUploaded++;
            $this->files[$k] = $arr_fileuploaded;
            if (4 != $v['error']) $obj_plgQlformuploaderFiler->logg($v, $destination, $this->module, $this->params);
        }

        $this->arrMessages[] = array('notice' => 1, 'str' => sprintf(JText::_('MOD_QLFORM_FILEUPLOAD_UPLOADSUCCESSFUL'), $filesUploaded));
        return true;
    }

    /**
     * @return array
     */
    function getFilesUploadedData()
    {
        $dataFilesUpload = array();
        foreach ($this->files as $k => $v) {
            $dataFilesUpload[$k] = array();
            $dataFilesUpload[$k]['name'] = $v['name'];
            $dataFilesUpload[$k]['savedTo'] = $v['current'];
            $dataFilesUpload[$k]['errorUploadServer'] = $v['error'];
            $dataFilesUpload[$k]['errorUploadFileCheck'] = 0;
            if (!empty($v['errorMsg'])) {
                $dataFilesUpload[$k]['errorUploadFileCheck'] = 1;
                $dataFilesUpload[$k]['errorUploadFileCheckMsg'] = $v['errorMsg'];
            }
        }
        return $dataFilesUpload;
    }

    /**
     * Method to check validation of e-mail address
     *
     * @return  bool    true on success; false on failure
     */
    public function checkFiles()
    {
        /*No, you cannot make this work without the plugin.
        The plugin does the upload, not the module.
        But thanks for having a look at my code:-)*/
        $obj_plgQlformuploaderFiler = new plgQlformuploaderFiler($this->module, $this->params, $this->form);

        /*for licence*/
        if (1 != $obj_plgQlformuploaderFiler->checkLicenceAllowed()) {
            $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_FILEUPLOAD_NOTALLOWED'));
            if (isset($obj_plgQlformuploaderFiler->arrMessages) AND 0 < count($obj_plgQlformuploaderFiler->arrMessages)) foreach ($obj_plgQlformuploaderFiler->arrMessages as $v) $this->arrMessages = $v;
            return false;
        }
        if (!is_array($this->files)) {
            $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_FILEUPLOAD_FILESARRAYISNOARRAY'));
            return false;
        }
        if (0 == count($this->files)) return true;
        $arrCheck = array();
        $arrCheck['filemaxsize'] = $this->params->get('fileupload_maxfilesize', 10000);
        $arrCheck['filetypeallowed'] = explode(',', (string)$this->params->get('fileupload_filetypeallowed', ''));
        foreach ($this->files as $k => $v) {
            $mixChecked = $obj_plgQlformuploaderFiler->checkFile($v, $arrCheck);
            $this->files[$k]['errorMsg'] = '';
            if (true === $mixChecked) $this->files[$k]['fileChecked'] = true;
            else {
                $this->files[$k]['errorMsg'] = $mixChecked;
                $this->files[$k]['fileChecked'] = false;
            }
        }
    }

    /**
     * @return bool
     */
    public function checkPlgQlformuploaderExists()
    {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT * FROM `#__extensions` WHERE `element`=\'qlformuploader\'');
        $qlformuploader = $db->loadObject();
        if (!isset($qlformuploader->extension_id)) {
            $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_FILEUPLOAD_NOEXTENSIONFOUND'));
            return false;
        }
        if (1 != $qlformuploader->enabled) {
            $this->arrMessages[] = array('warning' => 1, 'str' => JText::_('MOD_QLFORM_FILEUPLOAD_EXTENSIONNOTENABLED'));
            return false;
        }
        return true;
    }

    /**
     * @param $form
     * @return mixed
     */
    public function addPlaceholder($form)
    {
        foreach ($form->getFieldsets() as $fieldset) {
            $fields = $form->getFieldset($fieldset->name);
            foreach ($fields as $field) {
                $fieldLabel = JText::_($field->getAttribute('label'));
                if ('true' == $field->getAttribute('required')) $fieldLabel .= ' *';
                $form->setFieldAttribute($field->getAttribute('name'), 'hint', $fieldLabel);
            }
        }
        return $form;
    }

    /**
     *
     */
    public function addStyles()
    {
        JHtml::stylesheet('mod_qlform/qlform.css', false, true, false);
        if ('1' == $this->params->get('stylesActive', '0')) JFactory::getDocument()->addStyleDeclaration($this->getStyles($this->params));
    }

    /**
     *
     */
    public function addScript()
    {
        $document = JFactory::getDocument();
        if (1 === (int) $this->params->get('ajax', '0')) {
            $document->addScript('/media/mod_qlform/js/qlform.js');
        }
        //echo '<script src="/media/mod_qlform/js/qlform.js" type="text/javascript"></script>';
        //JHtml::stylesheet('mod_qlform/qlform.css', false, true, false);
        //JHtml::script('qlform.js');
        //JHtml::_('script', 'qlform/qlform.js', array('version' => 'auto', 'relative' => true));
    }

    /**
     * @param $params
     * @return string
     */
    private function getStyles($params)
    {
        $strModuleId = 'mod_qlform_' . $this->module->id;
        $moduleIdSelector = '#' . $strModuleId;

        $styles = array();;
        $styles[] = $moduleIdSelector . ' input,' . $moduleIdSelector . ' Xbutton,' . $moduleIdSelector . ' select,' . $moduleIdSelector . ' textarea';
        $styles[] = '{';
        $styles[] = 'border-radius:' . $params->get('stylesInputborderradius', '0') . 'px;';
        $styles[] = 'border-width:' . $params->get('stylesInputborderwidth', '1') . 'px;';
        $styles[] = 'border-style:' . $params->get('stylesInputborderstyle', 'solid') . ';';
        $styles[] = 'border-color:' . $params->get('stylesInputbordercolor', '#666666') . ';';
        $styles[] = 'background:' . $params->get('stylesInputbackground', '#ffffff') . ';';
        $styles[] = '}';
        $styles[] = $moduleIdSelector . ' input.submit';
        $styles[] = '{';
        $styles[] = 'border-radius:inherit;';
        $styles[] = 'border-width:inherit;';
        $styles[] = 'border-style:inherit;';
        $styles[] = 'border-color:inherit;';
        $styles[] = 'background:inherit;';
        $styles[] = '}';
        //$styles[]= '*{background:red!important;}';

        /*ADDITIONAL STYLES START*/
        if ('' != trim($params->get('stylesAdditionalstyles', ''))) $styles[] = $params->get('stylesAdditionalstyles', '');
        /*ADDITIONAL STYLES STOP*/
        return implode("\n", $styles);
    }

    /**
     * @param $data
     * @param $module
     * @param $form
     * @return bool
     */
    function initPreprocessing($data, $module, $form)
    {
        $this->processData = false;
        if (true == $this->checkIfCustomExists('modQlformPreprocessData')) $this->processData = true;
        else return false;
        $this->obj_processor = new modQlformPreprocessData($data, $this->params, $module, $form);
    }

    /**
     * @param $data
     * @param $for
     * @return mixed
     */
    function processFor($data, $for)
    {
        return $this->obj_processor->$for($data);
    }
}
