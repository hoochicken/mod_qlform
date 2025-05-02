<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2025 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
require_once(__DIR__ . '/helper/QlformHelper.php');

$arr_files = ['modelModqlform', 'modQlformMailer', 'modQlformDatabase', 'modQlformDatabaseExternal', 'modQlformMessager', 'modQlformSomethingElse', 'modQlformSomethingCompletelyDifferent', 'modQlformFiler', 'modQlformJmessages', 'modQlformValidation', 'modQlformPreprocessData',];
$included = [];
foreach ($arr_files as $k => $v) {
    $classExists = class_exists($v);
    $fileExists = file_exists($file = dirname(__FILE__) . '/php/classes/' . $v . '.php');
    if (!file_exists($file = dirname(__FILE__) . '/php/classes/' . $v . '.php')) {
        continue;
    }
    require_once($file);
}

