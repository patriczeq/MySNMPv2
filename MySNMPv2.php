<?php
/**
 *   MySNMPv2 lib
 * 
 *    @ Copyright R3D3 2022
 *
 *   - CM-SNMP communicator
 * 
 */ 
namespace MySNMPv2;
use dibi;

class MySNMPPackage extends MySNMPv2
  {
    protected static $SNMPConnector, $code, $binds;
    public static $sys = "";
    public static $cmInfo  = null;
    /*************************************************************************************************************************************************
     * 
     */
    function __construct($host = null, $security = null, $version = 1, $timeout = 5, $code = null)
      {
        self::$binds = new Objects\SnmpBinds;
        self::$SNMPConnector = new parent();
        self::$SNMPConnector->snmp_session($host, $security, $version, $timeout);
        self::setCode($code);
        self::genCmInfo();
      }
    
    /**
     * Modem Database
     */
    public static function genCmInfo($cpe = NULL)
      {
        $_ = new Objects\BaseObject;
        /**
         * HINT: ModemDevice Object: Construct ((String) Model, (String) ModelName, (Bool) has eRouter, (CmMacDiff object) interface CM_MAC diff (mta, eRouter), (array) prod firmware(s)
         */
        /**
         * Mainly used models
         */
        $_->TG3442DE  = new Objects\ModemDevice('TG3442DE',   'Arris TG3442DE',                     true, new Objects\CmMacDiff(1, 3),  array('01.04.046.17.EURO.SIP'));
        $_->CH7465VF  = new Objects\ModemDevice('CH7465VF',   'Compal CH7465VF',                    true, new Objects\CmMacDiff(1, 2),  array('CH7465VF-NCIP-6.15.20.2-2-NOSH'));
        $_->CH7465LG  = new Objects\ModemDevice('CH7465LG',   'Compal CH7465LG',                    true, new Objects\CmMacDiff(1, 2),  array('CH7465VF-NCIP-6.15.20.2-2-NOSH'));
        $_->TC7200    = new Objects\ModemDevice('TC7200',     'Technicolor TC7200',                 true, new Objects\CmMacDiff(1, 2),  array('STD6.03.03'));
        $_->EVW3226   = new Objects\ModemDevice('EVW3226',    'UBEE EVW3226',                       true, new Objects\CmMacDiff(1, 2),  array('2.10'));
        $_->EPC3925   = new Objects\ModemDevice('EPC3925',    'Cisco EPC3925',                      true, new Objects\CmMacDiff(2, 4));
        $_->EPC3208   = new Objects\ModemDevice('EPC3208',    'Cisco EPC3208',                      true, new Objects\CmMacDiff(2, 255));
        /**
         * Older models
         */
        $_->EVM3236   = new Objects\ModemDevice('EVM3236',    'UBEE EVM3236',                       false, new Objects\CmMacDiff(1,   255));
        $_->EVM3206   = new Objects\ModemDevice('EVM3206',    'UBEE EVM3206',                       false, new Objects\CmMacDiff(1,   255));
        $_->EPC2100   = new Objects\ModemDevice('EPC2100',    'Cisco EPC2100',                      false, new Objects\CmMacDiff(2,   255));
        $_->EPC2203   = new Objects\ModemDevice('EPC2203',    'Cisco EPC2203',                      false, new Objects\CmMacDiff(2,   255));
        $_->EPX2203   = new Objects\ModemDevice('EPX2203',    'Cisco EPX2203',                      false, new Objects\CmMacDiff(2,   255));
        $_->EPC2425   = new Objects\ModemDevice('EPC2425',    'Cisco EPC2425',                      false, new Objects\CmMacDiff(2,   255));
        $_->EPC3212   = new Objects\ModemDevice('EPC3212',    'Cisco EPC3212',                      false, new Objects\CmMacDiff(2,   255));
        $_->SB4100E   = new Objects\ModemDevice('SB4100E',    'Motorola SB4100E',                   false, new Objects\CmMacDiff);
        $_->SB4101    = new Objects\ModemDevice('SB4101',     'Motorola SB4101',                    false, new Objects\CmMacDiff);
        $_->SB4200    = new Objects\ModemDevice('SB4200',     'Motorola SB4200',                    false, new Objects\CmMacDiff);
        $_->SB5100E   = new Objects\ModemDevice('SB5100E',    'Motorola SB5100E',                   false, new Objects\CmMacDiff);
        $_->SB5101E   = new Objects\ModemDevice('SB5101E',    'Motorola SB5101E',                   false, new Objects\CmMacDiff);
        $_->SBV5120E  = new Objects\ModemDevice('SBV5120E',   'Motorola SBV5120E',                  false, new Objects\CmMacDiff);
        $_->SBV5121E  = new Objects\ModemDevice('SBV5121E',   'Motorola SBV5121E',                  false, new Objects\CmMacDiff);
        $_->SBV6120E  = new Objects\ModemDevice('SBV6120E',   'Motorola SBV6120E',                  false, new Objects\CmMacDiff);
        $_->TM502B    = new Objects\ModemDevice('TM502B',     'Thomson TM502B',                     false, new Objects\CmMacDiff);
        $_->TCM390    = new Objects\ModemDevice('TCM390',     'Thomson TCM390',                     false, new Objects\CmMacDiff);
        $_->TCM420    = new Objects\ModemDevice('TCM420',     'Thomson TCM420',                     false, new Objects\CmMacDiff);
        
        self::$cmInfo = property_exists($_, self::cmModel()) ? $_->{ self::cmModel() }: new Objects\ModemDevice(NULL, 'Unknown model', false, new Objects\CmMacDiff);
        
        return $cpe !== NULL ? $_->{strtoupper($cpe)} : NULL;
      }
      
    /**
     *  set Host
     */
    public static function setHost($host = null)
      {
        self::$SNMPConnector->changeHost($host);
      }
      
    /**
     *  Load BindValue
     */
    public static function BindVal($key = '', $index = 0)
      {
        if(property_exists(self::$binds, $key) && in_array($index, array_keys(self::$binds->{$key})))
          {
            return self::$binds->{$key}[$index];
          }
        return NULL;
      }
      
    /**
     * SET model code (fix here)
     */
    private static function setCode($code = NULL)
      {
        if(strtolower($code) == 'ch7465vf' || strtolower($code) == 'ch7465lg')
          {
            $code = 'ch7465';
          }
        self::$code = $code;
        return $code;
      }
    
    /**
     *  Just shortcut to $this->SNMPConnector->MIB
     */
    protected static function snmpOID()
      {
        return self::$SNMPConnector->OID(join(OID_COMMA, func_get_args()));
      }
      
      
      
    /**
     *  Just shortcut to $this->SNMPConnector->MIB
     */
    protected static function snmp()
      {
        $args = func_get_args();
        $mibOid = EMPTY_STRING;
        if(count($args) == 1 && gettype($args[0]) == 'array')
          {
            $mibOid = join(OID_COMMA, $args[0]);
          }
        else if(count($args) > 0)
          {
            $mibOid = join(OID_COMMA, $args);
          }
        
        if(count(explode(OID_COMMA, $mibOid)) > 2 && (explode(OID_COMMA, $mibOid)[0] == EMPTY_STRING || explode(OID_COMMA, $mibOid)[0] == 'iso') && explode(OID_COMMA, $mibOid)[1] == '1')
          {
            return self::$SNMPConnector->OID($mibOid);
          }

        return self::$SNMPConnector->MIB($mibOid);
      }
    
    
    
    /**
     *  SnmpGet shortcut
     */
    public static function snmpget()
      {
        return self::snmp(join(OID_COMMA, func_get_args()))->get();
      }
    
    
    
    /**
     *  SnmpWalk shortcut
     */
    public static function snmpwalk()
      {
        return self::snmp(join(OID_COMMA, func_get_args()))->walk();
      }
    
    
    
    /**
     *  SnmpSet shortcut
     */
    protected static function snmpset($arg = null, $type = null, $value = null)
      {
        return self::snmp($arg)->set($type, $value);
      }
    

    /**
    * Get vendor by mac - just DB, disabled macvendors.com API for better performance
    */
    public static function macVendor($mac = EMPTY_STRING)
      {
        if(!class_exists('dibi'))
          {
            return false;
          }
        return dibi::select('vendor')
                      ->from('macvendors')
                        ->where('mac = %s', substr(self::phyAddr($mac, EMPTY_STRING), 0, 6))
                      ->execute()
                    ->fetchSingle('mac');
      }
      
      
      
    /**
     *  Mac address diff
     */
    public static function macDiff($mac1, $mac2){
  		$mac1 = explode(":", $mac1 ?: '00');
  		$mac2 = explode(":", $mac2 ?: '00');
  		$diffs = array(0,0,0,0,0,0);
  		foreach($mac1 as $i=>$bit){
  			$diffs[$i] = hexdec($bit) - hexdec($mac2[$i]);
  		}
  		$diff = array_sum($diffs);
  		
  		return $diff;
  	}
  	
  	
  	
    /**
     *  PHY address fix
     */
    protected static function phyAddr($input = EMPTY_STRING, $join = ':')
      {
        return strtoupper( str_replace( array(':', ' ', '-', '.'), $join, $input ) );
      }
    
    
    
    /**
     *  HEX ip to DECip
     */
    protected static function hexIP($input = '00 00 00 00', $mask = '%d.%d.%d.%d')
      {
        $octs = explode(' ', $input);
        return sprintf($mask, hexdec($octs[0]), hexdec($octs[1]), hexdec($octs[2]), hexdec($octs[3]));
      }
    
    /**
     *  HEX mac to DECmac (sometimes used as OID key)
     */
    protected static function decMAC($input = '00:00:00:00:00:00', $mask = '%d.%d.%d.%d.%d.%d')
      {
        $octs = explode(':', self::phyAddr( $input ) );
        return sprintf($mask, hexdec($octs[0]), hexdec($octs[1]), hexdec($octs[2]), hexdec($octs[3]), hexdec($octs[4]), hexdec($octs[5]));
      }
    
    
    /**
     *  ipv6 format
     */
    protected static function IPv6Format($input = '00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00', $mask = "%'02X%'02X:%'02X%'02X:%'02X%'02X:%'02X%'02X:%'02X%'02X:%'02X%'02X:%'02X%'02X:%'02X%'02X")
      {
        // hint 2x:2x:2x:2x:2x:2x:2x:2x%4d
        $octs = explode(' ', $input);
        return sprintf($mask, hexdec($octs[0]), hexdec($octs[1]), hexdec($octs[2]), hexdec($octs[3]), hexdec($octs[4]), hexdec($octs[5]), hexdec($octs[6]), hexdec($octs[7]), hexdec($octs[8]),
                              hexdec($octs[9]), hexdec($octs[10]), hexdec($octs[11]), hexdec($octs[12]), hexdec($octs[13]), hexdec($octs[14]), hexdec($octs[15]));
      }
    
    
    
    /**
     *  Hex to String
     */
    protected static function hexStr($input = EMPTY_STRING)
      {
        $hex = str_replace(' ', EMPTY_STRING, $input);
        $str = hex2bin($hex);
        return trim($str);
      }
    
    
    
    /**
     *  str_contains || $string, $needle1[, or $needle2[, or $needle3]]
     */
    protected static function str_contains()
      {
        $args = func_get_args();
        $str = strtolower($args[0]);
        array_shift($args);
        foreach($args as $c)
          {
            if(function_exists('str_contains') ? str_contains($str, $c) : strpos($str, $c) !== false)
              {
                return true;
                break;
              }
          }
        return false;
      }
      
      
      
    /**
     *  Translate network interface name from all possible to fixed format
     */
    protected static function translateInterface($str = EMPTY_STRING)
      {
        $str = strtolower($str);
        $output = $str;
        if(self::str_contains($str, 'ethernet', 'switch') )
          {
            $output = INTF_ETH;
          }
        else if(self::str_contains($str, 'wlan5g', 'wifi 5g'))
          {
            $output = INTF_WL_5G;
          }
        else if(self::str_contains($str, 'wlan', 'wifi'))
          {
            $output = INTF_WL_2G;
          }

        
        return $output;
      }
      
      
      
    /**
     *  DateTime formatter, iso input! Y-m-d H:i:s
     */
    protected function dateTimeFormat($input = '0000-00-00 00:00:00', $format = 'Y-m-d H:i:s')
      {
        if($format == 'Y-m-d H:i:s')
          {
            return $input;
          }
        else
          {
            $seconds = time($input);
            $dtF = new \DateTime('@0');
            $dtT = new \DateTime("@$seconds");
            return $dtF->diff($dtT)->format($format);
          }
      }
      
      
      
    /**
    *   Hex dateTime formatter
    */
    protected static function hexDateTimeFormat($hex = '00 00 00 00 00 00 00', $format = FALSE)
      {
        $octs = explode(' ', $hex ?: '00 00 00 00 00 00 00');
         # Hint: 2d-1d-1d,1d:1d:1d.1d,1a1d:1d
        $dt = new Objects\BaseObject;
        $dt->Y = hexdec($octs[0] . $octs[1]);
        $dt->M = hexdec($octs[2]);
        $dt->D = hexdec($octs[3]);
        $dt->H = hexdec($octs[4]);
        $dt->i = hexdec($octs[5]);
        $dt->s = hexdec($octs[6]);
        $dt->dateTime = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $dt->Y, $dt->M, $dt->D, $dt->H, $dt->i, $dt->s);

        return !$format ? $dt->dateTime : $this->dateTimeFormat($dt->dateTime, $format);
      }
      
      
      
    /**
    * DeviceCode fetcher - ONLY if listed in codes const...
    */
    public static function getCode()
      {
        global $MySNMPv2_CPEs;
        $name = strtolower(self::sysDescr());
        /*if(strpos($name, 'no such') !== false)
          {
            $name = strtolower(self::sysName());
          }*/
        foreach($MySNMPv2_CPEs as $code)
          {
            if(strpos($name, $code) !== false)
              {
                return $code;
              }
          }
        return $name;
      }
    
    /**
    * Private Enterprises dispatcher (ex.: read wlSSID - different ways to get it on ch7465 vs tg3442de )
    */
    private static function __privateCall()
      {
        $args = func_get_args();
        $method = count($args) > 0 ? $args[0] : NULL;
        $argG = count($args) == 2 ? $args[1] : false;
        if(self::$code === NULL)
          {
            self::setCode(self::getCode());
          }
          
        $class = __NAMESPACE__ . "\\" . self::$code;

        if(self::$code === null || !self::$code || self::$code === "")
          {
            return array('error' => 'Missing device code, check sysDescr.');
          }
        else if(!class_exists($class))
          {
            return array('error' => $class . ' not exists!');
          }
        else if( (!method_exists($class, $method)) || (method_exists($class, $method) && !$class::supports($method)) )
          {
            return array('error' => $method . ' unsupported on  ' . self::$code);
          }
        else if(!$argG)
          {
            return $class::$method();
          }
        else
          {
            return $class::$method($argG);
          }
      }
    
    
    /*************************************************************************************************************************************************
     * 
     *  Global snmpGet
     * 
     */
    
    
    
    /**
    * RAW system description
    */
     public final static function sysDescr()
      {
        if(self::$sys === "" || self::$sys === "ERROR")
          {
            $_ = self::snmp('SNMPv2-MIB::sysDescr.0')->get();
            self::$sys = gettype($_) == 'array' ? 'ERROR' : str_replace(array('<<', '>>'), '', $_);
          }
        return self::$sys;
      }
    
    /**
    * get CmHW_REW
    */
     public final static function cmHWrev()
      {
        return @floatval(explode(';', explode('HW_REV: ', self::sysDescr())[1])[0]) ?: NULL;
      }
      
    /**
    * get CmModel
    */
     public final static function cmModel()
      {
        $model = @strtoupper(str_replace(array('>>', '.U'), '', explode('MODEL: ', self::sysDescr())[1])) ?: NULL;
        
        /**
         * 20/9/2022 FIX - CBN Vodafone firmware overwrites model name in sysDescr...
         */
        if($model == 'CH7465VF' && self::cmHWrev() == 5.01)
          {
            return 'CH7465LG';
          }
        return $model;
      }
      
    /**
     * get cm info
     */
    public final static function cmNfo()
      {
        return self::$cmInfo;
      }
      
    /**
    * RAW sysName
    */
     public final static function sysName()
      {
        $_ = self::snmpget('SNMPv2-MIB::sysName.0');
        return gettype($_) == 'array' ? 'ERROR' : $_;
      }
    
    
    
    /**
    * RAW SN
    */
     public final function serialNum()
      {
        return $this->snmpget('DOCS-CABLE-DEVICE-MIB::docsDevSerialNumber.0');
      }



    /**
    * RAW uptime in TIMETICKS
    */
    public function sysUpTime()
      {
        return @ $this->snmpget('SNMPv2-MIB::sysUpTime.0') ?: 0;
      }
      
      
      
    /**
    * Parsed uptime to string
    */
    public final function sysUpTimeStr($format = '%a day(s), %H:%I:%S', $val = null)
      {
        $sysUpTime = $val === null ? $this->sysUpTime() : $val;
        if(!$sysUpTime)
          {
            return EMPTY_STRING;
          }
        $seconds = intval($sysUpTime / 100);
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format($format);
      }
    
    /**
    * RAW uptime in TIMETICKS, str
    */
    public function sysUpTimeRS($format = '%a day(s), %H:%I:%S')
      {
        $up = $this->sysUpTime();
        return array($up, $this->sysUpTimeStr($format, $up));
      }
    
    /**
    * Device DateTime
    */
    public final function docsDevDateTime()
      {
        return self::hexDateTimeFormat( $this->snmpget('DOCS-CABLE-DEVICE-MIB::docsDevDateTime.0') );
      }
    
    /**
    * SerialNumber
    */
    public final function docsDevSerialNumber()
      {
        return $this->snmpget('DOCS-CABLE-DEVICE-MIB::docsDevSerialNumber.0');
      }
      
      
    /**
    * MaxCpe
    */
    public final function MaxCpe()
      {
        return $this->snmpget('DOCS-CABLE-DEVICE-MIB::docsDevMaxCpe.0');
      }
      
      
      
    /**
    * Current cm status
    */
    public final function docsIfCmStatusCode()
      {
        $status = $this->snmpget('DOCS-IF-MIB::docsIfCmStatusEntry.1.2');
        return array(gettype($status) === 'array' ? 0 : $status, self::BindVal('docsIfCmStatusCode', intval($status)) ?: 'other');
      }
      

    /**
    * Current firmware version, status
    */
    public final function firmware()
      {
        $data = $this->snmp('DOCS-CABLE-DEVICE-MIB::docsDevSoftware')
                      ->getObjects(
                          'docsDevSwAdminStatus', 
                          'docsDevSwOperStatus', 
                          'docsDevSwCurrentVers'
                      );
        $fw = new Objects\BaseObject;
        $fw->adminStatus = self::BindVal('docsDevSwAdminStatus', $data->docsDevSwAdminStatus);
        $fw->operStatus  = self::BindVal('docsDevSwOperStatus', $data->docsDevSwOperStatus);
        $fw->currVersion = $data->docsDevSwCurrentVers;
        
        return $fw;
      }
      
      
      
    /**
    * Parsed system internal time
    */
    public final function systime()
      {
        return self::hexDateTimeFormat(
                        $this->snmpget('DOCS-CABLE-DEVICE-MIB::docsDevDateTime.0')
                      );
      }
    
    
    
    /**
    * Device eventlog
    */
    public final function eventlog()
      {
       $eventEntry = $this->snmp('DOCS-CABLE-DEVICE-MIB::docsDevEventEntry')
                          ->assocTable(
                            'docsDevEvFirstTime',
                            'docsDevEvLastTime',
                            'docsDevEvCounts',
                            'docsDevEvLevel',
                            'docsDevEvText'
                          );
       $eventlog = array();
       foreach($eventEntry as $entry)
        {
          $ent              = new Objects\EventLog;
          $ent->firstTime   = self::hexDateTimeFormat( $entry->docsDevEvFirstTime );
          $ent->lastTime    = self::hexDateTimeFormat( $entry->docsDevEvLastTime );
          $ent->counts      = $entry->docsDevEvCounts;
          $ent->setLevel    ( $entry->docsDevEvLevel );
          $ent->text        = $entry->docsDevEvText;
          
          $eventlog[] = $ent;
        }

        
       return array_reverse($eventlog);
      }
    
    
    
    /**
    * Ethernet interfaces, speed in mbit, operStatus (boolean)
    */
    public final function ethernets($ifType = 6)
      {
        $ports = array();
        foreach($this->snmp('IF-MIB::ifEntry')
                      ->assocTable(
                          'ifOperStatus', 
                          'ifDescr', 
                          'ifType', 
                          'ifSpeed', 
                          array('ifType' => $ifType)
                      ) as $port)
          {
            /* Fulltext filter na arris -> bohužel má DSlite tunel ifType taky 6ku, takže filtrovat podle ifDescr */
            if($this->str_contains($port->ifDescr, 'dslite'))
              {
                continue;
              }
              
            $myPort = new Objects\HWInterface;
            $myPort->speed  = $port->ifSpeed / 1000000; // bps to mbps 
            $myPort->status = $port->ifOperStatus == 1; // 1 = up, 2 = down
            $myPort->descr  = $port->ifDescr;
            
            $ports[] = $myPort;
          }
          
        return $ports;
      }

    /**
     * Interface Monitor
     */
    public final function IntfMonitor($ids = array())
      {
        $r = new Objects\BaseObject;
        $r->ports = array();
        $r->ts = round(microtime(true) * 1000);
        if(!count($ids))
          {
            foreach($this->snmp('IF-MIB::ifEntry')
                        ->assocTable(
                            'ifType',
                            'ifInOctets',
                            'ifOutOctets',
                            'ifSpeed',
                            'ifOperStatus',
                            'ifDescr',
                            'ifInErrors',
                            'ifOutErrors',
                            'ifLastChange',
                            array('ifType' => array(6, 71, 253))
                        ) as $port)
            {
              $p = new Objects\BaseObject;
              $p->id   = $port->index;
              $p->d   = $port->ifDescr;
              $p->t   = $port->ifType;
              $p->sp  = $port->ifSpeed;
              $p->st  = $port->ifOperStatus == 1;
              $p->octs= array(
                          $port->ifInOctets,
                          $port->ifOutOctets
                        );
              $p->errors= array(
                          $port->ifInErrors,
                          $port->ifOutErrors
                        );
              $r->ports[] = $p;
            }
          }
        else
          {
            foreach($ids as $portID)
              {
                $p = new Objects\BaseObject;
                $p->id   = $portID;
                $p->sp  = $this->snmp('IF-MIB::ifSpeed')->iget($portID);
                $p->st  = $this->snmp('IF-MIB::ifOperStatus')->iget($portID) == 1;
                $p->octs= !$p->st ? array(0,0) : array(
                            $this->snmp('IF-MIB::ifInOctets')->iget($portID),
                            $this->snmp('IF-MIB::ifOutOctets')->iget($portID)
                          );
                $p->errors= !$p->st ? array(0,0) : array(
                            $this->snmp('IF-MIB::ifInErrors')->iget($portID),
                            $this->snmp('IF-MIB::ifOutErrors')->iget($portID)
                          );
                $r->ports[] = $p;
              }
          }
        

        return $r;
      }
    
    /**
     * ServiceFlows - from cm can count octs only in upstream... for nownStream use CMTS MIB!
     */
    
    public final function ServiceFlows()
      {
        $flows = $this->snmp('DOCS-QOS3-MIB::docsQosServiceFlowEntry')
                      ->assocTable(
                        'docsQosServiceFlowDirection',
                        'docsQosServiceFlowPrimary'
                      );

        $sf = array();
        
        foreach($flows as $flow)
          {
            $flow->octs = $this->snmp('DOCS-QOS3-MIB::docsQosServiceFlowOctets')->iget(array(2, $flow->index));
            $flow->tr   = $this->snmp('DOCS-QOS3-MIB::docsQosParamSetMaxTrafficRate')->iget(array(2, 1, $flow->index));
            
            if($flow->docsQosServiceFlowPrimary != 1)
              {
                $flow->pr = array();
                foreach($this->snmp('DOCS-QOS3-MIB::docsQosPktClassPriority')->walkArr(array(2, $flow->index)) as $type)
                  {
                    switch($type)
                      {
                        case 21:  
                        case 22:  
                        case 23:  $flow->pr = 'icmp/dns/snmp';   break;
                        case 11:
                        case 251: $flow->pr = 'vod';    break;
                        case 16:
                        case 41:  $flow->pr = 'wifree'; break;
                        case 31:
                        case 32:  $flow->pr = 'voip';   break;
                      }
                  }
              }
            else
              {
                $flow->pr = 'primary';
              }
            unset($flow->docsQosServiceFlowPrimary);
            $flow->dirc = $flow->docsQosServiceFlowDirection == 1 ? 'down' : 'up';
            unset($flow->docsQosServiceFlowDirection);
            $sf[] = $flow;
          }
        $sfT = new Objects\BaseObject;
        $sfT->ts = time();
        $sfT->sf = new Objects\BaseObject;
        foreach($sf as $s)
          {
            if(!property_exists($sfT->sf, $s->pr))
              {
                $sfT->sf->{$s->pr} = new Objects\BaseObject;
                $sfT->sf->{$s->pr}->down = new Objects\SF;
                $sfT->sf->{$s->pr}->up = new Objects\SF;
              }
            
            $sfT->sf->{$s->pr}->{$s->dirc}->index = $s->index;
            $sfT->sf->{$s->pr}->{$s->dirc}->rate  = $s->tr;
            $sfT->sf->{$s->pr}->{$s->dirc}->octs  = $s->octs;
              
          }
        
        return $sfT;
      }
      
    /**
    * RF down/up speed
    */
    public final function maxrates() // using QOS3 EuroDocsis 3.0 3.1
      {
        
        $flows = $this->snmp('DOCS-QOS3-MIB::docsQosServiceFlowEntry')
                      ->assocTable(
                        'docsQosServiceFlowDirection',
                        'docsQosServiceFlowPrimary'
                      );
        $trafficRate = $this->snmp('DOCS-QOS3-MIB::docsQosParamSetMaxTrafficRate', 2, 1);
        
        $PrimaryFlows = new Objects\DownUp;
        foreach($flows as $flow)
          {
            if($flow->docsQosServiceFlowPrimary == 1)
              {
                $PrimaryFlows->{ $flow->docsQosServiceFlowDirection == 1 ? 'down' : 'up' } = $trafficRate->iget( $flow->index );
              }
          }

        return $PrimaryFlows;
      }
      


    /**
    * RF signal levels (Frequency, power, SNR)
    */
    public final function signal($type = false, $id = false)
      {
        $signal = new Objects\BaseObject;
        if(!$type)
          {
            $signal->ds = array();
            $signal->us = array();
    
            $ifTable = $this->snmp('IF-MIB::ifEntry')
                              ->assocTable(
                                'ifType',
                                array('ifType' => array(SNMP_IF_DS, SNMP_IF_US))
                              );
                              
            $dsTable = $this->snmp('DOCS-IF-MIB::docsIfDownstreamChannelEntry')
                              ->assocTable(
                                'docsIfDownChannelFrequency',
                                'docsIfDownChannelPower'
                              );   
                              
            $dsSNR   = $this->snmp('DOCS-IF-MIB::docsIfSignalQualityEntry')
                                            ->assocTable(
                                                'docsIfSigQSignalNoise',
                                                'docsIfSigQUnerroreds',
                                                'docsIfSigQCorrecteds',
                                                'docsIfSigQUncorrectables'
                                            );   
            
            $usTable = $this->snmp('DOCS-IF-MIB::docsIfUpstreamChannelEntry')->assocTable('docsIfUpChannelFrequency');
            
            $usPower = $this->snmp('DOCS-IF3-MIB::docsIf3CmStatusUsEntry')
                                              ->assocTable(
                                                'docsIf3CmStatusUsTxPower',
                                                'docsIf3CmStatusUsModulationType'
                                              );
    
            foreach($ifTable ?: array() as $if)
            {
              switch($if->ifType)
                {
                  case SNMP_IF_DS:  # downstreams
                    foreach($dsTable ?: array() as $ds)
                      {
                        if($ds->index === $if->index)
                          {
                            $channel = new Objects\SignalChannel;
                            $channel->id = $ds->index;
                            $channel->freq = floatval( $ds->docsIfDownChannelFrequency / 1000000 ); # to MHz
                            $channel->snr = 0;
                            $channel->power = floatval( sprintf('%4.1f', ($ds->docsIfDownChannelPower / 10) + 60) );
                            foreach($dsSNR as $snr)
                              {
                                if($snr->index === $if->index)
                                  {
                                    $channel->snr   = floatval( sprintf('%4.1f', ($snr->docsIfSigQSignalNoise / 10)) );
                                    $channel->cdw   = new Objects\BaseObject;
                                    $channel->cdw->unerr  = $snr->docsIfSigQUnerroreds;
                                    $channel->cdw->corr   = $snr->docsIfSigQCorrecteds;
                                    $channel->cdw->uncorr = $snr->docsIfSigQUncorrectables;
                                    break;
                                  }
                              }
                            $signal->ds[] = $channel;
                            break;
                          }
                      }
                    break;
                  case SNMP_IF_US: # upstreams
                    foreach($usTable ?: array() as $us)
                      {
                        if($us->index === $if->index)
                          {
                            $channel = new Objects\SignalChannel;
                            $channel->id = $us->index;
                            $channel->power = 0;
                            $channel->freq = floatval( $us->docsIfUpChannelFrequency / 1000000 ); # to MHz
                            foreach($usPower ?: array() as $pwr)
                              {
                                if($pwr->index === $if->index)
                                  {
                                    $channel->power       = floatval( sprintf('%4.1f', ($pwr->docsIf3CmStatusUsTxPower / 10) + 60) );
                                    break;
                                  }
                              }
                            
                              $signal->us[] = $channel;
                              break;
                          }
                      }
                    break;
                }
            }
          }
        else if($type === 'ds')
          {
            $signal->ts = time();
            $signal->power = floatval( sprintf('%4.1f', ( $this->snmpget('DOCS-IF-MIB::docsIfDownChannelPower', $id) / 10 ) + 60) );
            $signal->snr = floatval( sprintf('%4.1f', ( $this->snmpget('DOCS-IF-MIB::docsIfSigQSignalNoise', $id) / 10) ) );
            $signal->cdw   = new Objects\BaseObject;
            $signal->cdw->unerr  = $this->snmpget('DOCS-IF-MIB::docsIfSigQUnerroreds', $id);
            $signal->cdw->corr   = $this->snmpget('DOCS-IF-MIB::docsIfSigQCorrecteds', $id);
            $signal->cdw->uncorr = $this->snmpget('DOCS-IF-MIB::docsIfSigQUncorrectables', $id);

          }
        
        return $signal;
      }

    /**
     * OFDM Info
     */
    public final function OFDMInfo()
      {
        $info = new Objects\BaseObject;
        //search Interface type 277
        $info->rf = $this->snmp('IF-MIB::ifEntry')
                      ->assocTable(
                          'ifType',
                          'ifDescr',
                          array('ifType' => 277)
                        );
        //$info->ofdma = $this->snmp('DOCS-IF31-MIB::docsIf31CmUsOfdmaChanEntry')->table();

        $info->ofdmChann = $this->snmp('DOCS-IF31-MIB::docsIf31CmDsOfdmChanEntry')
                                        ->assocTable(
                                          "docsIf31CmDsOfdmChanChannelId",//: 25,
                                          "docsIf31CmDsOfdmChanSubcarrierZeroFreq",//: 719600000,
                                          "docsIf31CmDsOfdmChanFirstActiveSubcarrierNum",//: 1428,
                                          "docsIf31CmDsOfdmChanLastActiveSubcarrierNum",//: 2667,
                                          "docsIf31CmDsOfdmChanNumActiveSubcarriers",//: 1208,
                                          "docsIf31CmDsOfdmChanSubcarrierSpacing",//: 50,
                                          "docsIf31CmDsOfdmChanCyclicPrefix",//: 1024,
                                          "docsIf31CmDsOfdmChanRollOffPeriod",//: 256,
                                          "docsIf31CmDsOfdmChanPlcFreq",//: 826000000,
                                          "docsIf31CmDsOfdmChanNumPilots",//: 24,
                                          "docsIf31CmDsOfdmChanPlcTotalCodewords",//: 1775043569,
                                          "docsIf31CmDsOfdmChanPlcUnreliableCodewords",//: 0,
                                          "docsIf31CmDsOfdmChanNcpTotalFields",//: 22720570724,
                                          "docsIf31CmDsOfdmChanNcpFieldCrcFailures"//: 0
                                        )[0] ?: NULL;
                                        
        $info->power = array();
        $power = $this->snmp('DOCS-IF31-MIB::docsIf31CmDsOfdmChannelPowerEntry')
                                      ->listTable(
                                            "docsIf31CmDsOfdmChannelPowerCenterFrequency",
                                            "docsIf31CmDsOfdmChannelPowerRxPower"
                                      );
        foreach($power as $p)
          {
            $f = new Objects\BaseObject;
            $f->freq  = $p->docsIf31CmDsOfdmChannelPowerCenterFrequency;
            $f->power = floatval( sprintf('%4.1f', ($p->docsIf31CmDsOfdmChannelPowerRxPower / 10) + 60) );
            $f->band  = $info->ofdmChann->docsIf31CmDsOfdmChanPlcFreq === $f->freq ? "PLC" : $p->index;
            $info->power[] = $f;
          }
        
        
        
        $info->profiles = array();

        $profiles = $this->snmp('DOCS-IF31-MIB::docsIf31CmDsOfdmProfileStatsEntry')
                                                ->table(0, array(
                                                    'docsIf31CmDsOfdmProfileStatsTotalCodewords',
                                                    'docsIf31CmDsOfdmProfileStatsCorrectedCodewords',
                                                    'docsIf31CmDsOfdmProfileStatsUncorrectableCodewords',
                                                    'docsIf31CmDsOfdmProfileStatsInOctets',
                                                    'docsIf31CmDsOfdmProfileStatsInUnicastOctets',
                                                    'docsIf31CmDsOfdmProfileStatsInMulticastOctets'
                                                    )
                                                );
        foreach($profiles->_indexes as $i => $n)
          {
            /**
             * Custom Profile name, OR ASCII A + $n
             */
            $profileID = @array(
                                255 => "NCP"
                            )[$n] ?: chr(ord("A") + $n);
                
            $profile = new Objects\BaseObject; 
            $profile->id = $n;
            $profile->name = $profileID;
            
            $profile->cwe = new Objects\BaseObject; 
            $profile->cwe->total   = $profiles->docsIf31CmDsOfdmProfileStatsTotalCodewords[$i];
            $profile->cwe->corr    = $profiles->docsIf31CmDsOfdmProfileStatsCorrectedCodewords[$i];
            $profile->cwe->uncorr  = $profiles->docsIf31CmDsOfdmProfileStatsUncorrectableCodewords[$i];
            
            $profile->oct = new Objects\BaseObject; 
            $profile->oct->in      = $profiles->docsIf31CmDsOfdmProfileStatsInOctets[$i];
            $profile->oct->uni     = $profiles->docsIf31CmDsOfdmProfileStatsInUnicastOctets[$i];
            $profile->oct->multi   = $profiles->docsIf31CmDsOfdmProfileStatsInMulticastOctets[$i];
            
            $info->profiles[] = $profile;
          }
        
        $rxMER = $this->snmp('DOCS-PNM-MIB::docsPnmCmDsOfdmRxMerEntry')
                                        ->assocTable(
                                          "docsPnmCmDsOfdmRxMerPercentile",//: 2,
                                          "docsPnmCmDsOfdmRxMerMean",//: 4388,
                                          "docsPnmCmDsOfdmRxMerStdDev",//: 88,
                                          "docsPnmCmDsOfdmRxMerThrVal",//: 167,
                                          "docsPnmCmDsOfdmRxMerThrHighestFreq",//: 852250000,
                                          "docsPnmCmDsOfdmRxMerMeasStatus"//: 2,
                                        )[0] ?: NULL;
      

        $info->rxMER = new Objects\BaseObject; 
        $info->rxMER->Percentile      = $rxMER->docsPnmCmDsOfdmRxMerPercentile;
        $info->rxMER->Mean            = floatval( $rxMER->docsPnmCmDsOfdmRxMerMean / 100 );
        $info->rxMER->StdDev          = floatval( $rxMER->docsPnmCmDsOfdmRxMerStdDev / 100 );
        $info->rxMER->ThrVal          = $rxMER->docsPnmCmDsOfdmRxMerThrVal * pow(4, -1);
        $info->rxMER->ThrHighestFreq  = $rxMER->docsPnmCmDsOfdmRxMerThrHighestFreq;
        $info->rxMER->MeasStatus      = (@array(
                                            1 => 'other',
                                            2 => 'inactive',
                                            3 => 'busy',
                                            4 => 'sampleReady',
                                            5 => 'error',
                                            6 => 'resourceUnavailable',
                                            7 => 'sampleTruncated'
                                          )[$rxMER->docsPnmCmDsOfdmRxMerMeasStatus] ?: 'other'). '(' . $rxMER->docsPnmCmDsOfdmRxMerMeasStatus . ')';
        


        $reqMER = $this->snmp('DOCS-PNM-MIB::docsPnmCmDsOfdmReqMERObjects')
                                ->getObjects(
                                    "docsPnmCmDsOfdmReqMerQam256",//: 108,
                                    "docsPnmCmDsOfdmReqMerQam1024",//: 136,
                                    "docsPnmCmDsOfdmReqMerQam2048",//: 148,
                                    "docsPnmCmDsOfdmReqMerQam4096"//: 164,
                                  );
        # unit: quarterdB - správný výpočet je tedy SNMP hodnota * 4 na -1
        $info->reqMER = new Objects\BaseObject;
        $info->reqMER->qam256  = $reqMER->docsPnmCmDsOfdmReqMerQam256  * pow(4, -1);
        $info->reqMER->qam1024 = $reqMER->docsPnmCmDsOfdmReqMerQam1024 * pow(4, -1);
        $info->reqMER->qam2048 = $reqMER->docsPnmCmDsOfdmReqMerQam2048 * pow(4, -1);
        $info->reqMER->qam4096 = $reqMER->docsPnmCmDsOfdmReqMerQam4096 * pow(4, -1);
        
        
        return $info;
      }
    
    /**
     *  cmmac
     */
    public static function cmmac()
      {
        $mac = self::snmp('IF-MIB::ifEntry')->assocTable(
                                'ifPhysAddress',
                                'ifType',
                                array('ifType' => SNMP_IF_RF)
                                );
        return count($mac) ? self::phyAddr( $mac[0]->ifPhysAddress ) : false;
      }
    
    

    /**
     *  CpeInfo - routerMode - check ipNetToPhysicalEntry, detect eRtr/MTA interface
     *  $filter ... return only CPEs with same vendor as cm
     */
    public static function cpeInfo($filter = true)
      {
        global $CPEs;
        
        $Table  = self::snmp('IP-MIB::ipNetToPhysicalEntry.4');
        $data   = $Table->walk();
        if(!count($data))
          {
            return false;
          }
        $list   = array();
        $cmMac  = self::cmmac();
        
        if($cmMac)
          {
            if(self::$code === null)
              {
                self::setCode( self::getCode() );
              }
          }
        
        
        foreach($data as $oid => $mac)
          {
            $entry          = new Objects\BaseObject;
            $entry->mac     = self::phyAddr( $mac );
            $entry->vendor  = self::macVendor( $entry->mac );
            $entry->type    = false;
            if($filter && (substr($entry->mac, 0, 8) !== substr($cmMac, 0, 8)))
              {
                continue;
              }
            foreach(self::$cmInfo->md as $if => $df)
              {
                if(self::macDiff($entry->mac, $cmMac) == $df)
                  {
                    $entry->type = $if;
                    break;
                  }
              }
              
            $dd = explode(OID_COMMA, str_replace($Table->OID, EMPTY_STRING, $oid));
            
            for($d = 0; $d < 4; $d++)
              {
                array_shift($dd);
              }
              
            if(count($dd) == 16)
              {
                $hex = array();
                foreach($dd as $dec)
                  {
                    $hex[] = dechex($dec);
                  }
                $entry->ip = self::IPv6Format( join(' ', $hex) );
              }
            else
              {
                $entry->ip = join(OID_COMMA, $dd) ?: false;
              }

            $list[] = $entry;
          }
        return $list;
      }
      
      
      
    /*************************************************************************************************************************************************
     * 
     *  Private Enterprises (get) - using External libs with Objects\CableModemCalls interface
     *  - every codename has own lib
     * 
     */
    
    /**
     *  list of extra supported functions
     */
    public static function extra()
      {
        if(self::$code === NULL)
          {
            self::setCode(self::getCode());
          }
        $class = __NAMESPACE__ . "\\" . self::$code;

        if( (self::$code === null || !self::$code || self::$code === "") || !class_exists($class) || !method_exists($class, 'extra') )
          {
            return array();
          }
        return $class::extra();
      }
    
    
    /**
     *  routerBridge mode?
     */
    public static function routerBridge()
      {
        if(self::$code === NULL)
          {
            self::setCode(self::getCode());
          }
          
        if(in_array(self::$code, array(
            'tm502b',     'epc3208',    'epc3212',    'sb4100e', 
            'sb4101',     'sb4200',     'sb5100e',    'sb5101e', 
            'sbv5120e',   'sbv5121e',   'sbv6120e',   'tcm390', 
            'tcm420',     'evm3206',    'evm3236',    'epc2100', 
            'epx2203',    'epc2203',    'epc2425'/*OldRouter*/
          )))
          {
            $_ = new Objects\BaseObject;
            $_->mode = 'bridge';
            return $_;
          }

        return self::__privateCall( __FUNCTION__ );
      }
    
    /**
     * mtaProv
     */
    public static function mtaProv()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    /**
     * mtaInfo
     */
    public static function mtaInfo()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
    /**
     * mtaReg
     */
    public static function mtaReg()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
    /**
     * mtaEventlog
     */
    public static function mtaEventlog()
      {
        return self::__privateCall( __FUNCTION__ );
      }
      
    /**
     * cmStats - utili, temp
     */
    public static function cmStats()
      {
        return self::__privateCall( __FUNCTION__ );
      }
      
    /**
     * wlConfig
     */
    public static function wlCfg()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
      
    /**
    * wireless radio info
    * Done: tg3442de, ch7465, tc7200, epc3925, 3226
    */
    public static function wlRadio()
      {
        return self::__privateCall( __FUNCTION__ );
      }
      
      
      
    /**
    * wireless APs info
    * Done: tg3442de, ch7465, tc7200, epc3925
    */
    public static function wlAPs()
      {
        return self::__privateCall( __FUNCTION__ );
      }
      
      
      
    /**
     *  wlScan status
     *  Done: tg3442de, ch7465, tc7200
     */
    public static function wlScanStatus()
      {
        return self::__privateCall( __FUNCTION__ );
      }
      
      
      
    /**
    * wireless scan res
    * Done: tg3442de, ch7465, tc7200
    */
    public static function wlScanResults()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
    
    
    /**
    * LAN associated devices
    * Done: tg3442de, ch7465, tc7200, epc3925
    */
    public static function lanClients()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
    /**
    * WiFi assoc devices
    * Done: ch7465
    */
    public static function wlClients()
      {
        return self::__privateCall( __FUNCTION__ );
      }

    /**
    * LAN associated devices count
    * Done: tg3442de, ch7465, tc7200, epc3925
    */
    public static function lanClientsCount()
      {
        return self::__privateCall( __FUNCTION__ );
      }
      
      
      
    /**
     *  LAN network configuration
     */
    public static function lanConfig()
      {
        return self::__privateCall( __FUNCTION__ );
      }
      


    /*************************************************************************************************************************************************
    * 
    *  Global get
    * 
    */
    
    
    
    /**
    * CM Reboot
    */
    public function docsDevResetNow()
      {
        return $this->snmp("DOCS-CABLE-DEVICE-MIB::docsDevResetNow.0")->setInt(1);
      }
    
    
    
     /*************************************************************************************************************************************************
    * 
    *  Private Enterprises (set)
    * 
    */
    
    /**
     *  routerBridge 
     */
    public static function cmModeSet($mode = 'bridge') // simple "router"/"bridge"
      {
        if(self::$code === NULL)
          {
            self::setCode(self::getCode());
          }
          
        if(in_array(self::$code, array(
            'tm502b',     'epc3208',    'epc3212',    'sb4100e', 
            'sb4101',     'sb4200',     'sb5100e',    'sb5101e', 
            'sbv5120e',   'sbv5121e',   'sbv6120e',   'tcm390', 
            'tcm420',     'evm3206',    'evm3236',    'epc2100', 
            'epx2203',    'epc2203',    'epc2425'/*OldRouter*/
          )))
          {
            $_ = new Objects\BaseObject;
            $_->error = 'bridgeOnly!';
            return $_;
          }

        return self::__privateCall( __FUNCTION__, $mode );
      }
    
    
    
    /**
     *  Factory reset
     */
    public static function factoryReset()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
    
    
    /**
     *  Start wl scan
     *  Done: tg3442de, ch7465, tc7200
     */
    public static function wlScan()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
    
    
    /**
     *  Apply wirelles settings
     */
    public static function wlApplyAll()
      {
        return self::__privateCall( __FUNCTION__ );
      }
    
    
    
    /**
     *  Set wirelless channel on selected radio interface
     */
    public static function wlChannelSet( Objects\wlChannelSet $_ )
      {
        return self::__privateCall( __FUNCTION__, $_ );
      }
      
      
      
    /**
     *  Set SSID on selected AP
     */
    public static function wlSsidSet( Objects\wlSsidSet $_ )
      {
        return self::__privateCall( __FUNCTION__, $_ );
      }
    
    
    
    /**
     *  Set security type on selected AP
     */
    public static function wlSecuritySet( Objects\wlSecuritySet $_ )
      {
        return self::__privateCall( __FUNCTION__, $_ );
      }
      
      
    
    /**
     *  Set password on selected AP
     */
    public static function wlWpaSet( Objects\wlWpaSet $_ )
      {
        return self::__privateCall( __FUNCTION__, $_ );
      }
      
      
    
    /**
     *  Set wirelless power output
     */
    public static function wlPowerSet( Objects\wlPowerSet $_ )
      {
        return self::__privateCall( __FUNCTION__, $_ );
      }
    
    
    
    /**
     *  Set wirelless bandwidth
     */
    public static function wlBandwidthSet( Objects\wlBandwidthSet $_ )
      {
        return self::__privateCall( __FUNCTION__, $_ );
      }
      
      
  }
  
?>