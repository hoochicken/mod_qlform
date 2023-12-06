<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace QlformNamespace\Module\Qlform\Site\Helper;

use Dompdf\Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class modQlformMailer
{
    public string $separator = '';
    public string $separator2 = '';
    public array $arrMessages = [];
    public array $files = [];

    public function __construct()
    {
        $this->separator = ': ';
        $this->separator2 = "\n";
    }

    public function mail(string $to, string $subject, array $data, $params, string $message = '', bool $emaildisplay = false): bool
    {
        try {
            $message = $this->generateMail($data, $subject, $message);
            $mail = Factory::getMailer();
            $mail->addRecipient($to);
            $mail->setSubject($subject);
            $mail->setBody($message);
            $mail->setSender($params['emailsender']);
            $mail->addReplyTo($params['emailreplyto']);
            if (isset($this->files) && is_array($this->files) && 0 < count($this->files)) {
                foreach ($this->files as $k => $file) {
                    if (!isset($file['fileChecked']) || !$file['fileChecked']) {
                        continue;
                    }
                    $mail->addAttachment($file['current'], $file['name'], 'base64', $file['type']);
                }
                $this->files = [];
            }
            if ($emaildisplay) {
                $this->arrMessages[] = $this->mailAsString($message, $mail);
            }
            return !is_object($mail->Send());
        } catch (Exception $e) {
            $this->arrMessages[] = $e->getMessage();
        }
    }

    public function mailAsString($message, $mail = '')
    {
        return '<span style=\'font-family:courier\'>' . preg_replace("/\\n/", '<br />', $message) . '</span>';
        echo '<pre>';
        print_r($mail);
        echo '</pre>';
    }

    public function generateMail(array $data, string $subject, string $body = '')
    {
        $headline = $this->generateMailHeadline($data, $subject);
        $body .= $this->generateMailBody($data);
        return $headline . $body;
    }

    public function generateMailHeadline(array $data, string $subject)
    {
        $headline = $subject . "\n\n";
        if (isset($data['subject']) && isset($data['subject']['data'])) $headline .= $data['subject']['data'] . "\n";
        if (isset($data['name']) && isset($data['name']['data']) && isset($data['email']) && isset($data['email']['data'])) $headline .= $data['name']['data'] . ' <' . $data['email']['data'] . '>' . "\n\n";
        return $headline;
    }

    public function generateMailBody(array $data): string
    {
        $body = '';
        foreach ($data as $k => $v) {
            $label = ucfirst($k);
            if (isset($v['label'])) $label = $v['label'];
            $vData = $v['data'];
            if (is_array($vData)) {
                $body .= $label . $this->separator . (string)json_encode($vData) . $this->separator2;
            } else $body .= $label . $this->separator . Text::_((string)strip_tags($vData)) . $this->separator2;
        }
        return $body;
    }

    public function checkEmail(string $str): bool
    {
        return filter_var($str, FILTER_VALIDATE_EMAIL);
    }
}