<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2014 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
$strModuleId='mod_qlform_'.$module->id;
$moduleIdSelector='#'.$strModuleId;

/*FORM IN GENERAL START*/
$script='';
if ('row'==$params->get('stylesComposition',''))
{
    $script.=$moduleIdSelector.' dt{clear:both;float:left;}'."\n";
    $script.=$moduleIdSelector.' dd{clear:none;float:left;}'."\n";
}
elseif ('column'==$params->get('stylesComposition',''))
{
    $script.= $moduleIdSelector.' dt{clear:both;float:left;}'."\n";
    $script.= $moduleIdSelector.' dd{clear:both;float:left;}'."\n";
}
if (''!=$params->get('stylesCompositionLabelwidth','')) $script.= $moduleIdSelector.' dt {width:'.$params->get('stylesCompositionLabelwidth','').'px;}'."\n";
if (''!=$params->get('stylesFormbackground','#ffffff')) $script.= $moduleIdSelector.' {background:'.$params->get('stylesFormbackground','#ffffff').';}'."\n";
/*FORM IN GENERAL STOP*/

/*FIELDSETS START*/
$script.= $moduleIdSelector.' fieldset';
$script.= "\n";
$script.= '{'."\n";;
    if (''!=$params->get('stylesFieldsetborderradius','0')) $script.= 'border-radius:'.$params->get('stylesFieldsetborderradius','0').'px;';
    $script.= "\n";
    if (''!=$params->get('stylesFieldsetborderwidth','1')) $script.= 'border-width:'.$params->get('stylesFieldsetborderwidth','1').'px;';
    $script.= "\n";
    if (''!=$params->get('stylesFieldsetborderstyle','solid')) $script.= 'border-style:'.$params->get('stylesFieldsetborderstyle','solid').';';
    $script.= "\n";
    if (''!=$params->get('stylesFieldsetbordercolor','#000000')) $script.= 'border-color:'.$params->get('stylesFieldsetbordercolor','#000000').';';
    $script.= "\n";
    if (''!=$params->get('stylesFieldsetbackground','#ffffff')) $script.= 'background:'.$params->get('stylesFieldsetbackground','#ffffff').';';
    $script.= "\n";
$script.= '}'."\n";
/*FIELDSETS STOP*/

/*FONT START*/
$script.= $moduleIdSelector.','.$moduleIdSelector.' legend,'.$moduleIdSelector.' label,'.$moduleIdSelector.' input,'.$moduleIdSelector.' button,'.$moduleIdSelector.' select,'.$moduleIdSelector.' textarea'."\n";
$script.= '{'."\n";
    $script.= 'color:'.$params->get('stylesFontcolor','#000000').';';
    $script.= "\n";
    if('0'!=$params->get('stylesFontsize','') AND ''!=$params->get('stylesFontsize',''))$script.='font-size:'.$params->get('stylesFontsize','').'px;';
    $script.= "\n";
    if(''!=$params->get('stylesFontfamily','')) $script.='font-family:\''.$params->get('stylesFontfamily','').'\';';
    $script.= "\n";
$script.= '}'."\n";
/*FONT STOP*/

/*INPUTS A.S.O. START*/
$script.= $moduleIdSelector.' input,'.$moduleIdSelector.' button,'.$moduleIdSelector.' select,'.$moduleIdSelector.' textarea';
$script.= "\n";
$script.= '{'."\n";;
    if (''!=$params->get('stylesInputwidth','60')) $script.= 'width:'.$params->get('stylesInputwidth','60').'px;';
    $script.= "\n";
    if (''!=$params->get('stylesInputborderradius','')) $script.= 'border-radius:'.$params->get('stylesInputborderradius','').'px;';
    $script.= "\n";
    if (''!=$params->get('stylesInputborderwidth','1')) $script.= 'border-width:'.$params->get('stylesInputborderwidth','1').'px;';
    $script.= "\n";
    if (''!=$params->get('stylesInputborderstyle','solid')) $script.= 'border-style:'.$params->get('stylesInputborderstyle','').';';
    $script.= "\n";
    if (''!=$params->get('stylesInputbordercolor','#000000')) $script.= 'border-color:'.$params->get('stylesInputbordercolor','#000000').';';
    $script.= "\n";
    if (''!=$params->get('stylesInputbackground','#ffffff')) $script.= 'background:'.$params->get('stylesInputbackground','#ffffff').';';
    $script.= "\n";
$script.= '}'."\n";
/*INPUTS A.S.O. STOP*/

/*ADDITIONAL STYLES START*/
if (''!=trim($params->get('stylesAdditionalstyles','')))$script.=$params->get('stylesAdditionalstyles','');
/*ADDITIONAL STYLES STOP*/
?>
<style type="text/css">
    <?php echo $script; ?>
    <?php //echo preg_replace("/\\n/",'<br />',$script);?>
</style>