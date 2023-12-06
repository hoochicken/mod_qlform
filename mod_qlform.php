<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace QlformNamespace\Module\Qlform\Site\Helper;

use Exception;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Registry\Registry;
use QlformNamespace\Module\Qlform\Site\Helper\QlformHelper;
use Joomla\CMS\Helper\ModuleHelper;
use stdClass;


require_once(__DIR__ . '/mod_qlform_require.php');

$objInput = QlformHelper::getInputByVersion(JVERSION);
$ajax = 'com_ajax' === $objInput->getString('option', '') && 'qlform' === $objInput->getString('module', '');
$validated = false;

defined('_JEXEC') or die;

/** @var stdClass $module */
/** @var QlformHelper $objHelper */


if ($ajax) {
    jimport('joomla.application.module.helper');

    $result = QlformHelper::getModuleParameters($objInput->getInt('moduleId', 0));
    $paramsRaw = $result->params ?? '';

    // create proper param object
    $params = new Registry();
    $params->loadString($paramsRaw);
    $module = $result;
    $module->params = $params;
}

// build helper with new parameter settings
$objHelper = new QlformHelper($params, $module);
$objHelper->formControl = $params->get('formControl', 'jform');

$db = $objHelper->getDatabaseDriver(JVERSION);

if ($params->get('smtpCheck', false)) {
    $recipientAll = preg_split("?\n?", $params->get('emailrecipient'));
    if (0 === count($recipientAll)) {
        $objHelper->setMessage(Text::_('MOD_QLFORM_MSG_SMTP_CONNECTION_NOT_WORKING'));
        $objHelper->setMessage(Text::_('MOD_QLFORM_MSG_SMTP_ADJUST_CONFIG'));
    }
    $to = $recipientAll[0];
    $subject = Text::_('MOD_QLFORM_MSG_SMTP_TESTMAIL_SUBJECT');
    $message = Text::_('MOD_QLFORM_MSG_SMTP_TESTMAIL_MESSAGE');
    $mailParams = ['emailsender' => '', 'emailreplyto' => ''];
    $obj_mailer = new modQlformMailer();
    $mailSent = $obj_mailer->mail($to, $subject, [], $mailParams, $message);
    if (!$mailSent) {
        $objHelper->setMessage(Text::_('MOD_QLFORM_MSG_SMTP_CONNECTION_NOT_WORKING'));
        $objHelper->setMessage(Text::_('MOD_QLFORM_MSG_SMTP_ADJUST_CONFIG'));
    }
}

$numModuleId = (int)$module->id;
$boolShowCaptcha = (bool)$objHelper->captchaDefault();
$boolFieldModuleId = (bool)$params->get('fieldModuleId');

//var_dump($boolFieldModuleId);echo '<pre>'; print_r($params); echo '</pre>'; die;

if ($params->get('bootstrap', 0)) {
    HTMLHelper::_('bootstrap.framework');
}

// Xml: getting xml string from params

if (!$ajax) {
    $objHelper->addStyles();
    $objHelper->addScript();
}
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

$objCaptcha = null;
// initiate captcha
if ($boolShowCaptcha) {
    $objCaptcha = $objHelper->getCaptcha();
}
// check database connection
$boolCheckDatabase = false;
if ($params->get('todoDatabase')) {
    $boolCheckDatabase = $objHelper->connectToDatabase($db);
    if ($boolCheckDatabase) {
        $boolCheckDatabase = $objHelper->checkDatabase($objHelper->objDatabase, $params->get('databasetable'), $strXml, $params->get('showDatabaseFormError'), $params->get('databaseaddcreated'));
    }
}

$boolCheckDatabaseExternal = false;
if ($params->get('todoDatabaseExternal')) {
    $arrParamsDatabaseExternal = ['driver', 'host', 'user', 'password', 'database', 'prefix',];
    foreach ($arrParamsDatabaseExternal as $strAttribute) {
        $strParameter = 'databaseexternal' . $strAttribute;
        $arrParamsDatabaseExternal[$$strAttribute] = $params->get($strParameter);
    }
    $boolCheckDatabaseExternal = $objHelper->connectToDatabase($db, $arrParamsDatabaseExternal);
    if (false !== $boolCheckDatabaseExternal) {
        $boolCheckDatabaseExternal = $objHelper->checkDatabase($objHelper->objDatabaseexternal, $params->get('databaseexternaltable'), $strXml, $params->get('showDatabaseexternalFormError'), $params->get('databaseexternaladdcreated'));
    }
}

/*validation server site*/
if
(
    /*JabBerwOcky for anti spam*/
    (
        !$params->get('honeypot', false)
        ||
        ($params->get('honeypot', 0) && isset($_POST['JabBerwOcky']) && '' == $_POST['JabBerwOcky'])
    )
    &&
    (
        ($boolFieldModuleId && isset($_POST['moduleId']) && $_POST['moduleId'] == $numModuleId && isset($_POST['formSent']) && $_POST['formSent'] && is_object($objForm))
        ||
        (!$boolFieldModuleId && isset($_POST['formSent']) && $_POST['formSent'] && is_object($objForm))
    )
) {
    $data = $objInput->getData($objHelper->formControl);
    $objHelper->processData = false;
    if ($params->get('processData', 0)) $objHelper->initPreprocessing($data, $module, $objForm);
    if ($objHelper->processData) $data = $objHelper->processFor($data, 'formDataBeforeValidation');
    if ($params->get('captchaadded') && 0 != $boolShowCaptcha && isset($_POST['captcha'])) $data['captcha'] = $_POST['captcha'];
    $dataToValidate = $data;
    $dataFiles = $objInput->files->get($objHelper->formControl);
    if (is_array($dataFiles)) $dataToValidate = array_merge($dataFiles, $dataToValidate);
    if ($objHelper->processData) $dataToValidate = $objHelper->processFor($dataToValidate, 'formAndFileDataBeforeValidation');
    $validatedForm = $objHelper->validate($dataToValidate);
    $objForm = $objHelper->form;
    $validatedCaptcha = false;
    if ($boolShowCaptcha && $objCaptcha instanceof Captcha) $validatedCaptcha = $objHelper->checkCaptcha($objCaptcha, $data);
    if ($validatedForm && (!$boolShowCaptcha || ($boolShowCaptcha && $validatedCaptcha))) $validated = true;
    else {
        if (is_array($data) || is_object($data)) foreach ($data as $k => $v) if (is_string($v)) $data[$k] = strip_tags(html_entity_decode($v));
        if ($objHelper->processData) $data = $objHelper->processFor($data, 'beforeBindToForm');
        $success = $objForm->bind($data);
        $validated = false;
    }
}
if ($params->get('addPostToForm', 0)) {
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

if ($validated) {
    /*FILE_UPLOAD START*/
    $objHelper->files = $objInput->files->get($objHelper->formControl);
    if ($params->get('fileupload_enabled', 0 || $params->get('fileemail_enabled', 0)) && $objHelper->checkPlgQlformuploaderExists() && is_array($objHelper->files) && 0 < count($objHelper->files)) {
        require_once(JPATH_BASE . '/plugins/system/qlformuploader/php/classes/plgQlformuploaderFiler.php');
        foreach ($objHelper->files as $k => $v) {
            $objHelper->files[$k]['current'] = $objHelper->files[$k]['tmp_name'];
            $objHelper->files[$k]['fieldname'] = $k;
        }
        $objHelper->checkFiles();

        if ($params->get('fileupload_enabled')) {
            $objHelper->saveFiles();
            $data['filesUploaded'] = $objHelper->getFilesUploadedData();
            $data['hyperlinks'] = implode('<br />', array_column($data['filesUploaded'], 'hyperlink'));
        }

    }

    if ($params->get('server_data')) {
        $data['server'] = $objHelper->getServerData($params->get('server_data_ip_anonymize'));
        if ($params->get('server_data_jsonify')) $data['server'] = json_encode($data['server']);
    }

    if ($params->get('show_data_sent')) {
        $objHelper->arrMessages[] = ['str' => '<strong>' . Text::_('MOD_QLFORM_SHOWDATASENT_LABEL') . '</strong><br />' . $objHelper->dump($data)];
    }
    $dataJsonified = $objHelper->subarrayToJson($data);

    if ($params->get('todoEmail')) {
        if ($objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'email');

        $emailMapping = preg_split("?\n?", $params->get('emailrecipient'));
        $recipientAll = $objHelper->getEmailAdressesFromMapping($emailMapping);
        $recipientsDefault = explode(';', trim($recipientAll[0] ?? ''));
        array_walk($recipientsDefault, function (&$item) { $item = trim($item); });
        $recipientsDefault = array_filter($recipientsDefault);
        if ($params->get('emailswitch') && 0 < count($emailMapping)) {
            $emailMapping = $objHelper->getEmailMapping($emailMapping);
            $emailFieldname = $params->get('emailfieldname', '');
            $switchValue = $dataToValidate[$emailFieldname] ?? '';
            $recipientAll = $emailMapping[$switchValue] ?? $recipientsDefault;
        }
        $mailSent = [];
        try {
            foreach ($recipientAll as $k => $emailAdress) {
                $emailAdress = trim($emailAdress);
                if (empty($emailAdress)) {
                    unset($recipientAll[$k]);
                    continue;
                }
                $mailSent[$k] = $objHelper->mail($emailAdress, Text::_($params->get('emailsubject')), $dataJsonified, $objForm, '', (bool)$params->get('emaillabels', true));
            }
        } catch (Exception $e) {

        }
        foreach ($mailSent as $k => $v) {
            if (!$v) {
                unset($mailSent[$k]);
            }
        }
        $successful = count($mailSent);
        $failed = count($recipientAll) - count($mailSent);
        if (count($mailSent) === count($recipientAll)) {
            if ($params->get('emailsentdisplay', false)) {
                $objHelper->arrMessages[] = ['str' => Text::sprintf('MOD_QLFORM_MAIL_SENT', $successful)];
            }
        } else {
            $objHelper->arrMessages[] = ['warning' => 1, 'str' => Text::sprintf('MOD_QLFORM_MAIL_SENT_ERROR_COUNT', $successful, $failed)];
        }
        if (isset($objHelper->files) && $params->get('fileemail_enabled', false)) {
            $dataFileUpload = [];
            foreach ($objHelper->files as $k => $v) {
                if (!isset($dataFileUpload[$v['name']])) $dataFileUpload[$v['name']] = [];
                $dataFileUpload[$v['name']]['savedTo'] = $v['current'] ?? '';
                $dataFileUpload[$v['name']]['error'] = $v['error'] ?? '';
            }
            $data['filesSendViaEmail'] = $dataFileUpload;
            unset($objHelper->files);
        }
    }
    $dataJsonified = $objHelper->subarrayToJson($data);

    if ($params->get('todoDatabase') && $boolCheckDatabase) {
        if ($objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'database');
        if ($params->get('databaseaddcreated')) $dataJsonified['created'] = date('Y-m-d H:i:s');
        if ($params->get('showDataSavedToDatabase')) $objHelper->arrMessages[] = array('str' => '<strong>' . Text::_('MOD_QLFORM_SHOWDATASAVEDTODATABASE_LABEL') . '</strong><br />' . $objHelper->dump($dataJsonified, 'foreachstring'));
        $objHelper->saveToDatabase($params->get('databasetable'), $dataJsonified);
    }
    if ($params->get('todoDatabaseExternal') && $boolCheckDatabaseExternal) {
        if ($objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'databaseExternal');
        if ($params->get('databaseexternaladdcreated')) $dataJsonified['created'] = date('Y-m-d H:i:s');
        if ($params->get('showDataSavedToDatabaseexternal')) $objHelper->arrMessages[] = array('str' => '<strong>' . Text::_('MOD_QLFORM_SHOWDATASAVEDTODATABASE_LABEL') . '</strong><br />' . $objHelper->dump($dataJsonified, 'foreachstring'));
        $objHelper->saveToDatabase($params->get('databaseexternaltable'), $dataJsonified, $arrParamsDatabaseExternal);
    }
    if ($params->get('todoSomethingElse')) {
        if ($objHelper->processData) $data = $objHelper->processFor($data, 'somethingElse');
        $objHelper->doSomethingElse($data, $module, $objForm);
    }
    if ($params->get('todoSomethingCompletelyDifferent')) {
        if ($objHelper->processData) $data = $objHelper->processFor($data, 'completlyDifferent');
        $objHelper->doSomethingCompletelyDifferent($data, $module, $objForm);
    }
    if ($params->get('todoJmessage')) {
        if ($objHelper->processData) $dataJsonified = $objHelper->processFor($dataJsonified, 'jmessage');
        // $objHelper->sendJmessage($data);
        $objHelper->sendJmessageAll($data);
    }
    if ($params->get('todoSendcopy') && isset($_POST[$objHelper->formControl]) && isset($_POST[$objHelper->formControl]['sendcopy']) && 1 == $_POST[$objHelper->formControl]['sendcopy'] && !empty($data[$params->get('sendcopyfieldname')])) {
        $dataWithoutServer = $data;
        if (isset($dataWithoutServer['server'])) unset($dataWithoutServer['server']);
        if ($objHelper->processData) $dataWithoutServer = $objHelper->processFor($dataWithoutServer, 'sendcopy');
        $dataWithoutServer = $objHelper->subarrayToJson($dataWithoutServer);
        $pretext = (string)$params->get('sendcopypretext', false);
        if ($params->get('sendcopyswitch', false)) {
            $fieldvalue = $data[$params->get('switchsendcopyfieldname', '')] ?? '';
            $pretext = $objHelper->unfoldByPretextSwitch($pretext, $fieldvalue) ;
        }
        $objHelper->mail($data[$params->get('sendcopyfieldname')], Text::_('MOD_QLFORM_COPY') . ': ' . Text::_($params->get('emailsubject')), $dataWithoutServer, $objForm, $pretext, (bool)$params->get('sendcopylabels', true), true);
    }

    $strLocation = $params->get('location');
    if ($params->get('locationbool') && !empty($strLocation)) {
        header('HTTP/1.0 302 Found');
        header('location:' . Text::_($strLocation));
        exit;
    }
    if (!$ajax && 1 === (int)$params->get('formBehaviourAfterSendUse', 0)) {
        echo '<script>' . $params->get('formBehaviourAfterSend', '') . '</script>';
    }
    $objHelper->arrMessages[] = array('str' => $params->get('message'));
}

// output json for recieve with javascript
// if (1 == $objInput->getInt('qlformAjax', 0)) {
if ($ajax) {
    $arrReturn = array_column($objHelper->arrMessages, 'str');
    $error = !$validated;
    $json = new JsonResponse(['messages' => $arrReturn, 'moduleId' => $numModuleId], implode('. ', $arrReturn), $error);
    echo $json;
    exit;
}


/*Display messages*/
$messages = '';
if (is_array($objHelper->arrMessages) && 0 < count($objHelper->arrMessages)) {
    $messages = $objHelper->displayMessages($params->get('messageType'));
}
require ModuleHelper::getLayoutPath('mod_qlform', $params->get('layout', 'default'));
