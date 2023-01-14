<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.formvalidator');
/** @var JForm $objForm  */
/** @var JRegistry $params  */
/** @var stdClass $params  */

$sfx = !empty($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';

if (!defined('QLFORM_JAVASCRIPT_ALREADY_LOADED')) {
    define('QLFORM_JAVASCRIPT_ALREADY_LOADED', true);
    require_once JModuleHelper::getLayoutPath('mod_qlform', 'default_javascript');
}
?>

<div class="qlformContainer qlform<?php echo $sfx; ?>">
    <?php
    echo '<span style="display:none;">{emailcloak=off}</span>';
    require JModuleHelper::getLayoutPath('mod_qlform', 'default_copyright');
    if (((1 == $params->get('messageType') || 3 == $params->get('messageType')) && isset($messages) && 0 < strlen($messages)) || 1 == $params->get('ajax', 0)) require JModuleHelper::getLayoutPath('mod_qlform', 'default_message');
    if (0 == $params->get('hideform') || (1 == $params->get('hideform') && (!isset($validated) || (isset($validated) && 0 == $validated)))) {
        if ('1' == $params->get('showpretext', '0')) require JModuleHelper::getLayoutPath('mod_qlform', 'default_pretext');
        if (is_object($objForm)) require JModuleHelper::getLayoutPath('mod_qlform', 'default_form');
    }
    if (1 == $params->get('backbool') && isset($validated)) require JModuleHelper::getLayoutPath('mod_qlform', 'default_back');
    if (1 == $params->get('authorbool')) require JModuleHelper::getLayoutPath('mod_qlform', 'default_author');
    ?>
</div>
