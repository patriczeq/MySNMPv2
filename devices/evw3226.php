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
 *  UBEE EVW3226 private SNMP enterprise
 *  See SNMP syntax hints in MIB files...
 */
class evw3226 extends MySNMPPackage implements Objects\CableModemCalls
  {
    /**
     * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ HEADER
     */
    
    /**
     * List of used MIBs
     */
    const MIBs = array(
      'UBEE-CM-RG-MIB',
      'UBEE-CLABWIFI-MIB',
      'UBEE-SIP-MTA-MGMT-MIB'
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
        'clabWIFIAccessPointSecurityModeEnabled' => array(
          1 => 'none',
          2 => 'wep64',
          3 => 'wep128',
          
          4 => 'wpaPsk',
          5 => 'wpa2Psk',
          6 => 'wpaPskwpa2Psk',
          
          7 => 'wpaEnterprise',
          8 => 'wpa2Enterprise',
          9 => 'wpawpa2Enterprise'
        )
    );
    
    /**
     * Interfaces MAC DIFF (RF INF => MTA/eRouter)
     */
    const macdiff = array(
      'mta'     => 2,
      'eRouter' => 3
    );
    
    /**
     * List of supported calls
     */
    const supported = array(
      'wlRadio',
      'wlAPs',
      //'lanClientsCount',
      //'routerBridge'
      //'lanConfig',
      //'wlScanStatus',
      //'wlScan',
      //'wlScanResults'
      'lanClients',
      'lanClientsCount',
      'wlClients',
      'routerBridge',
      'mtaInfo',
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
     
    public static function lanClients()
      {
        $ubee_lan = parent::snmp('UBEE-CM-RG-MIB::ubeeRgConnectedDevicesEntry')
                      ->assocTable(
                        'ubeeRgConnectedDevicesDeviceName',
                        'ubeeRgConnectedDevicesDeviceMACAddress',
                        'ubeeRgConnectedDevicesDeviceIPAddress',
                        'ubeeRgConnectedDevicesDeviceConnectionType',
                        //'ubeeRgConnectedDevicesDeviceLeaseTime',
                        'ubeeRgConnectedDevicesDeviceLeaseExpireTime'
                      );
        $rssi = parent::snmp('UBEE-CLABWIFI-MIB::clabWIFIAssociatedDeviceEntry')
                      ->assocTable(
                        'clabWIFIAssociatedDeviceActive',
                        'clabWIFIAssociatedDeviceMACAddress',
                        'clabWIFIAssociatedDeviceSignalStrength',
                        'clabWIFIAssociatedDeviceLastDataDownlinkRate',
                        'clabWIFIAssociatedDeviceLastDataUplinkRate'
                      );
        $wlMode = parent::snmp('UBEE-CLABWIFI-MIB::clabWIFIRadioEntry')
                      ->assocTable(
                        'clabWIFIRadioOperatingFrequencyBand',
                        'clabWIFIRadioEnable',
                        array('clabWIFIRadioEnable' => 1)
                      )[0]->clabWIFIRadioOperatingFrequencyBand ?: 1 === 1 ? '2.4Ghz' : '5Ghz';
        
        $lanClients = array();
        foreach($ubee_lan as $entry)
          {
            $cli              = new Objects\lanClientEntry;
            $cli->id          = $entry->index;
            $cli->mac         = parent::phyAddr( $entry->ubeeRgConnectedDevicesDeviceMACAddress );
            $cli->ipv4Addr    = $entry->ubeeRgConnectedDevicesDeviceIPAddress;
            $cli->hostname    = $entry->ubeeRgConnectedDevicesDeviceName;
            $cli->rssi        = -200;
            $cli->vendor      = parent::macVendor( $entry->ubeeRgConnectedDevicesDeviceMACAddress );
            //$cli->leaseCreate = parent::hexDateTimeFormat( $entry->ubeeRgConnectedDevicesDeviceLeaseTime );
            $cli->leaseExpire = parent::hexDateTimeFormat( $entry->ubeeRgConnectedDevicesDeviceLeaseExpireTime );
            foreach($rssi as $rs)
              {
                if(@$rs->clabWIFIAssociatedDeviceMACAddress ?: 0 == @$entry->ubeeRgConnectedDevicesDeviceMACAddress ?: 1)
                  {
                    $cli->rssi      = $rs->clabWIFIAssociatedDeviceSignalStrength * -1;
                    $cli->speed[0]  = $rs->clabWIFIAssociatedDeviceLastDataDownlinkRate;
                    $cli->speed[1]  = $rs->clabWIFIAssociatedDeviceLastDataUplinkRate;
                    $cli->interface = parent::translateInterface( 'wifi ' . $wlMode );
                    $cli->active    = $rs->clabWIFIAssociatedDeviceActive == 1;
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
        $wifiClients  = parent::snmp('UBEE-CLABWIFI-MIB::clabWIFIAssociatedDeviceEntry')
                          ->assocTable(
                            'clabWIFIAssociatedDeviceActive',
                            'clabWIFIAssociatedDeviceMACAddress',
                            'clabWIFIAssociatedDeviceSignalStrength',
                            'clabWIFIAssociatedDeviceLastDataDownlinkRate',
                            'clabWIFIAssociatedDeviceLastDataUplinkRate',
                            array('clabWIFIAssociatedDeviceActive' => 1)
                          );
        $clients = array();
        
        foreach($wifiClients as $entry)
          {
            $cli              = new Objects\lanClientEntry;
            $cli->mac         = parent::phyAddr( $entry->clabWIFIAssociatedDeviceMACAddress );
            $cli->rssi        = $entry->clabWIFIAssociatedDeviceSignalStrength;
            $cli->speed[0]    = $entry->clabWIFIAssociatedDeviceLastDataDownlinkRate / 1000000;
            $cli->speed[1]    = $entry->clabWIFIAssociatedDeviceLastDataUplinkRate / 1000000;
            
            $clients[] = $cli;
          }
        return $clients;
      }
        
    /**
     *  Print all LAN clients count
     */  
    public static function lanClientsCount()
      {
        $cli              = new Objects\BaseObject;
        $DHCPLeases       = parent::snmp('UBEE-CM-RG-MIB::ubeeRgConnectedDevicesEntry')
                            ->assocTable(
                              'ubeeRgConnectedDevicesDeviceIPAddress'
                            );
                            
        $cli->count       = count($DHCPLeases);
        
        return $cli;
      }
    /**
     *  Wireless interfaces, using predefined object wlRadioInterface
     * 
     *   UBEE-CABLE-MODEM-DATA-MIB:: ubeeWifi24G5GSwitchControl - 2.4/5ghz
     */
    public static function wlRadio()
      {

        $intfEntry = parent::snmp('CLAB-WIFI-MIB::clabWIFIRadioEntry')->assocTable(
                                'clabWIFIRadioEnable',
                                'clabWIFIRadioOperatingFrequencyBand',
                                'clabWIFIRadioChannel',
                                'clabWIFIRadioAutoChannelEnable',
                                'clabWIFIRadioOperatingChannelBandwidth',
                                'clabWIFIRadioTransmitPower',
                                'clabWIFIRadioChannelsInUse'
                          );
        $radioInterfaces = array();
        foreach($intfEntry as $entry)
          {
            $if                 = new Objects\wlRadioInterface;
            $if->id             = $entry->index;
            $if->enabled        = $entry->clabWIFIRadioEnable == 1;
            $if->channel        = intval( $entry->clabWIFIRadioChannelsInUse );
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
    public static function wlAPs()
      {
        $apEntry = parent::snmp('CLAB-WIFI-MIB::clabWIFISSIDEntry')
                          ->assocTable(
                            'clabWIFISSIDBSSID',
                            'clabWIFISSIDEnable',
                            'clabWIFISSIDSSID',
                            array('clabWIFISSIDEnable' => 1)
                          );

        $apStations = array();
        foreach($apEntry as $entry)
          {
            $ap                 = new Objects\wlAPEntry;
            $ap->id             = $entry->index;
            $ap->enabled        = $entry->clabWIFISSIDEnable == 1;
            $ap->bssid          = parent::phyAddr( $entry->clabWIFISSIDBSSID );
            $ap->ssid           = $entry->clabWIFISSIDSSID;
            $ap->security       = self::BindVal('clabWIFIAccessPointSecurityModeEnabled', parent::snmpget('CLAB-WIFI-MIB::clabWIFIAccessPointSecurityModeEnabled', $entry->index) );
            $apStations[] = $ap;
          }

          return $apStations;
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
          0 => 'bridge',
          1 => 'nat',
          2 => 'router',
          3 => 'natRouter',
        );
        $_->mode = @ $modes[ parent::snmpget('UBEE-CM-RG-MIB::ubeeRgDeviceOperMode.0') ] ?: NULL;
        return $_;
      }
    
    /**
     * MTA
     */
    
    public static function mtaInfo()
      {

        $nums = parent::snmp('UBEE-SIP-MTA-MGMT-MIB::profileEndPntEntry')
                                        ->assocTable(
                                            'profileEndPntAuthUserName',
                                            'profileEndPntEnabled'
                                        );

        $mta = array();

        foreach($nums as $i=>$d)
          {
            $line = new Objects\BaseObject;
            $line->prov   = $d->profileEndPntEnabled == 1;
            $line->num    = $d->profileEndPntAuthUserName;
            
            $mta[] = $line;
          }
        
        return array(
            "num" => $mta,
            "opt" => array(array(), array())
          );
      }
  }
  
  
?>