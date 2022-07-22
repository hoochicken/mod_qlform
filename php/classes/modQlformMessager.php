<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2022 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class modQlformMessager
{

	/**
	 * Method for raising messages via Joomla!
	 * @param   array  $arrMessages  messages to be displayed
	 */
	public function __construct($arrMessages,$type=1)
	{
		if (0==$type || !is_array($arrMessages) || 0>=count($arrMessages)) return false;
        if (1==$type) $this->getMessage($arrMessages);
		if (2==$type) $this->raiseJMessages($arrMessages);
		if (3==$type) {$this->getMessage($arrMessages);$this->raiseJMessages($arrMessages);}
		return true;
	}
	
	/**
	 * Method for raising error via enqueueMessage
	 * @param   array  $arrMessages  errors to be raised 
	 */
	public function raiseJMessages($arrMessages)
	{
	    //print_r($arrMessages);die;
        $app=JFactory::getApplication();
        if (is_array($arrMessages) && 0<count($arrMessages))
		foreach ($arrMessages as $k=>$v) 
		{
			if (''!=trim(strip_tags($v['str'])))
			{
                if(isset($v['warning']) && 1==$v['warning'])$app->enqueueMessage($v['str'],'error');
                else $app->enqueueMessage($v['str'],'message');
			}	
		}
	}
	/**
	 * Method for generating messages
	 * @param   array  $arrMessages  errors to be raised 
	 * @return   string errors to be shown 
	 */
	public function getMessage($arrMessages)
	{
		$message='';
		foreach ($arrMessages as $k=>$v) if (isset($v['str'])) $message.=$v['str'].'<br />';
		return $this->message=$message;
	}
}