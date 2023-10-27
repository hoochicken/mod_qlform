<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
use Joomla\CMS\Language\Text;
use QlformNamespace\Module\Qlform\Site\Helper\QlformHelper;

defined('_JEXEC') or die;
/** @var \Joomla\Registry\Registry $params */
/** @var \Joomla\CMS\Captcha\Captcha $objCaptcha */
/** @var QlformHelper $objHelper */
?>
<fieldset id="fieldsetCaptcha">
    <div class="control-group captcha">
        <?php if ('' != $params->get('captchalabel') && 1 != $params->get('captchaLabelswithin')) : ?>
            <label class="control-label" for="captcha"><?php echo Text::_($params->get('captchalabel')); ?></label>
        <?php endif; ?>
        <?php if ('' != $params->get('captchadesc')): ?>
            <label class="control-label" for="captcha"><?php echo Text::_($params->get('captchadesc')); ?></label>
        <?php endif; ?>
        <div class="controls">
            <?php echo $objCaptcha->display($objHelper->formControl . '[captcha]', 'id_captcha', 'strClass'); ?>
        </div>
    </div>
</fieldset>