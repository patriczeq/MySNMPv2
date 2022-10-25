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
 *  Cisco EPC3925 private SNMP enterprise
 *  See SNMP syntax hints in MIB files...
 */
class epc3925 extends MySNMPPackage implements Objects\CableModemCalls
  {
    /**
     * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ HEADER
     */
    
    /**
     * List of used MIBs
     */
    const MIBs = array(
      'CLAB-WIFI-MIB',
      'SA-RG-MIB'
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
      'saRgDot11BssSecurityMode' => array(
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
      'mta'     => 2,
      'eRouter' => 4
    );
    
    /**
     * List of supported calls
     */
    const supported = array(
      //'wan',
      //'wlRadio',
      'wlAPs',
      'lanClientsCount',
      'lanClients',
      'routerBridge',
      'lanConfig',
      //'wlScanStatus',
      //'wlScan',
      //'wlScanResults'
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
        return new Objects\UnsupportedMethod( __FUNCTION__, parent::$code );
      }
    
    /**
     *  Print wireless APs ARG all -> false for print only Enabled
     */
    public static function wlAPs()
      {
        $apEntry = parent::snmp('SA-RG-MIB::saRgDot11BssEntry')
                          ->assocTable(
                            'saRgDot11BssId',
                            'saRgDot11BssEnable',
                            'saRgDot11BssSsid',
                            'saRgDot11BssSecurityMode',
                            array('saRgDot11BssEnable' => 1)
                          );
        $apStations = array();
        foreach($apEntry as $entry)
          {
            $ap                 = new Objects\wlAPEntry;
            $ap->id             = $entry->index;
            $ap->enabled        = $entry->saRgDot11BssEnable == 1;
            $ap->bssid          = parent::phyAddr( $entry->saRgDot11BssId );
            $ap->ssid           = parent::hexStr( $entry->saRgDot11BssSsid );
            $ap->security       = self::BindVal('saRgDot11BssSecurityMode', $entry->saRgDot11BssSecurityMode);
            
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
          $cli->count = count(parent::snmp('SA-RG-MIB::saRgIpMgmtLanAddrEntry')
                              ->assocTable(
                                'saRgIpMgmtLanAddrInterface'
                              ));
          return $cli;
        }
        
    /**
     *  Print all LAN clients (ARP)
     */
    public static function lanClients()
      {
        $CISCO_LAN = parent::snmp('SA-RG-MIB::saRgIpMgmtLanAddrEntry')
                      ->assocTable(
                        'saRgIpMgmtLanAddrIp',
                        'saRgIpMgmtLanAddrPhysAddr',
                        'saRgIpMgmtLanAddrHostName',
                        'saRgIpMgmtLanAddrLeaseExpireTime',
                        'saRgIpMgmtLanAddrInterface'
                      );
        $lanClients = array();
        foreach($CISCO_LAN as $entry)
          {
            $cli              = new Objects\lanClientEntry;
            $cli->id          = $entry->index;
            //$cli->active      = $entry->rdkbRgIpMgmtLanConnectedClientsActive == 1;
            $cli->mac         = parent::phyAddr( $entry->saRgIpMgmtLanAddrPhysAddr );
            //$cli->ipAssoc     = $entry->rdkbRgIpMgmtLanConnectedClientsAddressSource == 'DHCP' ? IP_ASSOC_DHCP : IP_ASSOC_STATIC;
            $cli->ipv4Addr    = $entry->saRgIpMgmtLanAddrIp;
            $cli->hostname    = $entry->saRgIpMgmtLanAddrHostName;
            $cli->leaseExpire = parent::hexDateTimeFormat( $entry->saRgIpMgmtLanAddrLeaseExpireTime );
            //$cli->rssi        = -200;
            $cli->vendor      = parent::macVendor( $entry->saRgIpMgmtLanAddrPhysAddr );
            $cli->interface   = parent::translateInterface( $entry->saRgIpMgmtLanAddrInterface );
            $cli->intf        = $entry->saRgIpMgmtLanAddrInterface;

            $lanClients[] = $cli;
          }
        
        return $lanClients;
      }

    /**
     *  Print LAN network configuration
     */
    public static function lanConfig()
      {
        $lanCfg = parent::snmp('SA-RG-MIB::saRgIpMgmtLanEntry')
                          ->assocTable(
                            'saRgIpMgmtLanNetwork',
                            'saRgIpMgmtLanSubnetMask',
                            'saRgIpMgmtLanGateway',
                            'saRgIpMgmtLanDhcpServer',
                            'saRgIpMgmtLanUpnp'
                            );

                            
        if(count($lanCfg))
          {
            $lanCfg = $lanCfg[0];
          }
        $cfg                = new Objects\lanConfig;
        $cfg->network       = $lanCfg->saRgIpMgmtLanNetwork;
        $cfg->mask          = $lanCfg->saRgIpMgmtLanSubnetMask;
        $cfg->gateway       = $lanCfg->saRgIpMgmtLanGateway;
        $cfg->dhcpServer    = $lanCfg->saRgIpMgmtLanDhcpServer == 1;
        $cfg->upnp          = $lanCfg->saRgIpMgmtLanUpnp == 1;

        return $cfg;
      }
    
    /**
     *  WL scan status
     */
    public static function wlScanStatus()
      {
        return new Objects\UnsupportedMethod( __FUNCTION__, parent::$code );
      }
      
    /**
     *  Start wl scan
     */
    public static function wlScan()
      {
        return new Objects\UnsupportedMethod( __FUNCTION__, parent::$code );
      }
    
    /**
     *  Result of Wl scan
     */
    public static function wlScanResults()
      {
        return new Objects\UnsupportedMethod( __FUNCTION__, parent::$code );
      }
    
    /**
     *  WanMode
     */
    public static function routerBridge()
      {
        $_ = new Objects\BaseObject;
        $modes = array(
          1 => 'bridge',
          2 => 'router',
          3 => 'l2tpv2-client',
          4 => 'mixed',
          5 => 'vlan'
        );
        $_->mode = @ $modes[ parent::snmpget('SA-RG-MIB::saRgIpMgmtLanMode.32') ] ?: NULL;
        return $_;
      }
  }
  
  
?>