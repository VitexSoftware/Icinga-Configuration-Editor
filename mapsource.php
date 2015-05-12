<?php

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$hostgroupID = $oPage->getRequestValue('hostgroup_id', 'int');


$oPage->onlyForLogged();

if (is_null($hostgroupID)) {
    $gv = new IEHostMap;
} else {
    $gv = new IEHostgroupMap($hostgroupID);
}
error_reporting(E_ALL ^ E_STRICT);

$gv->image('dot');


