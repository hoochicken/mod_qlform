<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2021 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace qlform;
defined('_JEXEC') or die;

class modQlformJmessages
{
    /**
     * method for mailing using JoomlaMailer
     *
     * @param   string    $to      recipient of mail
     * @param   string 	$subject for mail
     * @param   array  $data post data from form
     *
     * @return  bool  True on success, false on failure
     */
    public function __construct()
    {
        $this->db=new modQlformDatabase();
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
	public function save($data)
	{
        $this->db->save('#__messages',$data);
        return true;
	}
	
	/**
	 * method to generate headline and body of mail 
	 *  
	 * @param array $data
	 * @param string $subject
	 */
	public function getData($recipient,$sender,$subject,$message)
	{
        $data=[];
        $data['user_id_from']=$sender;
        $data['user_id_to']=$recipient;
        $data['subject']=$subject;
        $data['message']=$message;
        $data['state']=0;
        $data['date_time']=date('Y-m-d H:i:s');
        $data['priority']=0;
        return $data;
	}

    /**
     * Method to check validation of e-mail address
     *
     * @param	string	$str wouldbe-email address
     * @return  bool    true on success; false on failure
     */
	public function getDataAsString($data,$strtype='html',$separator='#')
	{
        switch ($strtype)
        {
            case 'bare':
                $data=$this->subarrayToJson($data);
                $str='';
                foreach($data as $k=>$v)$str.='<div><strong>'.$k.'</strong></div><div>'.$v.'</div><p />';
                break;
            case 'implode':
                $str=$this->getImplode($data,$separator);
                break;
            case 'dump':
                $str=$this->dump($data);
                break;
            case 'serialize':
                $str=$this->getSerialize($data);
                break;
            case 'html':
                $str=$this->getHtml($data);
                break;
            case 'json':
            default :
                $str=json_encode($data);
                break;
        }
        return $str;
    }
    public function getSubject($subject,$data,$subject2)
    {
        $arr=explode(',',$subject2);
        if(!is_array($arr) || 0==count($arr))return $subject;
        foreach($arr as $k=>$v)if(isset($data[trim($v)]))
        {
            $v=trim($v);
            if(isset($data[$v]) && is_string($data[$v]['data']))$subject.=' - '.$data[$v]['data'];
            else $subject.=' - '.json_encode($data[$v]);
        }
        return $subject;
    }
    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function getHtml($array)
    {
        $arr=[];
        $separator='<br />';
        foreach($array as $k => $v) {
            $arr[]='<strong>'.$v['label'].'</strong>';
            $arr[]=$v['data'].$separator;
        }
        return implode($separator,$arr);
    }
    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function getImplode($array,$separator)
    {
        $arr=[];
        foreach($array as $k => $v) {
            $arr[]=$v['label'].$separator.$v['data'];
        }
        $str=implode($separator,$arr);
        return $str;
    }
    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function getSerialize($array)
    {
        foreach($array as $k => $v) {
            unset($array[$k]['name']);
        }
        return serialize($array);
    }
    /**
     * method to turn subarray into string via json_encode
     *
     * @param array $array multidimensional array
     * @return array $array array containing subarray as jsonified strings
     */
    function subarrayToJson($array)
    {
        if (is_array($array)){
            foreach($array as $k => $v) {
                if (is_array($v)) $array[$k]=json_encode($v);
            }
        }
        return $array;
    }
    /**
     * method to turn content vor array or object to string
     * that the developer of this module could never have guessed
     *
     * @param mixed $variable at your service
     */
    function dump($data,$type='var_dump')
    {
        foreach ($data as $k => $v) {
            unset($data[$k]['name']);
        }
        if ('var_dump'==$type)
        {
            ob_start();
            var_dump($data);
            $str_data=ob_get_contents();
            ob_end_clean();
        }
        elseif ('foreachstring'==$type)
        {
            $str_data='';
            foreach ($data as $k=>$v)
            {
                $str_data.='col['.$v['label'].']=>'.$v['data'].'<br />';
            }
        }
        return $str_data;
    }
}