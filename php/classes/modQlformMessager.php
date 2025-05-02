<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2025 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace QlformNamespace\Module\Qlform\Site\Helper;



use Joomla\CMS\Factory;

defined('_JEXEC') or die;

class modQlformMessager
{

    public string $message = '';

    /**
     * Method for raising messages via Joomla!
     * @param array $arrMessages messages to be displayed
     */
    public function __construct($arrMessages, $type = 1)
    {
        if (0 == $type || !is_array($arrMessages) || 0 >= count($arrMessages)) return false;
        if (1 == $type) $this->getMessage($arrMessages);
        if (2 == $type) $this->raiseJMessages($arrMessages);
        if (3 == $type) {
            $this->getMessage($arrMessages);
            $this->raiseJMessages($arrMessages);
        }
        return true;
    }

    /**
     * Method for raising error via enqueueMessage
     * @param array $arrMessages errors to be raised
     */
    public function raiseJMessages($arrMessages)
    {
        //print_r($arrMessages);die;
        $app = Factory::getApplication();
        if (!is_array($arrMessages) && 0 < count($arrMessages)) {
            return;
        }
        foreach ($arrMessages as $k => $v) {
            if (empty(trim(strip_tags($v['str'])))) {
                continue;
            }
            $type = (isset($v['warning']) && 1 == $v['warning']) ? 'error' : 'message';
            $app->enqueueMessage($v['str'], $type);
        }
    }

    public function getMessage($arrMessages)
    {
        $message = '';
        array_walk($arrMessages, function (&$item) {
            $item['html'] = $item['str'];
            // check, if html .. pretty hack, but ... well ... excuse me ...
            if (false !== strpos($item['str'], '<')) return;
            $item['html'] = '<p>' . $item['str'] . '</p>';
        });
        foreach ($arrMessages as $v) if (isset($v['html'])) $message .= $v['html'] . "\n";
        return $this->message = $message;
    }
}