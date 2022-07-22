<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2022 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

require_once(dirname(__FILE__) . '/helper.php');

$objInput = JFactory::getApplication()->input;
/** @var $module stdClass */
/** @var $objHelper modQlformHelper */


if (1 == $objInput->getInt('qlformAjax', 0)) {
    jimport('joomla.application.module.helper');

    $result = modQlformHelper::getModuleParameters($objInput->getInt('moduleId', 0));
    $paramsRaw = $result->params ?? '';

    // create proper param object
    $params = new JRegistry();
    $params->loadString($paramsRaw);
    $module = $result;
    $module->params = $params;
}

// build helper with new parameter settings
$objHelper = new modQlformHelper($params, $module);
$objHelper->formControl = $params->get('formControl', 'jform');

$db = Factory::getContainer()->get('DatabaseDriver');

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
if ($params->get('stylesLabelswithin', false)) {
    $objForm = $objHelper->addPlaceholder($objForm);
}

// initiate captcha
if ($boolShowCaptcha) {
    $objCaptcha = $objHelper->getCaptcha();
}
// check database connection
if ($params->get('todoDatabase')) {
    $boolCheckDatabase = $objHelper->connectToDatabase($db);
    if (true === $boolCheckDatabase) {
        $boolCheckDatabase = $objHelper->checkDatabase($objHelper->objDatabase, $params->get('databasetable'), $strXml, $params->get('showDatabaseFormError'), $params->get('databaseaddcreated'));
    }
}

if ($params->get('todoDatabaseExternal')) {
    $arrParamsDatabaseExternal = ['driver', 'host', 'user', 'password', 'database', 'prefix',];
    foreach ($arrParamsDatabaseExternal as $strAttribute) {
        $strParameter = 'databaseexternal' . $strAttribute;
        $arrParamsDatabaseExternal[$$strAttribute] = $params->get($strParameter);
    }
    $boolCheckDatabaseExternal = $objHelper->connectToDatabase($db, $arrParamsDatabaseExternal);
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
        ||
        (1 == $params->get('honeypot', 0) && isset($_POST['JabBerwOcky']) && '' == $_POST['JabBerwOcky'])
    )
    &&
    (
        (true === $boolFieldModuleId && isset($_POST['moduleId']) && $_POST['moduleId'] == $numModuleId && isset($_POST['formSent']) && 1 == $_POST['formSent'] && is_object($objForm))
        ||
        (false === $boolFieldModuleId && isset($_POST['formSent']) && 1 == $_POST['formSent'] && is_object($objForm))
    )
) {
    $data = $objInput->getData($objHelper->formControl);
    $objHelper->processData = false;
    if (1 == $params->get('processData', 0)) $objHelper->initPreprocessing($data, $module, $objForm);
    if ($objHelper->processData) $data = $objHelper->processFor($data, 'formDataBeforeValidation');
    if (1 == $params->get('captchaadded') && 0 != $boolShowCaptcha && isset($_POST['captcha'])) $data['captcha'] = $_POST['captcha'];
    $dataToValidate = $data;
    $dataFiles = $objInput->files->get($objHelper->formControl);
    if (is_array($dataFiles)) $dataToValidate = array_merge($dataFiles, $dataToValidate);
    if ($objHelper->processData) $dataToValidate = $objHelper->processFor($dataToValidate, 'formAndFileDataBeforeValidation');
    $validatedForm = $objHelper->validate($dataToValidate);
    $objForm = $objHelper->form;
    $validatedCaptcha = false;
    if (1 == $boolShowCaptcha && $objCaptcha instanceof JCaptcha) $validatedCaptcha = $objHelper->checkCaptcha($objCaptcha, $data);
    if ($validatedForm && (0 == $boolShowCaptcha || (1 == $boolShowCaptcha && $validatedCaptcha))) $validated = true;
    else {
        if (is_array($data) || is_object($data)) foreach ($data as $k => $v) if (is_string($v)) $data[$k] = strip_tags(html_entity_decode($v));
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
    if (isset($data) && isset($array_posts) && is_array($array_posts)) {
        $data = array_merge($data, $array_posts);
    }
}

if (isset($validated) && 1 == $validated) {
    /*FILE_UPLOAD START*/
    $objHelper->files = $objInput->files->get($objHelper->formControl);
    if ((1 == $params->get('fileupload_enabled', 0) || 1 == $params->get('fileemail_enabled', 0)) && true == $objHelper->checkPlgQlformuploaderExists() && is_array($objHelper->files) && 0 < count($objHelper->files)) {
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
        foreach ($mailSent as $k => $v) {
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
        if (isset($objHelper->files) && 1 == $params->get('fileemail_enabled', 0)) {
            foreach ($objHelper->files as $k => $v) {
                $dataFileUpload[$v['name']]['savedTo'] = $v['current'];
                $dataFileUpload[$v['name']]['error'] = $v['error'];
            }
            $data['filesSendViaEmail'] = $dataFileUpload;
            unset($objHelper->files);
        }
    }
    $dataJsonified = $objHelper->subarrayToJson($data);

    if (1 == $params->get('todoDatabase') && true == $boolCheckDatabase) {
        if (true == $objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'database');
        if (1 == $params->get('databaseaddcreated')) $dataJsonified['created'] = date('Y-m-d H:i:s');
        if (1 == $params->get('showDataSavedToDatabase')) $objHelper->arrMessages[] = array('str' => '<strong>' . JText::_('MOD_QLFORM_SHOWDATASAVEDTODATABASE_LABEL') . '</strong><br />' . $objHelper->dump($dataJsonified, 'foreachstring'));
        $objHelper->saveToDatabase($params->get('databasetable'), $dataJsonified);
    }
    if (1 == $params->get('todoDatabaseExternal') && true == $boolCheckDatabaseExternal) {
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
    if (1 == $params->get('todoSendcopy') && isset($_POST[$objHelper->formControl]) && isset($_POST[$objHelper->formControl]['sendcopy']) && 1 == $_POST[$objHelper->formControl]['sendcopy'] && !empty($data[$params->get('sendcopyfieldname')])) {
        $dataWithoutServer = $data;
        if (isset($dataWithoutServer['server'])) unset($dataWithoutServer['server']);
        if (true == $objHelper->processData) $dataWithoutServer = $objHelper->processFor($dataWithoutServer, 'sendcopy');
        $dataWithoutServer = $objHelper->subarrayToJson($dataWithoutServer);
        $objHelper->mail($data[$params->get('sendcopyfieldname')], JText::_('MOD_QLFORM_COPY') . ': ' . JText::_($params->get('emailsubject')), $dataWithoutServer, $objForm, $params->get('sendcopypretext'), $params->get('sendcopylabels', 1), 1);
    }

    $strLocation = $params->get('location');
    if (1 == $params->get('locationbool') && !empty($strLocation)) {
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
if (isset($objHelper->arrMessages) && is_array($objHelper->arrMessages)) $messages = $objHelper->displayMessages($params->get('messageType'));
require JModuleHelper::getLayoutPath('mod_qlform', $params->get('layout', 'default'));
