<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2019 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class modQlformMailer
{
	public $separator;
	public $separator2;
	public $arrMessages = [];

    /**
     * method for mailing using JoomlaMailer
     *
     */
    public function __construct()
    {
        $this->separator=': ';
        $this->separator2="\n";
    }
    /**
	 * method for mailing using JoomlaMailer
	 *
	 * @param   string    $to      recipient of mail
	 * @param   string 	$subject for mail
	 * @param   array  $data post data from form
	 *
	 * @return  bool  True on success, false on failure
	 */
	public function mail($to,$subject,$data,$params,$message='',$emaildisplay=0)
	{
        //if(false!==strpos($to,'halbfrau.de'))return false;
        $message=$this->generateMail($data,$subject,$message);
        $mail=JFactory::getMailer();
        $mail->addRecipient($to);
        $mail->setSubject($subject);
		$mail->setBody($message);
        $mail->setSender($params['emailsender']);
        $mail->addReplyTo($params['emailreplyto']);
        if (isset($this->files) AND is_array($this->files) AND 0<count($this->files))
        {
            foreach($this->files as $k=>$v) if(1==$v['fileChecked'])$mail->addAttachment($v['current'],$v['name'],'base64',$v['type']);
            unset($this->files);
        }
        if(1==$emaildisplay) $this->arrMessages[] = $this->mailAsString($message,$mail);
		if (!is_object($mail->Send())) return true; else return false;
	}

    /**
     * method to generate headline and body of mail
     *
     * @param $message
     * @param string $mail
     * @return string
     */
    public function mailAsString($message,$mail='')
    {
        return '<span style=\'font-family:courier\'>'.preg_replace("/\\n/",'<br />',$message).'</span>';
        echo '<pre>';print_r($mail);echo '</pre>';
    }


    /**
     * method to generate headline and body of mail
     *
     * @param array $data
     * @param string $subject
     * @param string $body
     * @return string
     */
	public function generateMail($data,$subject,$body='')
	{
		$headline=$this->generateMailHeadline($data,$subject);
		$body.=$this->generateMailBody($data);
        return $headline.$body;
	}

    /**
     * method to generate headline
     *  takes module subject, form subject, name and email by default
     * @param array $data
     * @param string $subject
     * @return string
     */
	public function generateMailHeadline($data,$subject)
	{
        $headline=$subject."\n\n";
		if (isset($data['subject']) AND isset($data['subject']['data'])) $headline.=$data['subject']['data']."\n";
		if (isset($data['name']) AND isset($data['name']['data']) AND isset($data['email']) AND isset($data['email']['data'])) $headline.=$data['name']['data'].' <'.$data['email']['data'].'>'."\n\n";
		return $headline;
	}

    /**
     * Method to generate body
     *
     * takes post data and foreaches it to body
     * @param array $data
     * @return string
     */
	public function generateMailBody($data)
	{
        $body='';
        foreach ($data as $k=>$v)
		{
			$label=ucfirst($k);
            if (isset($v['label']))$label=$v['label'];
            $vData=$v['data'];
            if (is_array($vData))
            {
                $body.=$label.$this->separator.(string)json_encode($vData).$this->separator2;
            }
            else $body.=$label.$this->separator.JText::_((string)strip_tags($vData)).$this->separator2;
		}
        return $body;
	}

    /**
     * Method to check validation of e-mail address
     *
     * @param	string	$str wouldbe-email address
     * @return  bool    true on success; false on failure
     */
	public function checkEmail($str)
	{
        if(filter_var($str,FILTER_VALIDATE_EMAIL)) return true;
        else return false;
    }
}