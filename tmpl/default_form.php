<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Helper\ModuleHelper;
use QlformNamespace\Module\Qlform\Site\Helper\QlformHelper;

defined('_JEXEC') or die;

$objCaptchaEnabled = false;

/** @var Registry $params */
/** @var JForm $objForm*/
/** @var stdClass $module*/
/** @var bool $boolFieldModuleId*/
/** @var QlformHelper $objHelper*/
/** @var bool $boolShowCaptcha */
/** @var JCaptcha $objCaptcha */
/** @var int $numModuleId */

$objCaptchaSet = $params->get('captcha', Factory::getApplication()->get('captcha', '0'));
foreach (PluginHelper::getPlugin('captcha') as $plugin) {
    if ($objCaptchaSet === $plugin->name) {
        $objCaptchaEnabled = true;
        break;
    }
} ?>

<form method="post" action="<?= Text::_(htmlspecialchars($params->get('action'))); ?>"
      id="mod_qlform_<?= $module->id; ?>"
      class="<?= $params->get('formclass', 'form-horizontal'); ?> form-validate" <?php if (1 == $params->get('fileupload_enabled') || 1 == $params->get('fileemail_enabled')) echo ' enctype="multipart/form-data" '; ?>>
    <?php
    if (1 == $params->get('addPostToForm') && isset($array_posts) && is_array($array_posts)) : foreach ($array_posts as $k => $v) : ?>
        <input type="hidden" name="former[<?= $k; ?>]"
               value="<?= preg_replace("/\"/", "", $v); ?>" /><?php
    endforeach; endif; ?>
    <?php if (1 == $params->get('honeypot', 0)) : ?>
        <div style="display:none;"><input name="JabBerwOcky" type="text" value=""/></div>
    <?php endif; ?>
    <?php if (1 == $params->get('ajax', 0)) : ?>
        <input name="qlformAjax" type="hidden" value="1"/>
    <?php endif; ?>
    <input name="formSent" type="hidden" value="1"/>
    <?php
    //echo '<pre>';print_r($objForm);die;
    foreach ($objForm->getFieldsets() as $fieldset):
        $fields = $objForm->getFieldset($fieldset->name);
        echo '<fieldset id="' . $fieldset->name . '"';
        if (isset($fieldset->class)) echo ' class="' . $fieldset->class . '"';
        echo '>';
        if (isset($fieldset->label) && '' != $fieldset->label) echo '<legend id="legend' . $fieldset->name . '">' . Text::_($fieldset->label) . '</legend>';
        foreach ($fields as $field):
            if ($field->__get('hidden', '') && false !== strpos($field->input, 'MAX_FILE_SIZE')): echo $field->value . '<input type="hidden" name="MAX_FILE_SIZE" value="' . $params->get('fileupload_maxfilesize', 0) . '" />';
            elseif ($field->__get('hidden', '')): echo $field->__get('input', null);
            else:
                ?>
                <div class="form-group control-group <?= $field->id; ?> <?php if (1 == $params->get('stylesLabelswithin', 0)) echo 'notlabelled'; else echo 'labelled'; ?> <?= $field->class; ?>">
                    <?php
                    // print_r($field);
                    if (1 != $params->get('stylesLabelswithin', 0) || $objHelper->formControl . '_sendcopy' == trim($field->id) || 'spacer' == strtolower($field->type) || 'checkboxes' == strtolower($field->type) || 'list' == strtolower($field->type)):
                        $label = $field->label;
                        $label = str_replace('}}', '>', str_replace('{{', '<', preg_replace('/class="/', 'class="control-label ', $label, 1)));
                        echo $label;
                    endif; ?>
                    <div class="controls <?= $field->id; ?>">
                        <?= preg_replace('/(?<=class=")([^"]*)(span[0-9]{1,2})([^"]*)/', '$1$3', $field->input); ?>
                    </div>
                </div>
            <?php endif;
        endforeach;
        echo '</fieldset>';
    endforeach; ?>
    <?php

    $onclick = (1 === (int)$params->get('formBehaviourBeforeSendUse', 0)) ? 'qlformBeforeSend_' . $module->id . '(' . $module->id . ')' : '';
    if ($boolShowCaptcha && $objCaptcha instanceof JCaptcha) require ModuleHelper::getLayoutPath('mod_qlform', 'default_captcha'); ?>
    <div class="submit control-group">
        <div class="controls">
            <button class="btn btn-large btn-primary submit <?php if (1 == $params->get('ajax', 0)) echo 'ajax'; ?>" onclick="<?= $onclick; ?>"
                    type="submit"><?= htmlspecialchars(Text::_($params->get('submit'))); ?></button>
        </div>
    </div>
    <?php if ($boolFieldModuleId || 1 == $params->get('ajax', 0)) : ?>
        <input type="hidden" value="<?= $numModuleId; ?>" name="moduleId"/>
    <?php endif; ?>
</form>
