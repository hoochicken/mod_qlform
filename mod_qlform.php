<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2019 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once(dirname(__FILE__) . '/helper.php');

$objInput = JFactory::getApplication()->input;
/** @var $module stdClass*/
/** @var $objHelper modQlformHelper*/

if (1 == $objInput->getInt('qlformAjax', 0)) {
    jimport( 'joomla.application.module.helper' );

    $moduleIdByAjax = $objInput->getInt('moduleId', 0);
    $objDb = JFactory::getDbo();
    $objDb->setQuery('SELECT * FROM #__modules WHERE id = ' . $moduleIdByAjax);
    $arrResult = $objDb->loadObject();
    $strParams = isset($arrResult->params) ? $arrResult->params : '';

    // create propor param object
    $params = new JRegistry();
    $params->loadString($strParams);
    $module = $arrResult;
    $module->params = $params;
}

$objHelper = new modQlformHelper($params, $module);
$objHelper->formControl = $params->get('formControl', 'jform');


if (1 == $params->get('smtpCheck', 0)) {
    $recipient = preg_split("?\n?", $params->get('emailrecipient'));
    if (0 === count($recipient)) {
        $objHelper->setMessage(JText::_('MOD_QLFORM_MSG_SMTP_CONNECTION_NOT_WORKING'));
        $objHelper->setMessage(JText::_('MOD_QLFORM_MSG_SMTP_ADJUST_CONFIG'));
    }
    $to = $recipient[0];
    $subject = JText::_('MOD_QLFORM_MSG_SMTP_TESTMAIL_SUBJECT');
    $message = JText::_('MOD_QLFORM_MSG_SMTP_TESTMAIL_MESSAGE');
    $mailParams = ['emailsender' => '', 'emailreplyto' => ''];
    $obj_mailer = new modQlformMailer();
    $mailSent = $obj_mailer->mail($to, $subject, [], $mailParams, $message);
    if (!$mailSent) {
        $objHelper->setMessage(JText::_('MOD_QLFORM_MSG_SMTP_CONNECTION_NOT_WORKING'));
        $objHelper->setMessage(JText::_('MOD_QLFORM_MSG_SMTP_ADJUST_CONFIG'));
    }
}

$numModuleId = (int)$module->id;
$boolShowCaptcha = (bool)$objHelper->captchaDefault();
$boolFieldModuleId = (bool)$params->get('fieldModuleId');

//var_dump($boolFieldModuleId);echo '<pre>'; print_r($params); echo '</pre>'; die;

if (1 == $params->get('bootstrap', 0)) {
    JHtml::_('bootstrap.framework');
}

// Xml: getting xml string from params
$objHelper->addStyles();
$objHelper->addScript();
$strXml = $objHelper->transformText($params->get('xml'));
// simplexml_load_string($strXml);

// add additional fields like user id, module id etc.
$objHelper->createAdditionalFields();
if (isset($objHelper->arrFields) && is_array($objHelper->arrFields)) {
    $strXml = $objHelper->addFieldsToXml($strXml, $objHelper->arrFields, 'additionalFields');
}

// transform xml to Joomla! form object
$objForm = $objHelper->getForm($strXml, $numModuleId);
if (true === (bool)$params->get('stylesLabelswithin', false)) {
    $objForm = $objHelper->addPlaceholder($objForm);
}

// initiate captcha
if (true === $boolShowCaptcha) {
    $objCaptcha = $objHelper->getCaptcha();
}
// check database connection
if (true === (bool)$params->get('todoDatabase')) {
    $boolCheckDatabase = $objHelper->connectToDatabase();
    if (true === $boolCheckDatabase){
        $boolCheckDatabase = $objHelper->checkDatabase($objHelper->objDatabase, $params->get('databasetable'), $strXml, $params->get('showDatabaseFormError'), $params->get('databaseaddcreated'));
    }
}

if (true === (bool)$params->get('todoDatabaseExternal')) {
    $arrParamsDatabaseExternal = ['driver', 'host', 'user', 'password', 'database', 'prefix',];
    foreach ($arrParamsDatabaseExternal as $strAttribute){
        $strParameter = 'databaseexternal' . $strAttribute;
        $arrParamsDatabaseExternal[$$strAttribute] = $params->get($strParameter);
    }
    $boolCheckDatabaseExternal = $objHelper->connectToDatabase($arrParamsDatabaseExternal);
    //print_r($arrParamsDatabaseExternal);die;
    if (false !== $boolCheckDatabaseExternal) {
        $boolCheckDatabaseExternal = $objHelper->checkDatabase($objHelper->objDatabaseexternal, $params->get('databaseexternaltable'), $strXml, $params->get('showDatabaseexternalFormError'), $params->get('databaseexternaladdcreated'));
    }
}

/*validation server site*/
if
(
    /*JabBerwOcky for anti spam*/
    (
        0 == $params->get('honeypot', 0)
        OR
        (1 == $params->get('honeypot', 0) AND isset($_POST['JabBerwOcky']) AND '' == $_POST['JabBerwOcky'])
    )
    AND
    (
        (true === $boolFieldModuleId AND isset($_POST['moduleId']) AND $_POST['moduleId'] == $numModuleId AND isset($_POST['formSent']) AND 1 == $_POST['formSent'] AND is_object($objForm))
        OR
        (true === $boolFieldModuleId AND isset($_POST['formSent']) AND 1 == $_POST['formSent'] AND is_object($objForm))
    )
) {
    $data = $objInput->getData($objHelper->formControl);
    $objHelper->processData = false;
    if (1 == $params->get('processData', 0)) $objHelper->initPreprocessing($data, $module, $objForm);
    if ($objHelper->processData) $data = $objHelper->processFor($data, 'formDataBeforeValidation');
    if (1 == $params->get('captchaadded') AND 0 != $boolShowCaptcha AND isset($_POST['captcha'])) $data['captcha'] = $_POST['captcha'];
    $dataToValidate = $data;
    $dataFiles = $objInput->files->get($objHelper->formControl);
    if (is_array($dataFiles)) $dataToValidate = array_merge($dataFiles, $dataToValidate);
    if ($objHelper->processData) $dataToValidate = $objHelper->processFor($dataToValidate, 'formAndFileDataBeforeValidation');
    $validatedForm = $objHelper->validate($dataToValidate);
    $objForm = $objHelper->form;
    $validatedCaptcha = false;
    if (1 == $boolShowCaptcha AND $objCaptcha instanceof JCaptcha) $validatedCaptcha = $objHelper->checkCaptcha($objCaptcha, $data);
    if ($validatedForm AND (0 == $boolShowCaptcha OR (1 == $boolShowCaptcha AND $validatedCaptcha))) $validated = true;
    else {
        if (is_array($data) OR is_object($data)) foreach ($data as $k => $v) if (is_string($v)) $data[$k] = strip_tags(html_entity_decode($v));
        if (true == $objHelper->processData) $data = $objHelper->processFor($data, 'beforeBindToForm');
        $success = $objForm->bind($data);
        $validated = false;
    }
}
if (1 == $params->get('addPostToForm', 0)) {
    if (isset($_POST[$objHelper->formControl])) $array_posts[$objHelper->formControl] = $objHelper->subarrayToJson($_POST[$objHelper->formControl]);
    if (isset($_POST['former'])) $array_posts['former'] = $objHelper->subarrayToJson($_POST['former']);
    if (isset($array_posts)) {
        $array_posts = $objHelper->stripQuotesInArrayValue($array_posts);
        $array_posts = $objHelper->mergeSubarrays($array_posts, 'former', $objHelper->formControl);
    }
    if (isset($data) AND isset($array_posts) AND is_array($array_posts)) {
        $data = array_merge($data, $array_posts);
    }
}

if (isset($validated) AND 1 == $validated) {
    /*FILE_UPLOAD START*/
    $objHelper->files = $objInput->files->get($objHelper->formControl);
    if ((1 == $params->get('fileupload_enabled', 0) OR 1 == $params->get('fileemail_enabled', 0)) AND true == $objHelper->checkPlgQlformuploaderExists() AND is_array($objHelper->files) AND 0 < count($objHelper->files)) {
        require_once(JPATH_BASE . '/plugins/system/qlformuploader/php/classes/plgQlformuploaderFiler.php');
        foreach ($objHelper->files as $k => $v) {
            $objHelper->files[$k]['current'] = $objHelper->files[$k]['tmp_name'];
            $objHelper->files[$k]['fieldname'] = $k;
        }
        $objHelper->checkFiles();
        if (1 == $params->get('fileupload_enabled')) {
            $objHelper->saveFiles();
            $data['filesUploaded'] = $objHelper->getFilesUploadedData();
        }

    }

    if (1 == $params->get('server_data')) {
        $data['server'] = $objHelper->getServerData($params->get('server_data_ip_anonymize'));
        if (1 == $params->get('server_data_jsonify')) $data['server'] = json_encode($data['server']);
    }

    if (1 == $params->get('show_data_sent')) {
        $objHelper->arrMessages[] = array('str' => '<strong>' . JText::_('MOD_QLFORM_SHOWDATASENT_LABEL') . '</strong><br />' . $objHelper->dump($data));
    }
    $dataJsonified = $objHelper->subarrayToJson($data);

    if (1 == $params->get('todoEmail')) {
        if (true == $objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'email');
        $recipient = preg_split("?\n?", $params->get('emailrecipient'));
        $mailSent = [];
        foreach ($recipient as $k => $v) {
            $v = trim($v);
            if ('' == $v) {
                unset($recipient[$k]);
                continue;
            }
            $mailSent[$k] = $objHelper->mail($v, JText::_($params->get('emailsubject')), $dataJsonified, $objForm, '', $params->get('emaillabels', 1));
        }
        foreach($mailSent as $k => $v) {
            if (1 != $v) {
                unset($mailSent[$k]);
            }
        }
        if (count($mailSent) == count($recipient)) {
            if (1 == $params->get('emailsentdisplay', 0)) $objHelper->arrMessages[] = array('str' => JText::_('MOD_QLFORM_MAIL_SENT'));
        } else {
            $successful = count($mailSent);
            $failed = count($recipient) - count($mailSent);
            $objHelper->arrMessages[] = array('warning' => 1, 'str' => sprintf(JText::_('MOD_QLFORM_MAIL_SENT_ERROR_COUNT'), $successful, $failed));
        }
        if (isset($objHelper->files) AND 1 == $params->get('fileemail_enabled', 0)) {
            foreach ($objHelper->files as $k => $v) {
                $dataFileUpload[$v['name']]['savedTo'] = $v['current'];
                $dataFileUpload[$v['name']]['error'] = $v['error'];
            }
            $data['filesSendViaEmail'] = $dataFileUpload;
            unset($objHelper->files);
        }
    }
    $dataJsonified = $objHelper->subarrayToJson($data);

    if (1 == $params->get('todoDatabase') AND true == $boolCheckDatabase) {
        if (true == $objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'database');
        if (1 == $params->get('databaseaddcreated')) $dataJsonified['created'] = date('Y-m-d H:i:s');
        if (1 == $params->get('showDataSavedToDatabase')) $objHelper->arrMessages[] = array('str' => '<strong>' . JText::_('MOD_QLFORM_SHOWDATASAVEDTODATABASE_LABEL') . '</strong><br />' . $objHelper->dump($dataJsonified, 'foreachstring'));
        $objHelper->saveToDatabase($params->get('databasetable'), $dataJsonified);
    }
    if (1 == $params->get('todoDatabaseExternal') AND true == $boolCheckDatabaseExternal) {
        if (true == $objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'databaseExternal');
        if (1 == $params->get('databaseexternaladdcreated')) $dataJsonified['created'] = date('Y-m-d H:i:s');
        if (1 == $params->get('showDataSavedToDatabaseexternal')) $objHelper->arrMessages[] = array('str' => '<strong>' . JText::_('MOD_QLFORM_SHOWDATASAVEDTODATABASE_LABEL') . '</strong><br />' . $objHelper->dump($dataJsonified, 'foreachstring'));
        $objHelper->saveToDatabase($params->get('databaseexternaltable'), $dataJsonified, $arrParamsDatabaseExternal);
    }
    if (1 == $params->get('todoSomethingElse')) {
        if (true == $objHelper->processData) $data = $objHelper->processFor($data, 'somethingElse');
        $objHelper->doSomethingElse($data, $module, $objForm);
    }
    if (1 == $params->get('todoSomethingCompletelyDifferent')) {
        if (true == $objHelper->processData) $data = $objHelper->processFor($data, 'completlyDifferent');
        $objHelper->doSomethingCompletelyDifferent($data, $module, $objForm);
    }
    if (1 == $params->get('todoJmessage')) {
        if (true == $objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'jmessage');
        $objHelper->sendJmessage($data);
    }
    if (1 == $params->get('todoSendcopy') AND isset($_POST[$objHelper->formControl]) AND isset($_POST[$objHelper->formControl]['sendcopy']) AND 1 == $_POST[$objHelper->formControl]['sendcopy'] AND !empty($data[$params->get('sendcopyfieldname')])) {
        $dataWithoutServer = $data;
        if (isset($dataWithoutServer['server'])) unset($dataWithoutServer['server']);
        if (true == $objHelper->processData) $dataWithoutServer = $objHelper->processFor($dataWithoutServer, 'sendcopy');
        $dataWithoutServer = $objHelper->subarrayToJson($dataWithoutServer);
        $objHelper->mail($data[$params->get('sendcopyfieldname')], JText::_('MOD_QLFORM_COPY') . ': ' . JText::_($params->get('emailsubject')), $dataWithoutServer, $objForm, $params->get('sendcopypretext'), $params->get('sendcopylabels', 1), 1);
    }

    $strLocation = $params->get('location');
    if (1 == $params->get('locationbool') AND !empty($strLocation)) {
        header('HTTP/1.0 302 Found');
        header('location:' . JText::_($strLocation));
        exit;
    }
    $objHelper->arrMessages[] = array('str' => $params->get('message'));
}

// output json for recieve with javascript
if (1 == $objInput->getInt('qlformAjax', 0)) {
    $arrReturn = array_column($objHelper->arrMessages, 'str');
    if ($validated) {
        $json = new JResponseJson(['messages' => $arrReturn], implode('. ', $arrReturn));
    } else {
        $json = new JResponseJson(['messages' => $arrReturn], implode('. ', $arrReturn), true);
    }
    echo $json;
    exit;
}


/*Display messages*/
if (isset($objHelper->arrMessages) AND is_array($objHelper->arrMessages)) $messages = $objHelper->displayMessages($params->get('messageType'));
require JModuleHelper::getLayoutPath('mod_qlform', $params->get('layout', 'default'));
