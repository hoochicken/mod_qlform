<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2022 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Qlform\Site\Helper;

use JText;

defined('_JEXEC') or die;

class modQlformCaptcha
{

    public $data;
    public $params;
    public $module;
    public $form;

    /**
     * constructor
     */
    public function __construct($params, $module)
    {
        $this->params = $params;
        $this->module = $module;
        $this->errors = array();
    }

    /**
     * Method to initiate captcha according to catcha plugin
     *
     * @param int $type type of error displayed, either via joomla or text displayes
     * @since    1.6
     */
    public function initiateCaptcha($objCaptcha)
    {
        $this->captchaHtml = '';
        if ('qlform' != $this->params->get('captcha')) return $this->initiateCaptchaPlugin($objCaptcha);
        return $this->initiateCaptchaGeneric();
    }

    public function initiateCaptchaPlugin($objCaptcha)
    {
        /*impossible to generate JDispatcher with new captcha; so only ONE captcha type for one page available; only ONE form validation, other must fail; hell!!*/
        JPluginHelper::importPlugin('captcha', $objCaptcha);

        $this->dispatcher = JEventDispatcher::getInstance();
        $this->dispatcher->trigger('onInit');
        $arr = $this->dispatcher->trigger('onDisplay');
        if (isset($arr[0])) {
            $this->captchaHtml = $arr[0];
            return true;
        }
        $this->error = JText::sprintf('MOD_QLFORM_MSG_CAPTCHANOTFOUND', $objCaptcha);
        return false;
    }

    public function initiateCaptchaGeneric()
    {

    }

    /**
     * Method to check captcha
     *
     * @param int $type type of error displayed, either via joomla or text displayes
     * @since    1.6
     */
    public function checkCaptcha()
    {
        if ('qlform' != $this->params->get('captcha')) return $this->checkCaptchaPlugin();
        return $this->checkCaptchaGeneric();
    }

    public function checkCaptchaPlugin()
    {
        $res = $this->dispatcher->trigger('onCheckAnswer');
        $this->error = $this->dispatcher->getError();
        if (!isset($res[0])) return false;
        return $res[0];
    }

    public function checkCaptchaGeneric()
    {

    }
}