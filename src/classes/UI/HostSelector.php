<?php

namespace Icinga\Editor\UI;

/**
 * Volba hostů sledovaných danou službou
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class HostSelector extends EaseContainer
{
    public $myKeyColumn = 'service_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEServices $service
     */
    public function __construct($service)
    {
        $hostsAssigned  = array();
        parent::__construct();
        $fieldName      = $this->getmyKeyColumn();
        $initialContent = new \Ease\TWB\Panel(_('Sledované hosty služby'),
            'default');
        $initialContent->setTagCss(array('width' => '100%'));

        if (is_null($service->getMyKey())) {
            $initialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {
            $serviceName = $service->getName();
            $host        = new IEHost();

            if (\Ease\Shared::user()->getSettingValue('admin')) {
                $allHosts = $host->getAllFromMySQL(NULL,
                    array($host->myKeyColumn, $host->nameColumn, 'platform', 'register'),
                    null, $host->nameColumn, $host->myKeyColumn);
            } else {
                $allHosts = $host->getListing(null, true,
                    array('platform', 'register'));
            }
            if ($service->getDataValue('host_name')) {
                foreach ($service->getDataValue('host_name') as $hostId => $hostName) {
                    if (isset($allHosts[$hostId])) {
                        $hostsAssigned[$hostId] = $allHosts[$hostId];
                    }
                }
            }
            foreach ($allHosts as $hostID => $hostInfo) {
                if ($hostInfo['register'] != 1) {
                    unset($allHosts[$hostID]);
                }

                if (($hostInfo['platform'] != 'generic') && $hostInfo['platform']
                    != $service->getDataValue('platform')) {
                    unset($allHosts[$hostID]);
                }
            }

            foreach ($hostsAssigned as $hostID => $hostInfo) {
                unset($allHosts[$hostID]);
            }

            if (count($allHosts)) {

                foreach ($allHosts as $hostID => $hostInfo) {
                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown(
                        $hostInfo[$host->nameColumn], 'inverse', 'xs',
                        array(
                        new \Ease\Html\ATag('host.php?host_id='.$hostID.'&amp;service_id='.$service->getId(),
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editace')),
                        new \Ease\Html\ATag('?addhost='.$hostInfo[$host->nameColumn].'&amp;host_id='.$hostID.'&amp;'.$service->getmyKeyColumn().'='.$service->getMyKey().'&amp;'.$service->nameColumn.'='.$service->getName(),
                            \Ease\TWB\Part::GlyphIcon('plus').' '._('Začít sledovat'))
                    )));
                }
            }

            if (count($hostsAssigned)) {
                $initialContent->addItem('<br/>');
                foreach ($hostsAssigned as $hostID => $hostInfo) {

                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown(
                        $hostInfo[$host->nameColumn], 'success', 'xs',
                        array(
                        new \Ease\Html\ATag(
                            '?delhost='.$hostInfo[$host->nameColumn].'&amp;host_id='.$hostID.'&amp;'.$service->getmyKeyColumn().'='.$service->getMyKey().'&amp;'.$service->nameColumn.'='.$service->getName(),
                            \Ease\TWB\Part::GlyphIcon('remove').' '._('Přestat sledovat'))
                        , new \Ease\Html\ATag('host.php?host_id='.$hostID.'&amp;service_id='.$service->getId(),
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editace'))
                        )
                        )
                    );
                }
            }
        }
        $this->addItem($initialContent);
    }

    /**
     * Uloží položky
     *
     * @param array $request
     */
    public static function saveMembers($request)
    {
        $host = new IEHost();
        if (isset($request[$host->myKeyColumn])) {
            if ($host->loadFromMySQL($request[$host->myKeyColumn])) {
                if (isset($request['addhost']) || isset($request['delhost'])) {
                    if (isset($request['addhost'])) {
                        $host->addMember('service_name', $request['service_id'],
                            $request['service_name']);
                        if ($host->saveToSQL()) {
                            $host->addStatusMessage(sprintf(_('položka %s byla přidána'),
                                    $request['addhost']), 'success');
                        } else {
                            $host->addStatusMessage(sprintf(_('položka %s nebyla přidána'),
                                    $request['addhost']), 'warning');
                        }
                    }
                    if (isset($request['delhost'])) {
                        $host->delMember('service_name', $request['service_id'],
                            $request['service_name']);
                        if ($host->saveToSQL()) {
                            $host->addStatusMessage(sprintf(_('položka %s byla odebrána'),
                                    $request['delhost']), 'success');
                        } else {
                            $host->addStatusMessage(sprintf(_('položka %s nebyla odebrána'),
                                    $request['delhost']), 'warning');
                        }
                    }
                }
            }
        }
    }
}