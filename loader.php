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
 * 
 *    MySNMPv2_mappers obsahuje veškeré využívané objekty
 *    Privátní SNMP jsou izolované ve vlastních knihovnách, automaticky se načítají všechny soubory z ./devices
 *      - dodržujte pojmenování souboru a knihovny ve formátu "modelname" (lowercase). Pro společné dotazy (vypsané v interface CableModemCalls) se přepíná mezi knihovnami hledáním "modelname" v sysDescr zařízení.
 *      - v každé knihovně připojte pole "supports" jako konstantu a vpište 100% funkční metody, které zařízení podporuje
 *      - v každé knihovně připojte assoc pole "macdiff" s přepočtem CM MAC, MTA, eRouter pro zjišťování typu interface
 *      - zbytek dotazů je předepsán v interface CableModemCalls
 */
namespace MySNMPv2;
require __DIR__ . '/../phpSNMP/mib_format.php';
require __DIR__ . '/MySNMPv2_mappers.php';
require __DIR__ . '/MySNMPv2.php';

$MySNMPv2_CPEs = array();

foreach(glob( __DIR__ . '/devices/*.php' ) as $lib)
  {
      $MySNMPv2_CPEs[] = str_replace('.php', EMPTY_STRING, basename($lib));
      require $lib;
  }

