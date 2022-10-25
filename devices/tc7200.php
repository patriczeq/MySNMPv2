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
 *  Technicolor TC7200 private SNMP enterprise
 *  See SNMP syntax hints in MIB files...
 */
class tc7200 extends MySNMPPackage implements Objects\CableModemCalls
  {
    /**
     * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ HEADER
     */
   
    /**
     * List of used MIBs
     */
    const MIBs = array(
      'CLAB-WIFI-MIB',
      'THOMSON-WIFI-MGMT-MIB',
      'THOMSON-BROADBAND-MIB',
      'THOMSON-CABLEGATEWAY-MIB',
      'TMM-EMTA-SIP-MIB',
      'PKTC-EVENT-MIB'
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
      'cgWifiBssSecurityMode' => array(
          0 => 'disabled',
          1 => 'wep',
          2 => 'wpaPsk',
          3 => 'wpa2Psk',
          4 => 'wpaEnterprise',
          5 => 'wpa2Enterprise',
          6 => 'radiusWep',
          7 => 'wpaWpa2Psk',
          8 => 'wpaWpa2Enterprise'
        )
    );
    
    /**
     * Interfaces MAC DIFF (RF INF => MTA/eRouter)
     */
    const macdiff = array(
      'mta'     => 1,
      'eRouter' => 2
    );
    
    /**
     * List of supported calls
     */
    const supported = array(
      //'wan',
      'wlRadio',
      'wlAPs',
      'lanClientsCount',
      'lanClients',
      'wlClients',
      'routerBridge',
      //'lanConfig',
      'wlScanStatus',
      'wlScan',
      'wlScanResults',
      'mtaInfo',
      'mtaReg',
      'mtaEventlog'
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
     *  Wireless interfaces, using predefined object wlRadioInterface
     */
    public static function wlRadio()
      {
        // tc7200 umí zapnout WiFi interface pouze v singleBand
        
        $intfEntry = parent::snmp('CLAB-WIFI-MIB::clabWIFIRadioEntry')->assocTable(
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
            $if->channel        = $entry->clabWIFIRadioAutoChannelEnable == 1 ? intval(parent::snmpget('THOMSON-WIFI-MGMT-MIB::cgWifiCurrentUsedChannel', $entry->clabWIFIRadioOperatingFrequencyBand == 1 ? 32 : 92)) : $entry->clabWIFIRadioChannel;
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
        $apEntry = parent::snmp('THOMSON-WIFI-MGMT-MIB::cgWifiBssEntry')
                          ->assocTable(
                            'cgWifiBssId',
                            'cgWifiBssEnable',
                            'cgWifiBssSsid',
                            'cgWifiBssSecurityMode',
                            !$all && array('cgWifiBssEnable' => 1)
                          );
        $apStations = array();
        foreach($apEntry as $entry)
          {
            $ap                 = new Objects\wlAPEntry;
            $ap->id             = $entry->index;
            $ap->enabled        = $entry->cgWifiBssEnable == 1;
            $ap->bssid          = parent::phyAddr( $entry->cgWifiBssId );
            $ap->ssid           = $entry->cgWifiBssSsid;
            $ap->band           = 'native';
            $ap->security       = self::BindVal('cgWifiBssSecurityMode', $entry->cgWifiBssSecurityMode);
            
            $apStations[] = $ap;
          }

          return $apStations;
      }
    
    /**
     *  Print all LAN clients count
     */  
    public static function lanClientsCount()
      {
        $TH_LAN = parent::snmp('THOMSON-CABLEGATEWAY-MIB::cgConnectedClientsTableEntry') # už fakt nevim kde hledat... cableGatewayBase -> 56 ??
                            ->assocTable(
                              'cgConnectedClientsMacAddr',
                              'cgConnectedClientsEntryInterface'
                            );
        $TH_WL = parent::snmp('THOMSON-WIFI-MGMT-MIB::cgWifiConnectedDevicesEntry')
                      ->assocTable(
                          'cgWifiConnectedDevicesMacAddress',
                          'cgWifiConnectedDevicesRssi'
                        );
        $count = 0;
        foreach($TH_LAN as $cli)
          {
            if(strpos(strtolower($cli->cgConnectedClientsEntryInterface), 'wifi') !== false)
                {
                  foreach($TH_WL as $wl)
                    {
                      if($cli->mac == parent::phyAddr( $wl->cgWifiConnectedDevicesMacAddress ))
                        {
                          $count++;
                          break;
                        }
                    }
                }
              else
                {
                  $count++;
                }
          }
                        
        $cli              = new Objects\BaseObject;
        $cli->count = $count;
        return $cli;
      }
        
    /**
     *  Print all LAN clients (ARP)
     */
    public static function lanClients()
      {
        
        $TH_LAN = parent::snmp('THOMSON-CABLEGATEWAY-MIB::cgConnectedClientsTableEntry')
                      ->assocTable(
                         'cgConnectedClientsHostName',
                         'cgConnectedClientsMacAddr',
                         'cgConnectedClientsIPAddr',
                         'cgConnectedClientsEntryInterface',
                         'cgConnectedClientsLeaseTime',
                         'cgConnectedClientsExpireTime',
                         'cgConnectedClientsRemainingTime'
                      );
        $TH_WL = parent::snmp('THOMSON-WIFI-MGMT-MIB::cgWifiConnectedDevicesEntry')
                      ->assocTable(
                          'cgWifiConnectedDevicesMacAddress',
                          'cgWifiConnectedDevicesRssi',
                          'cgWifiAssociatedDeviceLastDataDownlinkRate',
                          'cgWifiAssociatedDeviceLastDataUplinkRate'
                        );
        $lanClients = array();
        foreach($TH_LAN as $entry)
          {
            $cli              = new Objects\lanClientEntry;
            $cli->id          = $entry->index;
            $cli->mac         = parent::phyAddr( $entry->cgConnectedClientsMacAddr );
            $cli->ipv4Addr    = $entry->cgConnectedClientsIPAddr;
            $cli->hostname    = $entry->cgConnectedClientsHostName;
            $cli->vendor      = parent::macVendor( $entry->cgConnectedClientsMacAddr );
            $cli->leaseCreate = parent::hexDateTimeFormat( $entry->cgConnectedClientsLeaseTime );
            $cli->leaseExpire = parent::hexDateTimeFormat( $entry->cgConnectedClientsExpireTime );
            $cli->interface   = parent::translateInterface( $entry->cgConnectedClientsEntryInterface );
            $cli->intf        = $entry->cgConnectedClientsEntryInterface;
            
            foreach($TH_WL as $wl)
              {
                if($cli->mac == parent::phyAddr( $wl->cgWifiConnectedDevicesMacAddress ))
                  {
                    $cli->rssi      = $wl->cgWifiConnectedDevicesRssi;
                    $cli->speed[0]  = $wl->cgWifiAssociatedDeviceLastDataDownlinkRate / 1000; // mbit
                    $cli->speed[1]  = $wl->cgWifiAssociatedDeviceLastDataUplinkRate / 1000; // mbit
                    break;
                  }
              }
            if(strpos(strtolower($cli->intf), 'wifi') !== false && $cli->rssi == -200)
              {
                $cli->active = false;
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
        $TH_WL = parent::snmp('THOMSON-WIFI-MGMT-MIB::cgWifiConnectedDevicesEntry')
                      ->assocTable(
                          'cgWifiConnectedDevicesMacAddress',
                          'cgWifiConnectedDevicesRssi',
                          'cgWifiAssociatedDeviceLastDataDownlinkRate',
                          'cgWifiAssociatedDeviceLastDataUplinkRate'
                        );
        $clients = array();
        
        foreach($TH_WL as $entry)
          {
            $cli              = new Objects\lanClientEntry;
            $cli->mac         = parent::phyAddr( $entry->cgWifiConnectedDevicesMacAddress );
            $cli->rssi        = $entry->cgWifiConnectedDevicesRssi;
            $cli->speed[0]    = $entry->cgWifiAssociatedDeviceLastDataDownlinkRate / 1000000;
            $cli->speed[1]    = $entry->cgWifiAssociatedDeviceLastDataUplinkRate / 1000000;
            
            $clients[] = $cli;
          }
        return $clients;
      }
    
    /**
     *  Print LAN network configuration
     */
    public static function lanConfig()
      {
        return new Objects\lanConfig;
      }  
    
    /**
     *  WL scan status
     */
    public static function wlScanStatus()
      {
        $response = new Objects\BaseObject;
        $response->status = parent::snmp('THOMSON-WIFI-MGMT-MIB::cgWifiApsScan', 32)->get() == 2 ? "finished" : "running";
        
        return $response;
      }
      
    /**
     *  Start wl scan (32 - 2.4Ghz, 92 - 5Ghz) - pokud má ještě někdo tc7200, stejně jede na 2.4Ghz - 5ku budu ignorovat!
     */
    public static function wlScan()
      {
        $response = new Objects\BaseObject;
        parent::snmp('THOMSON-WIFI-MGMT-MIB::cgWifiApsScan', 32)->setInt(1);
        $response->status = "started";

        return $response;
      }
    
    /**
     *  Result of Wl scan
     */
    public static function wlScanResults()
      {
        $scanResults          = new Objects\BaseObject;
        $scanResults->results = array();
        
        $resultEntry = parent::snmp('THOMSON-WIFI-MGMT-MIB::cgWifiApsScanResultsEntry')
                                          ->assocTable(
                                              array(
                                                '1.32' => 'cgWifiValid',
                                                '2.32' => 'cgWifiNetworkName',
                                                '3.32' => 'cgWifiSecurityMode',
                                                '5.32' => 'cgWifiRssi',
                                                '6.32' => 'cgWifiChannel',
                                                '7.32' => 'cgWifiMacAddress',
                                              ),
                                              array('cgWifiValid' => 1)
                                            );
        foreach($resultEntry ?: array() as $row)
          {
            /*if(!$row->cgWifiValid)
              {
                continue;
              }*/
            $wl = new Objects\wlScanEntry;
            
            $wl->id          = $row->index;
            $wl->channel     = $row->cgWifiChannel;
            $wl->bssid       = parent::phyAddr( $row->cgWifiMacAddress );
            $wl->vendor      = parent::macVendor( $row->cgWifiMacAddress );
            $wl->ssid        = $row->cgWifiNetworkName;
            $wl->rssi        = $row->cgWifiRssi;
            $wl->SetSecurity( $row->cgWifiSecurityMode );
            
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
        $_->mode = parent::snmpget('THOMSON-CABLEGATEWAY-MIB::cgRouterOperMode.0') == 1 ? "bridge" : "router";
        /*if(strpos(parent::$sys, "STD6.02.4") !== false || strpos(parent::$sys, "STD6.02.4") !== false)
          {
            $_->mode = parent::snmpget('THOMSON-CABLEGATEWAY-MIB::cgRouterOperMode.0') == 1 ? "bridge" : "router";
          }
        else
          {
            $_->mode = parent::snmpget('BRCM-RG-IP-MIB::rgIpPrimaryLANIfBridgeMode.0') == 1 ? "bridge" : "router";
          }*/

        return $_;
      }
    
    /**
     * MTA
     */
    
    public static function mtaInfo()
      {

        $nums = parent::snmp('TMM-EMTA-SIP-MIB::tmmEmtaSipEndPointEntry')
                                        ->assocTable(
                                            'tmmEmtaSipEndPointProxyUsername',
                                            'tmmEmtaSipEndPointRegisterStatus'
                                        );
        $opt = parent::snmp('.1.3.6.1.4.1.4413.2.2.2.1.6.8.3.1.1')
                                        ->assocTable(
                                            array(
                                              6 => 'name',
                                              7 => 'value'
                                            )
                                        );
        $mta = array();
        
        $opts = array(
          array(),
          array()
        );
        
        foreach($opt as $op)
          {
            $opts[strpos($op->name, "Line 1") !== false ? 0 : 1][str_replace(array(" - Line 1", " - Line 2"), "", $op->name)] = $op->value == 1;
          }
        
        
        foreach($nums as $i=>$d)
          {
            $line = new Objects\BaseObject;
            $line->prov   = $d->tmmEmtaSipEndPointRegisterStatus == 1;
            $line->num    = $d->tmmEmtaSipEndPointProxyUsername;
            
            $mta[] = $line;
          }
        
        return array(
            "num" => $mta,
            "opt" => $opts
          );
      }
      
    public static function mtaReg()
      {
        $data = array();
        foreach(parent::snmpwalk('.1.3.6.1.4.1.4491.2.2.3.5.1.1.5') as $id=>$val) // .1.3.6.1.4.1.4491.2.2.3.5.1.1.5
          {
            $data[] = $val;
          }
        return $data;
      }
    
    public static function mtaEventlog()
      {
        $data = array();
        foreach(parent::snmpwalk('PKTC-EVENT-MIB::pktcDevEvText') as $id=>$val) // .1.3.6.1.4.1.4491.2.2.3.4.1.1.6
          {
            $data[] = $val;
          }
        return $data;
      }

  }
  
  
?>