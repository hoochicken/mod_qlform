<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
use Joomla\Registry\Registry;
use Joomla\CMS\Helper\ModuleHelper;
defined('_JEXEC') or die;

$objCaptchaEnabled = false;

/** @var Registry $params */
/** @var JForm $objForm*/
/** @var stdClass $module*/

$objCaptchaSet = $params->get('captcha', JFactory::getApplication()->get('captcha', '0'));
foreach (JPluginHelper::getPlugin('captcha') as $plugin) {
    if ($objCaptchaSet === $plugin->name) {
        $objCaptchaEnabled = true;
        break;
    }
} ?>

<form method="post" action="<?php echo JText::_(htmlspecialchars($params->get('action'))); ?>"
      id="mod_qlform_<?php echo $module->id; ?>"
      class="<?php echo $params->get('formclass', 'form-horizontal'); ?> form-validate" <?php if (1 == $params->get('fileupload_enabled') || 1 == $params->get('fileemail_enabled')) echo ' enctype="multipart/form-data" '; ?>>
    <?php
    if (1 == $params->get('addPostToForm') && isset($array_posts) && is_array($array_posts)) : foreach ($array_posts as $k => $v) : ?>
        <input type="hidden" name="former[<?php echo $k; ?>]"
               value="<?php echo preg_replace("/\"/", "", $v); ?>" /><?php
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
        if (isset($fieldset->label) && '' != $fieldset->label) echo '<legend id="legend' . $fieldset->name . '">' . JText::_($fieldset->label) . '</legend>';
        foreach ($fields as $field):
            if ($field->hidden && false !== strpos($field->input, 'MAX_FILE_SIZE')): echo $field->value . '<input type="hidden" name="MAX_FILE_SIZE" value="' . $params->get('fileupload_maxfilesize', 0) . '" />';
            elseif ($field->hidden): echo $field->input;
            else:
                ?>
                <div class="form-group control-group <?php echo $field->id; ?> <?php if (1 == $params->get('stylesLabelswithin', 0)) echo 'notlabelled'; else echo 'labelled'; ?> <?php echo $field->class; ?>">
                    <?php
                    // print_r($field);
                    if (1 != $params->get('stylesLabelswithin', 0) || $objHelper->formControl . '_sendcopy' == trim($field->id) || 'spacer' == strtolower($field->type) || 'checkboxes' == strtolower($field->type)):
                        $label = $field->label;
                        $label = str_replace('}}', '>', str_replace('{{', '<', preg_replace('/class="/', 'class="control-label ', $label, 1)));
                        echo $label;
                    endif; ?>
                    <div class="controls <?php echo $field->id; ?>">
                        <?php $input = preg_replace('/(?<=class=")([^"]*)(span[0-9]{1,2})([^"]*)/', '$1$3', $field->input);
                        $input = str_replace('class="', 'class="form-control ', $field->input, $count);
                        if (0 == $count) $input = str_replace(' type=', ' class="form-control" type=', $input);
                        echo $input;
                        $count = 0;
                        ?>
                    </div>
                </div>
            <?php endif;
        endforeach;
        echo '</fieldset>';
    endforeach; ?>
    <?php if (true === $boolShowCaptcha && $objCaptcha instanceof JCaptcha) require ModuleHelper::getLayoutPath('mod_qlform', 'default_captcha'); ?>
    <div class="submit control-group">
        <div class="controls">
            <button class="btn btn-large btn-primary submit" onclick="qlformBeforeSend(<?php echo $module->id; ?>)"
                    type="submit"><?php echo htmlspecialchars(JText::_($params->get('submit'))); ?></button>
        </div>
    </div>
    <?php if (true === $boolFieldModuleId || 1 == $params->get('ajax', 0)) : ?>
        <input type="hidden" value="<?php echo $numModuleId; ?>" name="moduleId"/>
    <?php endif; ?>
</form>
