<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2022 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
?>
<fieldset id="fieldsetCaptcha">
    <div class="control-group captcha">
        <?php if ('' != $params->get('captchalabel') && 1 != $params->get('captchaLabelswithin')) : ?>
            <label class="control-label" for="captcha"><?php echo JText::_($params->get('captchalabel')); ?></label>
        <?php endif; ?>
        <?php if ('' != trim($params->get('captchadesc'))): ?>
            <label class="control-label" for="captcha"><?php echo JText::_($params->get('captchadesc')); ?></label>
        <?php endif; ?>
        <div class="controls">
            <?php echo $objCaptcha->display($objHelper->formControl . '[captcha]', 'id_captcha', 'strClass'); ?>
        </div>
    </div>
</fieldset>