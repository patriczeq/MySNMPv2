<?php
namespace MySNMPv2\Objects;

define('SNMP_IF_RF', 127);
define('SNMP_IF_DS', 128);
define('SNMP_IF_US', 129);
define('OID_COMMA', '.');
define('EMPTY_STRING', '');
define('WL_2G', '2.4Ghz');
define('WL_5G', '5Ghz');
define('INTF_UNKNOWN', 'unknown');
define('INTF_ETH', 'Ethernet');
define('INTF_WL_2G', 'WiFi ' . WL_2G);
define('INTF_WL_5G', 'WiFi ' . WL_5G);
define('IP_ASSOC_STATIC', 'static');
define('IP_ASSOC_DHCP', 'dhcp');
class SnmpBinds
  {
    /**
     *  @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
     *  GLOBAL
     *  @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
     */
    public $docsDevSwOperStatus = array(
              1 => 'In progress',
              2 => 'Complete from provisioning',
              3 => 'Complete from management',
              4 => 'Failed',
              5 => 'Other'
           );
    public $docsDevSwAdminStatus = array(
              1 => 'upgradeFromMgt',
              2 => 'allowProvisioningUpgrade',
              3 => 'ignoreProvisioningUpgrade'
           );
    public $docsIfCmStatusCode = array(
              1 => 'other',
              2 => 'notReady',
              3 => 'notSynchronized',
              4 => 'phySynchronized',
              5 => 'usParametersAcquired',
              6 => 'rangingComplete',
              7 => 'ipComplete',
              8 => 'todEstablished',
              9 => 'securityEstablished',
              10 => 'paramTransferComplete',
              11 => 'registrationComplete',
              12 => 'operational',
              13 => 'accessDenied'
            );
   public $ifType = array(
          'other(1)','regular1822(2)','hdh1822(3)','ddnX25(4)','rfc877x25(5)','ethernetCsmacd(6)','iso88023Csmacd(7)','iso88024TokenBus(8)','iso88025TokenRing(9)','iso88026Man(10)','starLan(11)','proteon10Mbit(12)','proteon80Mbit(13)','hyperchannel(14)','fddi(15)','lapb(16)','sdlc(17)','ds1(18)','e1(19)','basicISDN(20)','primaryISDN(21)','propPointToPointSerial(22)','ppp(23)','softwareLoopback(24)','eon(25)','ethernet3Mbit(26)','nsip(27)','slip(28)','ultra(29)','ds3(30)','sip(31)','frameRelay(32)','rs232(33)','para(34)','arcnet(35)','arcnetPlus(36)','atm(37)','miox25(38)','sonet(39)','x25ple(40)','iso88022llc(41)','localTalk(42)','smdsDxi(43)','frameRelayService(44)','v35(45)','hssi(46)','hippi(47)','modem(48)','aal5(49)','sonetPath(50)','sonetVT(51)','smdsIcip(52)','propVirtual(53)','propMultiplexor(54)','ieee80212(55)','fibreChannel(56)','hippiInterface(57)','frameRelayInterconnect(58)','aflane8023(59)','aflane8025(60)','cctEmul(61)','fastEther(62)','isdn(63)','v11(64)','v36(65)','g703at64k(66)','g703at2mb(67)','qllc(68)','fastEtherFX(69)','channel(70)','ieee80211(71)','ibm370parChan(72)','escon(73)','dlsw(74)','isdns(75)','isdnu(76)','lapd(77)','ipSwitch(78)','rsrb(79)','atmLogical(80)','ds0(81)','ds0Bundle(82)','bsc(83)','async(84)','cnr(85)','iso88025Dtr(86)','eplrs(87)','arap(88)','propCnls(89)','hostPad(90)','termPad(91)','frameRelayMPI(92)','x213(93)','adsl(94)','radsl(95)','sdsl(96)','vdsl(97)','iso88025CRFPInt(98)','myrinet(99)','voiceEM(100)','voiceFXO(101)','voiceFXS(102)','voiceEncap(103)','voiceOverIp(104)','atmDxi(105)','atmFuni(106)','atmIma (107)','pppMultilinkBundle(108)','ipOverCdlc (109)','ipOverClaw (110)','stackToStack (111)','virtualIpAddress (112)','mpc (113)','ipOverAtm (114)','iso88025Fiber (115)','tdlc (116)','gigabitEthernet (117)','hdlc (118)','lapf (119)','v37 (120)','x25mlp (121)','x25huntGroup (122)','trasnpHdlc (123)','interleave (124)','fast (125)','ip (126)','docsCableMaclayer (127)','docsCableDownstream (128)','docsCableUpstream (129)','a12MppSwitch (130)','tunnel (131)','coffee (132)','ces (133)','atmSubInterface (134)','l2vlan (135)','l3ipvlan (136)','l3ipxvlan (137)','digitalPowerline (138)','mediaMailOverIp (139)','dtm (140)','dcn (141)','ipForward (142)','msdsl (143)','ieee1394 (144)','if-gsn (145)','dvbRccMacLayer (146)','dvbRccDownstream (147)','dvbRccUpstream (148)','atmVirtual (149)','mplsTunnel (150)','srp (151)','voiceOverAtm (152)','voiceOverFrameRelay (153)','idsl (154)','compositeLink (155)','ss7SigLink (156)','propWirelessP2P (157)','frForward (158)','rfc1483 (159)','usb (160)','ieee8023adLag (161)','bgppolicyaccounting (162)','frf16MfrBundle (163)','h323Gatekeeper (164)','h323Proxy (165)','mpls (166)','mfSigLink (167)','hdsl2 (168)','shdsl (169)','ds1FDL (170)','pos (171)','dvbAsiIn (172)','dvbAsiOut (173)','plc (174)','nfas (175)','tr008 (176)','gr303RDT (177)','gr303IDT (178)','isup (179)','propDocsWirelessMaclayer (180)','propDocsWirelessDownstream (181)','propDocsWirelessUpstream (182)','hiperlan2 (183)','propBWAp2Mp (184)','sonetOverheadChannel (185)','digitalWrapperOverheadChannel (186)','aal2 (187)','radioMAC (188)','atmRadio (189)','imt (190)','mvl (191)','reachDSL (192)','frDlciEndPt (193)','atmVciEndPt (194)','opticalChannel (195)','opticalTransport (196)','propAtm (197)','voiceOverCable (198)','infiniband (199)','teLink (200)','q2931 (201)','virtualTg (202)','sipTg (203)','sipSig (204)','docsCableUpstreamChannel (205)','econet (206)','pon155 (207)','pon622 (208)','bridge (209)','linegroup (210)','voiceEMFGD (211)','voiceFGDEANA (212)','voiceDID (213)','mpegTransport (214)','sixToFour (215)','gtp (216)','pdnEtherLoop1 (217)','pdnEtherLoop2 (218)','opticalChannelGroup (219)','homepna (220)','gfp (221)','ciscoISLvlan (222)','actelisMetaLOOP (223)','fcipLink (224)','rpr (225)','qam (226)'
           );
  };
  
interface CableModemCalls
  {
    # Helpers
    public static function supports($fn = '');
    public static function BindVal($key = '', $value = 0);
    # Functions
    public static function wlRadio();
    public static function wlAPs();
    public static function wlScanStatus();
    public static function wlScan();
    public static function wlScanResults();
    public static function lanConfig();
    public static function lanClients();
  }

class UnsupportedMethod
  {
    public $unsupported = 'This method is not supported on this device.';
    function __construct($method = null, $code = null, $msg = null)
      {
        if($method !== null && $code !== null)
          {
            $this->unsupported = 'Method ' . $method . ' is not supported on ' . $code . '.';
          }
        else if($method !== null && $code === null)
          {
            $this->unsupported = 'Method ' . $method . ' is not supported on this device.';
          }
        else if($method === null && $code !== null)
          {
            $this->unsupported = 'This method is not supported on ' . $code . '.';
          }
        if($msg !== null)
          {
            $this->message = $msg;
          }
      }
  }
  
class BaseObject 
  {
  }
# @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Response Structs

class SignalChannel
  {
    public  $freq,
            $power,
            $snr = 0;
  }
  
class EventLog
  {
    public  $firstTime,
            $lastTime = '1970-01-01 00:00:00';
    public  $counts,
            $level = 0;
    public  $levelStr = "";
    public  $text;
    private $levels = array(
            1 => 'emergency',
            2 => 'alert',
            3 => 'critical',
            4 => 'error',
            5 => 'warning',
            6 => 'notice',
            7 => 'information',
            8 => 'debug'
        );
        
    public function setLevel($l = 0)
      {
        
        $this->level = $l;
        $this->levelStr = isset($this->levels[$l]) ?  $this->levels[$l] : "??";
      }
  }

class ModemDevice
  {
    public  $model,
            $name = null;
    public  $eRouter = false;
    public  $md = null;
    public  $prodFW = array();
    
    function __construct($model = null, $name = null, $eRouter = null, CmMacDiff $macDiff, $prodFW = array())
      {
        $this->model    = $model;
        $this->name     = $name;
        $this->eRouter  = $eRouter;
        $this->md       = $macDiff;
        $this->prodFW   = $prodFW;
      }
  }
  
class CmMacDiff
  {
    public  $eRouter,
            $mta = 255;
    function __construct($mta = 255, $eRouter = 255)
      {
        $this->mta = $mta;
        $this->eRouter = $eRouter;
      }
  }
  
class SF
  {
    public  $index,
            $rate,
            $octs;
  }
  
class HWInterface
  {
    public  $status,
            $speed,
            $descr;
  }
  
class DownUp
  {
    public  $down,
            $up = 0;
  }
  
class wlRadioInterface 
  {
    public  $id,
            $enabled,
            $channel,
            $autoChannel,
            $band,
            $bandwidth,
            $txPower;
  }
  
class wlAPEntry 
  {
    public  $id,
            $enabled,
            $bssid,
            $ssid,
            $security;
    public  $guest = false;
  }
  
class wlScanEntry 
  {
    public  $id,
            $channel,
            $bssid,
            $vendor,
            $ssid,
            $rssi,
            $security,
            $bw,
            $snr;
            
    public function SetSecurity($str = "")
      {
        $this->security = $str;
      }
  }
  
class lanClientEntry 
  {
    public  $id,
            $mac,
            $ipAssoc,
            $ipv4Addr,
            $hostname,
            $interface,
            $vendor;
    public  $active = true;
    public  $speed = array(-1, -1);
    public  $rssi = -200; # -200 means, that device is not connected, or connected via Ethernet
  }
  
class lanConfig 
  {
    public  $network,
            $mask,
            $dmz,
            $virtualServer;
    public $dns = array();
  }
  
class DMZCfg{
  public $enabled = false;
  public $ip = "0.0.0.0";
}

class virtualServerRow 
  {
    public  $id,
            $descr,
            $enabled,
            $publicPortStart,
            $publicPortEnd,
            $localPortStart,
            $localPortEnd,
            $dest,
            $type;
  }
# @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ SET Structs

class wlChannelSet
  {
    public  $interface,
            $channel;
            
    function __construct($interface = null, $channel = null)
      {
        $this->interface = $interface;
        $this->channel   = $channel;
      }
  }
  
class wlPowerSet
  {
    public  $interface,
            $power;
            
    function __construct($interface = null, $power = null)
      {
        $this->interface = $interface;
        $this->power     = $power;
      }
  }
  
class wlBandwidthSet
  {
    public  $interface,
            $bandwidth;
            
    function __construct($interface = null, $bandwidth = null)
      {
        $this->interface = $interface;
        $this->bandwidth = $bandwidth;
      }
  }
  
class wlSsidSet
  {
    public  $id,
            $ssid;
            
    function __construct($id = null, $ssid = null)
      {
        $this->id     = $id;
        $this->ssid   = $ssid;
      }
  }
  
class wlSecuritySet
  {
    public  $id,
            $security;
            
    function __construct($id = null, $sec = null)
      {
        $this->id         = $id;
        $this->security   = $security;
      }
  }
  
class wlWpaSet
  {
    public  $id,
            $wpa;
            
    function __construct($id = null, $wpa = null)
      {
        $this->id     = $id;
        $this->wpa    = $wpa;
      }
  }
?>