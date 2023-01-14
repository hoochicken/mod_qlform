<?php
/**
 * @package        mod_qlcontent
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if (isset($field)) : ?>
    <div class="<?php echo $field; ?>">
        <?php include(dirname(__FILE__) . '/default_showposition.php'); ?>
        <?php if (isset($item->$field)) echo $item->$field; ?>
    </div>
<?php endif; ?>

<?php if ($params->get('show_print_icon')) : ?>
    <?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
<?php endif; ?>
<?php if ($params->get('show_email_icon')) : ?>
    <?php echo JHtml::_('icon.email', $this->item, $params); ?>
<?php endif; ?>
<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
<?php //endif; ?>