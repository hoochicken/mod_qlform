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
echo '<script>';

  // initiate empty window array for moduleIds
  if (!defined('QLFORM_JAVASCRIPT_ALREADY_LOADED')) {
    define('QLFORM_JAVASCRIPT_ALREADY_LOADED', true);
    echo 'window.qlformScriptsModuleIds = [];';
  }
  // add current moduleId to that array
  echo sprintf('window.qlformScriptsModuleIds.push(%s);', $module->id);

  // create new js function for especially THIS module
  echo sprintf('function qlformAfterSend_%s(moduleId) {', $module->id);
  echo 'if ("undefined" === window.qlformScriptsModuleIds || 0 > window.qlformScriptsModuleIds.indexOf(moduleId)) return true;';
  echo $params->get('formBehaviourAfterSend', '');
  echo '}';

echo '</script>';
