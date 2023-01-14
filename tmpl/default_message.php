<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
$style = (empty($messages) || empty(strip_tags($messages)))
    ? 'display:none'
    : 'display:block';
?>
<div class="qlform message alert alert-info" style="<?php $style;?> ">
    <?php echo $messages; ?>
</div>
