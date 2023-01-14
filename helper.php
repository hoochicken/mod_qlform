<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// namespace Modules\Mod_Qlform\Helper\QlformHelper;
// namespace QlformNamespace\Module\Qlform\Site;

require_once __DIR__ . '/helper/QlformHelper.php';
use QlformNamespace\Module\Qlform\Site\Helper\QlformHelper;
// use Modules\Mod_Qlform\Helper\QlformHelper;


class ModQlformHelper
{
    /**
     * @return void
     * @throws Exception
     */
    public static function recieveQlformAjax()
    {
        QlformHelper::recieveQlformAjaxInternal();
    }
}