<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
/** @var JRegistry $params */
/** @var stdClass $module */
?>
<script>
  function qlformBeforeSend(moduleId) {
    if (moduleId !== <?php echo $module->id ?? 0; ?>) return true;
    <?php if (1 === (int)$params->get('formBehaviourBeforeSendUse', 0)) echo $params->get('formBehaviourBeforeSend', ''); ?>
  }

  function qlformAfterSend(moduleId) {
    if (moduleId !== <?php echo $module->id ?? 0; ?>) return true;
    <?php if (1 === (int)$params->get('formBehaviourAfterSendUse', 0)) echo $params->get('formBehaviourAfterSend', ''); ?>
  }
</script>
