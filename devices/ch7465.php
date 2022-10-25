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
 *  Compal (CBN) CH7465LG / CH7465VF private SNMP enterprise
 *  See SNMP syntax hints in MIB files...
 */
class ch7465 extends MySNMPPackage implements Objects\CableModemCalls
  {
    /**
     * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ HEADER
     */
    
    /**
     * List of used MIBs
     */
    const MIBs = array(
      'CBN-GATEWAY-MIB',
      'CBN-WiFiMgmt-MIB',
      'CBN-LAN-USER-MIB',
      'CBN-EMTA-MGMT-MIB',
      'CBN-DOCSIS-CONFIG-MIB'
    );
    
    /**
     * OID values Bind
     */
    const OIDBind = array(
      'wifiMgmtBandWidth' => array(
        1 => '20MHz',
        2 => '40MHz',
        3 => '80MHz'
        ),
      'wifiMgmtBssSecurityMode' => array(
        0 => 'disabled',
        1 => 'wep64',
        2 => 'wep128',
        3 => 'wpaPsk',
        4 => 'wpa2Psk',
        5 => 'wpaEnterprise',
        6 => 'wpa2Enterprise',
        7 => 'radiusWep',
        8 => 'wpaPskwpa2Psk',
        9 => 'wpaEnterprisewpa2Enterprise'
       ),
      'cmLanUserIngressInterface' => array(
        1  => 'other',
        2  => 'ethernet',
        3  => 'wlanMainSsid',
        4  => 'wlanGuestSsid1',
        5  => 'wlanGuestSsid2',
        6  => 'wlanGuestSsid3',
        7  => 'wlan5gMainSsid',
        8  => 'wlan5gGuestSsid1',
        9  => 'wlan5gGuestSsid2',
        10 => 'wlan5gGuestSsid3',
        11 => 'wlanGuestSsid4',
        12 => 'wlanGuestSsid5',
        13 => 'wlanGuestSsid6',
        14 => 'wlanGuestSsid7',
        15 => 'wlanGuestSsid8',
        16 => 'wlanGuestSsid9',
        17 => 'wlanGuestSsid10',
        18 => 'wlanGuestSsid11',
        19 => 'wlanGuestSsid12',
        20 => 'wlanGuestSsid13',
        21 => 'wlanGuestSsid14',
        22 => 'wlanGuestSsid15',
        23 => 'wlan5gGuestSsid4',
        24 => 'wlan5gGuestSsid5',
        25 => 'wlan5gGuestSsid6',
        26 => 'wlan5gGuestSsid7',
        27 => 'wlan5gGuestSsid8',
        28 => 'wlan5gGuestSsid9',
        29 => 'wlan5gGuestSsid10',
        30 => 'wlan5gGuestSsid11',
        31 => 'wlan5gGuestSsid12',
        32 => 'wlan5gGuestSsid13',
        33 => 'wlan5gGuestSsid14',
        34 => 'wlan5gGuestSsid15'
      ),
      'wifiMgmtApNeighborScan' => array(
        1 => 'running',
        2 => 'done'
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
      'wlRadio',
      'wlAPs',
      'lanClientsCount',
      'lanClients',
      'wlClients',
      'lanConfig',
      'wlCfg',
      'wlScanStatus',
      'wlScan',
      'wlScanResults',
      'mtaNumbers',
      'routerBridge',
      'setRouterBridge',
      'mtaInfo',
      'mtaReg',
      'mtaProv',
      'cmModeSet',
      
      'cmStats'
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
       * MTA phone numbers
       * - slow if you dont know MTA IP - CpeInfo ~4sec + SipID 40ms
       */
      public static function mtaNumbers($mtaIP = NULL)
        {
          $mtaIP = $mtaIP ?: parent::mtaIP();
          $data = array();
          if($mtaIP)
            {
              parent::setHost($mtaIP);

              foreach(parent::snmp('CBN-EMTA-MGMT-MIB::cbnSipEndPntCfgEntry')
                        ->assocTable('cbnSipEndPntCfgDisplayName') as $n)
                {
                  if($n->cbnSipEndPntCfgDisplayName)
                    {
                      $data[] = intval($n->cbnSipEndPntCfgDisplayName);
                    }
                }
            }
          return array("mta" => $mtaIP, "data" => $data);
        }
        

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
          $intfEntry = parent::snmp('CBN-WiFiMgmt-MIB::wifiMgmtIntfEntry')
                              ->assocTable(
                                  'wifiMgmtCurrentChannel', 
                                  'wifiMgmtChannelSetting', 
                                  'wifiMgmtBand', 
                                  'wifiMgmtBandWidth', 
                                  'wifiMgmtPhyTxPowerLevel', 
                                  'wifiMgmtStbcEnable'
                              );
          $radioInterfaces = array();
          
          foreach($intfEntry as $entry)
            {
              $if                 = new Objects\wlRadioInterface;
              $if->id             = $entry->index;
              $if->enabled        = $entry->wifiMgmtStbcEnable == 1;
              $if->channel        = $entry->wifiMgmtCurrentChannel;
              $if->autoChannel    = !$entry->wifiMgmtChannelSetting;
              $if->band           = $entry->wifiMgmtBand == 1 ? WL_2G : WL_5G;
              $if->txPower        = 100 - ($entry->wifiMgmtPhyTxPowerLevel * 10);
              $if->bandwidth      = self::BindVal('wifiMgmtBandWidth', $entry->wifiMgmtBandWidth);

              $radioInterfaces[] = $if;
            }
  
          return $radioInterfaces;
        }
      
      
      
      /**
       *  Print wireless APs ARG all -> false for print only Enabled
       */
      public static function wlAPs()
        {
            $usedIndexes  = array(32, 35, 92, 95);
            $guest        = array(35, 95);
            
            $apEntry = parent::snmp('CBN-WiFiMgmt-MIB::wifiMgmtBssEntry')
                              ->assocTable(
                                  'wifiMgmtBssId',
                                  'wifiMgmtBssEnable',
                                  'wifiMgmtBssSsid',
                                  'wifiMgmtBssSecurityMode',
                                  'wifiMgmtBssNetMode'
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
                $ap->enabled        = $entry->wifiMgmtBssEnable == 1;
                $ap->bssid          = parent::phyAddr( $entry->wifiMgmtBssId );
                $ap->ssid           = $entry->wifiMgmtBssSsid;
                $ap->band           = $entry->wifiMgmtBssNetMode < 11 ? '2.4Ghz' : '5Ghz';
                $ap->security       = self::BindVal('wifiMgmtBssSecurityMode', $entry->wifiMgmtBssSecurityMode);
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
          $DHCPLeases       = parent::snmp('CBN-GATEWAY-MIB::gwLanDhcpv4LeaseEntry')
                               ->assocTable(
                                  'gwLanDhcpv4LeaseClientID'
                                );

          $lanUserEntry     = parent::snmp('CBN-LAN-USER-MIB::cmLanUserEntry')
                               ->assocTable(
                                  'cmLanUserIngressInterface'
                                );
          $cli->count       = 0;
          foreach($DHCPLeases as $entry)
            {
              foreach($lanUserEntry as $ue)
                {
                  if($ue->index == $entry->index)
                    {
                      $cli->count++;
                      break;
                    }
                }
            }

          return $cli;
        }
        
        
        
      /**
       *  Print all LAN clients (ARP)
       */
      public static function lanClients()
        {
          $network      = parent::hexIP(parent::snmpget('CBN-GATEWAY-MIB::gwLanIPAddress.0'), '%d.%d.%d');
          $DHCPLeases   = parent::snmp('CBN-GATEWAY-MIB::gwLanDhcpv4LeaseEntry')
                               ->assocTable(
                                  'gwLanDhcpv4LeaseClientID',
                                  'gwLanDhcpv4LeaseHostName',
                                  'gwLanDhcpv4LeaseRowStatus',
                                  'gwLanDhcpv4LeaseLeaseCreateTime',
                                  'gwLanDhcpv4LeaseLeaseExpireTime'
                                );
          
          $lanUserEntry = parent::snmp('CBN-LAN-USER-MIB::cmLanUserEntry')
                               ->assocTable(
                                  'cmLanUserIngressInterface',
                                  'cmLanUserAssociateMethod'
                                );
          $wifiClients  = parent::snmp('CBN-WiFiMgmt-MIB::wifiMgmtClientEntry')
                               ->assocTable(
                                  'wifiMgmtClientMacAddress',
                                  'wifiMgmtRxPhyRate',
                                  'wifiMgmtTxPhyRate',
                                  'wifiMgmtRSSI'
                              );

          $lanClients = array();
          foreach($DHCPLeases as $entry)
            {
              $cli              = new Objects\lanClientEntry;
              $cli->id          = $entry->index;
              $cli->ipv4Addr    = sprintf('%s.%d', $network, $entry->index);
              $cli->active      = $entry->gwLanDhcpv4LeaseRowStatus == 1;
              $cli->mac         = parent::phyAddr( $entry->gwLanDhcpv4LeaseClientID );
              $cli->hostname    = $entry->gwLanDhcpv4LeaseHostName;
              $cli->vendor      = parent::macVendor( $entry->gwLanDhcpv4LeaseClientID );
              $cli->rssi        = -200;
              $cli->leaseCreate = parent::hexDateTimeFormat( $entry->gwLanDhcpv4LeaseLeaseCreateTime );
              $cli->leaseExpire = parent::hexDateTimeFormat( $entry->gwLanDhcpv4LeaseLeaseExpireTime );

              $foundInLan = false;
              
              foreach($lanUserEntry as $ue)
                {
                  
                  if($ue->index == $entry->index)
                    {
                      $cli->interface = parent::translateInterface( self::BindVal('cmLanUserIngressInterface', $ue->cmLanUserIngressInterface) );
                      $cli->intf      = self::BindVal('cmLanUserIngressInterface', $ue->cmLanUserIngressInterface);
                      $cli->ipAssoc   = $ue->cmLanUserAssociateMethod == 1 ? IP_ASSOC_DHCP : IP_ASSOC_STATIC;
                      $foundInLan = true;
                      break;
                    }
                }
                
              if(!$foundInLan)
                {
                  $cli->active = false;
                }
              else  # get RSSI, phyrate
                {
                  foreach($wifiClients as $wl)
                    {
                      if(parent::phyAddr( $wl->wifiMgmtClientMacAddress ) == $cli->mac)
                        {
                          $cli->rssi = $wl->wifiMgmtRSSI;
                          $cli->speed[0] = $wl->wifiMgmtRxPhyRate / 1000000;
                          $cli->speed[1] = $wl->wifiMgmtTxPhyRate / 1000000;
                          break;
                        }
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
          $wifiClients  = parent::snmp('CBN-WiFiMgmt-MIB::wifiMgmtClientEntry')
                               ->assocTable(
                                  'wifiMgmtClientMacAddress',
                                  'wifiMgmtRxPhyRate',
                                  'wifiMgmtTxPhyRate',
                                  'wifiMgmtRSSI'
                              );
          $clients = array();
          
          foreach($wifiClients as $entry)
            {
              $cli              = new Objects\lanClientEntry;
              $cli->mac         = parent::phyAddr( $entry->wifiMgmtClientMacAddress );
              $cli->rssi        = $entry->wifiMgmtRSSI;
              $cli->speed[0]    = $entry->wifiMgmtRxPhyRate / 1000000;
              $cli->speed[1]    = $entry->wifiMgmtTxPhyRate / 1000000;
              
              $clients[] = $cli;
            }
          return $clients;
        }
      
      /**
       *  Print LAN network configuration
       */
      public static function lanConfig()
        {
          $lan  = parent::snmp('CBN-GATEWAY-MIB::gwIPv4Objects')
                               ->getObjects(
                                  'gwLanIPAddress',
                                  'gwLanSubnetMask'
                              );
          $dmz = parent::snmpget('CBN-GATEWAY-MIB::gwNatGamingDMZIpAddr.0');
          $virtualServer = parent::snmp('CBN-GATEWAY-MIB::gwVirtualServerEntry')
                              ->assocTable(
                                  'gwVirtualServerName',
                                  'gwVirtualServerExternalStartPort',
                                  'gwVirtualServerExternalEndPort',
                                  'gwVirtualServerLanAddr',
                                  'gwVirtualServerEnable',
                                  'gwVirtualServerProtocol',
                                  'gwVirtualServerInternalStartPort',
                                  'gwVirtualServerInternalEndPort'
                              );
          $cfg                = new Objects\lanConfig;
          $cfg->virtualServer = array();
          
          foreach(parent::snmp('CBN-GATEWAY-MIB::gwWanDnsServerEntry')->assocTable('gwWanAddrDnsServerAddr') as $server)
            {
              $cfg->dns[] = parent::hexIP( $server->gwWanAddrDnsServerAddr );
            }
            
          foreach(parent::snmp('CBN-GATEWAY-MIB::gwIPv6DnsServerEntry')->assocTable('gwIPv6DnsServerAddr') as $server)
            {
              $cfg->dns[] = parent::IPv6Format( $server->gwIPv6DnsServerAddr );
            }
          
          $net = explode(".", parent::hexIP( $lan->gwLanIPAddress ));
          
          $cfg->network    = $net[0] . '.' . $net[1] . '.' . $net[2] . '.0';
          $cfg->mask       = parent::hexIP( $lan->gwLanSubnetMask );
          $cfg->gateway    = implode(".", $net);
          
          $cfg->dmz = new Objects\DMZCfg;
          $cfg->dmz->enabled = parent::hexIP( $dmz ) !== $cfg->network;
          $cfg->dmz->ip = parent::hexIP( $dmz );
          
          $cfg->upnp = parent::snmpget('CBN-GATEWAY-MIB::gwAdvCfgUPnPEnable.0') == 1;
          $cfg->dhcpServer = parent::snmpget('CBN-GATEWAY-MIB::gwLanDhcpv4Enable.0') == 1;
          
          
          foreach($virtualServer as $row)
          {
            $vs = new Objects\virtualServerRow;
            $vs->id                 = $row->index;
            $vs->type               = intval( $row->gwVirtualServerProtocol ) === 1 ? 'tcp' : (intval( $row->gwVirtualServerProtocol ) == 2 ? 'udp' : 'udp/tcp');
            $vs->descr              = $row->gwVirtualServerName;
            $vs->enabled            = $row->gwVirtualServerEnable == 1;
            $vs->publicPortStart    = $row->gwVirtualServerExternalStartPort;
            $vs->publicPortEnd      = $row->gwVirtualServerExternalEndPort;
            $vs->localPortStart     = $row->gwVirtualServerInternalStartPort;
            $vs->localPortEnd       = $row->gwVirtualServerInternalEndPort;
            $vs->dest               = parent::hexIP( $row->gwVirtualServerLanAddr );
            
            $cfg->virtualServer[] = $vs;
          }
          

          return $cfg;
        }
      
      
      /**
       * WL cfg
       */
      public static function wlCfg()
        {
          $response = new Objects\BaseObject;
          $modes = array(
            1 => '2.4Ghz',
            2 => '2Ghz',
            3 => 'Concurent',
            4 => 'off'
          );
          
          $response->mode = @ $modes[ parent::snmpget('CBN-WiFiMgmt-MIB::wifiMgmtBandMode.0') ] ?: NULL;
          
          return $response;
        }
        
      /**
       *  WL scan status
       */
      public static function wlScanStatus()
        {
          /**
            Set to true(1) to trigger AP scan process. During the scan process, all associated WiFi client will be disconnected.
            The scan process will take around 5 seconds to get the result.
            Always returns false(2) when read.
          */
          $response = new Objects\BaseObject;
          $response->status = parent::snmpget('CBN-WiFiMgmt-MIB::wifiMgmtApNeighborScan.0') == 1 ? 'running' : 'finished';
          
          if($response->status === 'finished')
            {
              sleep(5);
              // coz 5Ghz scan is 2nd scan...
            }
          
          return $response;
        }
      
      /**
       *  Start wl scan
       */
      public static function wlScan()
        {
          
          $response = new Objects\BaseObject;
          if(parent::snmpget('CBN-WiFiMgmt-MIB::wifiMgmtApNeighborScan.0') == 1)
            {
              $response->status = "in progress";
            }
          else
            {
              parent::snmp('CBN-WiFiMgmt-MIB::wifiMgmtApNeighborScan')->setInt(1);
              $response->status = "started";
            }
  
          return $response;

        }
      
      
      
      /**
       *  Result of Wl scan
       */
      public static function wlScanResults()
        {
          $scanResults          = new Objects\BaseObject;
          $scanResults->results = array();
          
          $resultEntry = parent::snmp('CBN-WiFiMgmt-MIB::wifiMgmtApNeighborEntry')
                            ->assocTable(
                              'wifiMgmtApNeighborSsid',
                              'wifiMgmtApNeighborChannel',
                              'wifiMgmtApNeighborRssi',
                              'wifiMgmtApNeighborMacAddress',
                              'wifiMgmtApNeighborPrivacy'
                            );
                            
          foreach($resultEntry ?: array() as $entry)
            {
              $wl            = new Objects\wlScanEntry;
              $wl->id        = $entry->index;
              $wl->channel   = $entry->wifiMgmtApNeighborChannel;
              $wl->bssid     = parent::phyAddr( $entry->wifiMgmtApNeighborMacAddress );
              $wl->vendor    = parent::macVendor( $entry->wifiMgmtApNeighborMacAddress );
              $wl->ssid      = $entry->wifiMgmtApNeighborSsid;
              $wl->rssi      = $entry->wifiMgmtApNeighborRssi;
              $wl->security  = $entry->wifiMgmtApNeighborPrivacy == 1;
              
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
            1 => 'router',
            2 => 'bridge'
          );
          $_->mode = @$_->modes[parent::snmpget('CBN-DOCSIS-CONFIG-MIB::cmRgOperMode.0')] ?: null;
          return $_;
        }
      
      /**
       *  WanMode set
       */
      public static function cmModeSet($mode = null)
        {
          $_ = new Objects\BaseObject;
          $modes = array(
            'router' => 1,
            'bridge' => 2
          );
          if($mode !== "router" && $mode !== "bridge")
            {
              $_->error = "Unknown mode! (".$mode.")";
            }
          else if(self::routerBridge()->mode === $mode)
            {
              $_->error = "Already set as ".$mode;
            }
          else
            {
              //parent::snmp("CBN-DOCSIS-CONFIG-MIB::cmRgOperMode")->set('i', $modes[$mode]);
              //parent::snmp("CBN-DOCSIS-CONFIG-MIB::cmConfigBridgingApplySetting.0")->set('i', 1);
              
              //parent::snmp("DOCS-CABLE-DEVICE-MIB::docsDevResetNow.0")->set('i', 1);
              //$_->set = "sent ".$mode;
              
              $_->error = "CBN-DOCSIS-CONFIG-MIB::cmRgOperMode se přepíše po rebootu... jako kdyby to přepisoval CFG (dojde 2x k rebootu)";

            }
          return $_;
        }
      
      /**
     * MTA
     */
    public static function mtaProv()
      {
        $mta = array();
        $nums = parent::snmp('CBN-EMTA-MGMT-MIB::cbnSipEndPntCfgEntry')
                                        ->assocTable(
                                            'cbnSipEndPntCfgUserId',
                                            'cbnSipEndPntCfgRegistrarStatus'
                                        );
        foreach($nums as $i=>$d)
          {
            $line = new Objects\BaseObject;
            $line->prov   = $d->cbnSipEndPntCfgRegistrarStatus == 1;
            $line->num    = $d->cbnSipEndPntCfgUserId;
            
            $mta[] = $line;
          }
        return $mta;
      }
    
    public static function mtaInfo()
      {
        $nums = parent::snmp('CBN-EMTA-MGMT-MIB::cbnSipEndPntCfgEntry')
                                        ->assocTable(
                                            'cbnSipEndPntCfgUserId',
                                            'cbnSipEndPntCfgRegistrarStatus'
                                        );
        $opt = parent::snmp('CBN-EMTA-MGMT-MIB::cbnSipCallFeatureEntry')
                                        ->assocTable(
                                            array(
                                              'cbnSipCallFeatureDescrText',
                                              'cbnSipCallFeatureEnabled'
                                            )
                                        );
        $mta = array();
        $opts = array(
          array(),
          array()
        ); 
        
        foreach($nums as $i=>$d)
          {
            $line = new Objects\BaseObject;
            $line->prov   = $d->cbnSipEndPntCfgRegistrarStatus == 1;
            $line->num    = $d->cbnSipEndPntCfgUserId;
            
            $mta[] = $line;
          }
        
        foreach($opt as $op)
          {
            $opts[strpos($op->cbnSipCallFeatureDescrText, "Line 1") !== false ? 0 : 1][str_replace(array(" - Line 1", " - Line 2"), "", $op->cbnSipCallFeatureDescrText)] = $op->cbnSipCallFeatureEnabled == 1;
          }
        return array(
            "num" => $mta,
            "opt" => $opts
          );
      }
    
    public static function mtaReg()
      {
        $data = array();
        foreach(parent::snmpwalk('CBN-EMTA-MGMT-MIB::emtaCallSignalingLogEntry') as $id=>$val)
          {
            $data[] = $val;
          }
        return $data;
      }
  }

?>