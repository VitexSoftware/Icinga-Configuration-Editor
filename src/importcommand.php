<?php

namespace Icinga\Editor;

/**
 * Import ze souboru
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

if ($oPage->isPosted()) {
    $importer = new Engine\Command();
    $success  = 0;
    $cfgText  = $oPage->getRequestValue('cfgtext');
    if ($cfgText) {
        $success += $importer->importText($cfgText,
            ['command_type' => $oPage->getRequestValue('type')]);
    }

    if (isset($_FILES['cfgfile']['tmp_name']) && strlen(trim($_FILES['cfgfile']['tmp_name']))) {
        $success += $importer->importFile($_FILES['cfgfile']['tmp_name'],
            ['command_type' => $oPage->getRequestValue('type')]);
    }
    if ($success) {
        $oPage->addStatusMessage(sprintf(_('Příkaz %s byl naimportován'),
                $importer->getName()), 'success');
    } else {
        $oPage->addStatusMessage(_('Příkaz nebyl naimportován'), 'warning');
    }
} else {
    $oPage->addStatusMessage(_('Zadejte konfigurační fragment příkazu, nebo zvolte soubor k importu'));
}


$oPage->addItem(new UI\PageTop(_('Načtení příkazů ze souboru')));

$fileForm = new \Ease\TWB\Form('CfgFileUp', null, 'POST', null,
    ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data']);
$fileForm->addInput(new \Ease\Html\TextareaTag('cfgtext', ''),
    _('konfigurační fragment'));
$fileForm->addInput(new \Ease\Html\InputFileTag('cfgfile', null),
    _('configuration file'));

$typeSelector = new \Ease\Html\Select('type', 'check');
$typeSelector->addItems(['check' => 'check', 'notify' => 'notify', 'handler' => 'handler']);

$fileForm->addInput($typeSelector, _('druh vkládaných příkazů'));

$fileForm->addItem(new \Ease\TWB\SubmitButton(_('importovat'), 'success'));

$oPage->container->addItem(new \Ease\TWB\Panel(_('Import příkazu do konfigurace'),
    'success', $fileForm));

$oPage->addItem(new UI\PageBottom());


$oPage->draw();

