<?php
/**
 *    _____             _             
 *   |  __ \           | |            
 *   | |  | | ___   ___| |_ ___  _ __ 
 *   | |  | |/ _ \ / __| __/ _ \| '__|
 *   | |__| | (_) | (__| || (_) | |   
 *   |_____/ \___/ \___|\__\___/|_| 
 * 
 *    @ Copyright R3D3 2022
 */
namespace MySNMPv2;

/**
 *  Arris (Vodafone Station) TG3442DE private SNMP enterprise
 *  See SNMP syntax hints in MIB files...
 */
class tg3442de extends MySNMPPackage implements Objects\CableModemCalls
  {
    /**
     * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ HEADER
     */
    
    /**
     * List of used MIBs
     */
    const MIBs = array(
      'CLAB-WIFI-MIB',
      'ARRIS-ROUTER-DEVICE-MIB',
      'RDKB-RG-MIB'
    );
    
    /**
     * OID values Bind
     */
    const OIDBind = array(
      'clabWIFIRadioOperatingChannelBandwidth' => array(
        1 => '20MHz',
        2 => '40MHz',
        3 => '80MHz',
        4 => '160MHz',
        5 => 'ac80plus80MHz',
        6 => 'auto'
      ),
      'arrisRouterWiFiScanOperatingChannelBandwidth' => array(
        0 => 0,
        1 => 20,
        2 => 40,
        3 => 80,
        4 => 160
      ),
      'arrisRouterBssSecurityMode' => array(
        0 => 'disabled',
        1 => 'wep',
        2 => 'wpaPsk',
        3 => 'wpa2Psk',
        4 => 'wpaEnterprise',
        5 => 'wpa2Enterprise',
        6 => 'wepEnterprise',
        7 => 'wpaWpa2Psk',
        8 => 'wpaWpa2Enterprise',
        129 => 'wepApplyImmediate',
        130 => 'wpaPskImmediate',
        131 => 'wpa2PskImmediate',
        132 => 'wpaEnterpriseImmediate',
        133 => 'wpa2EnterpriseImmediate',
        134 => 'wepEnterpriseImmediate',
        135 => 'wpaWpa2PskImmediate',
        136 => 'wpaWpa2EnterpriseImmediate'
      ),
      'rdkbRgIpMgmtLanMode' => array(
        1 => 'bridge',
        2 => 'router',
        3 => 'l2tpv2-client',
        4 => 'mixed',
        5 => 'vlan'
      ),
      'arrisRouterWiFiScanResult' => array(
        0 => 'uninit',
        1 => 'running',
        2 => 'completeError',
        3 => 'completeSuccess',
      ),
      'arrisRouterWiFiStartScan' => array(
        '2g'  => 1,
        '5g'  => 2,
        'all' => 3
      )
    );
    
    /**
     * Interfaces MAC DIFF (RF INF => MTA/eRouter)
     */
    const macdiff = array(
      'mta'     => 1,
      'eRouter' => 3
    );
    
    /**
     * List of supported calls
     */
    const supported = array(
      'wlRadio',
      'wlAPs',
      'lanClientsCount',
      'lanClients',
      'wlClients',
      'lanConfig',
      'wlScanStatus',
      'wlScan',
      'wlScanResults',
      'routerBridge',
      
      'ofdm'
    );
    
    /**
     *  Is that call supported??
     * 
     */
    public static function supports($fn = '')
      {
        return in_array($fn, self::supported);
      }
    
    /**
     *  List of supported functions
     * 
     */
    public static function extra()
      {
        return self::supported;
      }
      
      
    /**
     *  OID Value binder
     * 
     */
    public static function BindVal($key = '', $value = 0)
      {
        if(array_key_exists($key, self::OIDBind) && array_key_exists($value, self::OIDBind[$key]))
          {
            return self::OIDBind[$key][$value];
          }
        return parent::BindVal($key, $value);
      }
      
    /**
     * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ EOF HEADER
     */
    /**
       * CM stats
       */
      public static function cmStats()
        {
          $data = new Objects\BaseObject;
          $data->cpuLoad = parent::snmpget('CBN-DOCSIS-CONFIG-MIB::cmCbnSystemMonitorCPULoad.0');
          return $data;
        }
        
    /**
     *  Wireless interfaces, using predefined object wlRadioInterface
     */
    public static function wlRadio()
      {
        $intfEntry = parent::snmp('CLAB-WIFI-MIB::clabWIFIRadioEntry')
                            ->assocTable(
                                'clabWIFIRadioEnable',
                                'clabWIFIRadioOperatingFrequencyBand',
                                'clabWIFIRadioChannel',
                                'clabWIFIRadioAutoChannelEnable',
                                'clabWIFIRadioOperatingChannelBandwidth',
                                'clabWIFIRadioTransmitPower'
                            );
        $radioInterfaces = array();
        foreach($intfEntry as $entry)
          {
            $if                 = new Objects\wlRadioInterface;
            $if->id             = $entry->index;
            $if->enabled        = $entry->clabWIFIRadioEnable == 1;
            $if->channel        = $entry->clabWIFIRadioChannel;
            $if->autoChannel    = $entry->clabWIFIRadioAutoChannelEnable == 1;
            $if->band           = $entry->clabWIFIRadioOperatingFrequencyBand == 1 ? WL_2G : WL_5G;
            $if->txPower        = $entry->clabWIFIRadioTransmitPower;
            $if->bandwidth      = self::BindVal('clabWIFIRadioOperatingChannelBandwidth', $entry->clabWIFIRadioOperatingChannelBandwidth);
            
            $radioInterfaces[] = $if;
          }
          
        return $radioInterfaces;
      }
      
    /**
     *  Print wireless APs ARG all -> false for print only Enabled
     */
    public static function wlAPs($all = false)
      {
        $usedIndexes  = array(10001, 10004, 10101, 10104);
        $guest        = array(10004, 10104);
        
        $apEntry = parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterBSSEntry')
                          ->assocTable(
                            'arrisRouterBssID',
                            'arrisRouterBssSSID',
                            'arrisRouterBssActive',
                            'arrisRouterBssSecurityMode'
                          );
        $apStations = array();
        foreach($apEntry as $entry)
          {
            if(!in_array($entry->index, $usedIndexes))
                  {
                    continue;
                  }
            $ap                 = new Objects\wlAPEntry;
            $ap->id             = $entry->index;
            $ap->enabled        = $entry->arrisRouterBssActive == 1;
            $ap->bssid          = parent::phyAddr( $entry->arrisRouterBssID );
            $ap->ssid           = $entry->arrisRouterBssSSID;
            $ap->security       = self::BindVal('arrisRouterBssSecurityMode', $entry->arrisRouterBssSecurityMode);
            $ap->band           = $entry->index > 10100 ? WL_5G : WL_2G;
            $ap->guest          = in_array($entry->index, $guest);
            $apStations[] = $ap;
          }

          return $apStations;
      }
    
    /**
     *  Print all LAN clients count
     */  
    public static function lanClientsCount()
      {
        $cli              = new Objects\BaseObject;
        $cli->count = count(parent::snmp('RDKB-RG-MIB::rdkbRgIpMgmtLanConnectedClientsEntry')
                            ->assocTable(
                                'rdkbRgIpMgmtLanConnectedClientsActive',
                                array('rdkbRgIpMgmtLanConnectedClientsActive' => 1)
                              ));
        return $cli;
      }
      
    /**
     *  Print all LAN clients (ARP)
     */
    public static function lanClients()
      {
        $lanEntry = parent::snmp('RDKB-RG-MIB::rdkbRgIpMgmtLanConnectedClientsEntry')
                            ->assocTable(
                                'rdkbRgIpMgmtLanConnectedClientsPhysAddr',
                                'rdkbRgIpMgmtLanConnectedClientsAddressSource',
                                'rdkbRgIpMgmtLanConnectedClientsIpv4Addr',
                                'rdkbRgIpMgmtLanConnectedClientsHostName',
                                'rdkbRgIpMgmtLanConnectedClientsInterface',
                                'rdkbRgIpMgmtLanConnectedClientsActive',
                                'rdkbRgIpMgmtLanConnectedClientsRSSI'
                              );
        $wCli = parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWiFiClientInfoEntry')
                          ->assocTable(
                            'arrisRouterWiFiClientInfoMAC',
                            'arrisRouterWiFiClientInfoLastRxPktRate',
                            'arrisRouterWiFiClientInfoLastTxPktRate'
                          );
                          
        $lanClients = array();
        foreach($lanEntry as $entry)
          {
            $cli              = new Objects\lanClientEntry;
            $cli->id          = $entry->index;
            $cli->active      = $entry->rdkbRgIpMgmtLanConnectedClientsActive == 1;
            $cli->mac         = parent::phyAddr( $entry->rdkbRgIpMgmtLanConnectedClientsPhysAddr );
            $cli->ipAssoc     = $entry->rdkbRgIpMgmtLanConnectedClientsAddressSource == 'DHCP' ? IP_ASSOC_DHCP : IP_ASSOC_STATIC;
            $cli->ipv4Addr    = $entry->rdkbRgIpMgmtLanConnectedClientsIpv4Addr;
            $cli->hostname    = $entry->rdkbRgIpMgmtLanConnectedClientsHostName;
            $cli->rssi        = $entry->rdkbRgIpMgmtLanConnectedClientsRSSI;
            $cli->vendor      = parent::macVendor( $entry->rdkbRgIpMgmtLanConnectedClientsPhysAddr );
            $cli->interface   = parent::translateInterface( $entry->rdkbRgIpMgmtLanConnectedClientsInterface );
            $cli->intf        = $entry->rdkbRgIpMgmtLanConnectedClientsInterface;
            foreach($wCli as $wl)
              {
                if($cli->mac == parent::phyAddr( $wl->arrisRouterWiFiClientInfoMAC ))
                  {
                    $cli->speed[0]  = $wl->arrisRouterWiFiClientInfoLastRxPktRate / 1000; // mbit
                    $cli->speed[1]  = $wl->arrisRouterWiFiClientInfoLastTxPktRate / 1000; // mbit
                    break;
                  }
              }
            
            $lanClients[] = $cli;
          }
        
        return $lanClients;
      }
      
    /**
     *  Print active WlClients (ARP)
     */
    public static function wlClients()
      {
        $lanEntry = parent::snmp('RDKB-RG-MIB::rdkbRgIpMgmtLanConnectedClientsEntry')
                            ->assocTable(
                                'rdkbRgIpMgmtLanConnectedClientsPhysAddr',
                                'rdkbRgIpMgmtLanConnectedClientsActive',
                                'rdkbRgIpMgmtLanConnectedClientsRSSI',
                                array('rdkbRgIpMgmtLanConnectedClientsActive' => 1)
                              );
        $wCli = parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWiFiClientInfoEntry')
                          ->assocTable(
                            'arrisRouterWiFiClientInfoMAC',
                            'arrisRouterWiFiClientInfoLastRxPktRate',
                            'arrisRouterWiFiClientInfoLastTxPktRate'
                          );
                          
        $clients = array();
        
        foreach($lanEntry as $entry)
          {
            $cli              = new Objects\lanClientEntry;
            $cli->mac         = parent::phyAddr( $entry->rdkbRgIpMgmtLanConnectedClientsPhysAddr );
            $cli->rssi        = $entry->rdkbRgIpMgmtLanConnectedClientsRSSI;
            foreach($wCli as $wl)
              {
                if($cli->mac == parent::phyAddr( $wl->arrisRouterWiFiClientInfoMAC ))
                  {
                    $cli->speed[0]  = $wl->arrisRouterWiFiClientInfoLastRxPktRate / 1000; // mbit
                    $cli->speed[1]  = $wl->arrisRouterWiFiClientInfoLastTxPktRate / 1000; // mbit
                    break;
                  }
              }
            $clients[] = $cli;
          }

        return $clients;
      }
      
    /**
     *  Print LAN network configuration
     */
    public static function lanConfig()
      {
        $lanCfg = parent::snmp('RDKB-RG-MIB::rdkbRgIpMgmtLanEntry')
                          ->assocTable(
                            'rdkbRgIpMgmtLanNetwork',
                            'rdkbRgIpMgmtLanSubnetMask',
                            'rdkbRgIpMgmtLanGateway',
                            'rdkbRgIpMgmtLanDhcpServer',
                            'rdkbRgIpMgmtLanUpnp'
                            );
                            
        $DMZ     = parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterFWCfg')
                          ->getObjects(
                            'arrisRouterFWEnableDMZ',
                            'arrisRouterFWIPAddrDMZ'
                            );
                            
        $virtualServer = parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterFWVirtSrvEntry')
                          ->assocTable(
                            'arrisRouterFWVirtSrvDesc',
                            'arrisRouterFWVirtSrvPortStart',
                            'arrisRouterFWVirtSrvPortEnd',
                            'arrisRouterFWVirtSrvProtoType',
                            'arrisRouterFWVirtSrvIPAddr',
                            'arrisRouterFWVirtSrvLocalPortStart',
                            'arrisRouterFWVirtSrvLocalPortEnd',
                            'arrisRouterFWVirtSrvRowStatus',
                            'arrisRouterFWVirtSrvEnable',
                            'arrisRouterFWVirtSrvProtoType' // INTEGER {udp(0), tcp(1), both(2) }
                            );
        foreach(parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWanCurrentDNSEntry')->assocTable('arrisRouterWanCurrentDNSIPAddrType', 'arrisRouterWanCurrentDNSIPAddr') as $server)
          {
            $cfg->dns[] = $server->arrisRouterWanCurrentDNSIPAddr;
          }
        if(count($lanCfg))
          {
            $lanCfg = $lanCfg[0];
          }
        $cfg                = new Objects\lanConfig;
        $cfg->network       = $lanCfg->rdkbRgIpMgmtLanNetwork;
        $cfg->mask          = $lanCfg->rdkbRgIpMgmtLanSubnetMask;
        $cfg->gateway       = $lanCfg->rdkbRgIpMgmtLanGateway;
        $cfg->dhcpServer    = $lanCfg->rdkbRgIpMgmtLanDhcpServer == 1;
        $cfg->upnp          = $lanCfg->rdkbRgIpMgmtLanUpnp == 1;
        
        $cfg->dmz = new Objects\DMZCfg;
        $cfg->dmz->enabled = $DMZ->arrisRouterFWEnableDMZ == 1;
        $cfg->dmz->ip = parent::hexIP( $DMZ->arrisRouterFWIPAddrDMZ );
        
        
        $cfg->virtualServer = array();
        
        foreach($virtualServer as $row)
          {
            $vs = new Objects\virtualServerRow;
            $vs->id                 = $row->index;
            $vs->type               = !intval( $row->arrisRouterFWVirtSrvProtoType ) ? 'udp' : (intval( $row->arrisRouterFWVirtSrvProtoType ) == 1 ? 'tcp' : 'udp/tcp');
            $vs->descr              = $row->arrisRouterFWVirtSrvDesc;
            $vs->enabled            = $row->arrisRouterFWVirtSrvEnable == 1;
            $vs->publicPortStart    = $row->arrisRouterFWVirtSrvPortStart;
            $vs->publicPortEnd      = $row->arrisRouterFWVirtSrvPortEnd;
            $vs->localPortStart     = $row->arrisRouterFWVirtSrvLocalPortStart;
            $vs->localPortEnd       = $row->arrisRouterFWVirtSrvLocalPortEnd;
            $vs->dest               = parent::hexIP( $row->arrisRouterFWVirtSrvIPAddr );
            
            $cfg->virtualServer[] = $vs;
          }
        

        
        //$virtualServer
        
        return $cfg;
      }
    
    /**
     *  WL scan status
     */
    public static function wlScanStatus()
      {
        $response = new Objects\BaseObject;
        $response->status = parent::snmpget('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWiFiScanResult.0') == 1 ? "running" : "finished";
        
        return $response;
      }
    
    /**
     *  Start wl scan
     *  Modes: 2g, 5g, all
     */
    public static function wlScan($mode = 'all')
      {
        $response = new Objects\BaseObject;
        if(parent::snmpget('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWiFiScanResult.0') == 1)
          {
            $response->status = "in progress";
          }
        else
          {
            parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWiFiStartScan.0')->setInt(3);
            $response->status = "started";
          }

        return $response;
      }

    /**
     *  Result of Wl scan
     */
    public static function wlScanResults()
      {
        $scanResults = new Objects\BaseObject;
        $scanResults->results = array();

        $resultEntry = parent::snmp('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWiFiScanResultEntry')
                          ->assocTable(
                            'arrisRouterWiFiScanSSID',
                            'arrisRouterWiFiScanChannel',
                            'arrisRouterWiFiScanRSSI',
                            'arrisRouterWiFiScanMAC',
                            'arrisRouterWiFiScanNoise',
                            'arrisRouterWiFiScanOperatingChannelBandwidth',
                            'arrisRouterWiFiScanSecurityModeEnabled'
                          );
        foreach($resultEntry as $entry)
          {
            $wl                   = new Objects\wlScanEntry;
            $wl->id               = $entry->index;
            $wl->channel          = $entry->arrisRouterWiFiScanChannel;
            $wl->bssid            = parent::phyAddr( $entry->arrisRouterWiFiScanMAC );
            $wl->vendor           = parent::macVendor( $entry->arrisRouterWiFiScanMAC );
            $wl->ssid             = $entry->arrisRouterWiFiScanSSID;
            $wl->rssi             = $entry->arrisRouterWiFiScanRSSI;
            $wl->bw               = self::BindVal('arrisRouterWiFiScanOperatingChannelBandwidth', $entry->arrisRouterWiFiScanOperatingChannelBandwidth);
            $wl->snr              = $entry->arrisRouterWiFiScanNoise;
            $wl->security         = $entry->arrisRouterWiFiScanSecurityModeEnabled > 1 ? true : false;
            
            $scanResults->results[] = $wl;
          }
          
        return $scanResults;
      }
    
    /**
     *  WanMode
     */
    public static function routerBridge()
      {
        $_ = new Objects\BaseObject;
        $_->modes = array(
          0 => 'bridge',
          1 => 'router (ipv4)',
          2 => 'router (ipv6)',
          3 => 'router (dualStack)'
        );
        $_->mode = @ $_->modes[ parent::snmpget('ARRIS-ROUTER-DEVICE-MIB::arrisRouterWanIPProvMode.0') ] ?: NULL;
        return $_;
      }

    /**
     *  Set Wireless channel
     */
    public static function wlChannelSet( Objects\wlChannelSet $ch )
      {
        if($ch->interface === null)
          {
            return array('exception' => 'blahblah... no interface selected...'); // TODO: append to TRACY logger!
          }
        $ch->status = parent::snmpsetInt('CLAB-WIFI-MIB::clabWIFIRadioEntry.' . $ch->interface, $ch->channel);
        return $ch;
      }
    
    
    

  }
  
  
?>