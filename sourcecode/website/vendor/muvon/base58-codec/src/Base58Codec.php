<?php
namespace Muvon\KISS;

class Base58Codec {
  public static function checkEncode(string $hex): string {
    $binary = hex2bin($hex);
    $hash = hash('sha256', hash('sha256', $binary, true));
    $checksum = substr($hash, 0, 8);
    $hex = $binary . hex2bin($checksum);
    return static::encode($hex);
  }

  public static function checkDecode(string $base58add): string {
    $binary = static::decode($base58add);
    $checksum = substr($binary, -4);
    $binary = substr($binary, 0, -4);
    $hash = hash('sha256', hash('sha256', $binary, true));
    $checksum0 = substr($hash, 0, 8);
    $checksum1 = bin2hex($checksum);
    if (strcmp($checksum0, $checksum1)) {
      return false;
    }
    return bin2hex($binary);
  }


  public static function encode(string $string): string {
    $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $base = strlen($alphabet);
    if (!is_string($string)) {
      return false;
    }
    if (strlen($string) === 0) {
      return '';
    }
    $bytes = array_values(unpack('C*', $string));
    $decimal = $bytes[0];
    for ($i = 1, $l = sizeof($bytes); $i < $l; $i++) {
      $decimal = bcmul($decimal, 256);
      $decimal = bcadd($decimal, $bytes[$i]);
    }
    $output = '';
    while ($decimal >= $base) {
      $div = bcdiv($decimal, $base, 0);
      $mod = bcmod($decimal, $base);
      $output .= $alphabet[$mod];
      $decimal = $div;
    }
    if ($decimal > 0) {
      $output .= $alphabet[$decimal];
    }
    $output = strrev($output);
    foreach ($bytes as $byte) {
      if ($byte === 0) {
        $output = $alphabet[0] . $output;
        continue;
      }
      break;
    }
    return (string) $output;
  }

  public static function decode(string $base58): string {
    $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $base = strlen($alphabet);
    if (!is_string($base58)) {
      return false;
    }
    if (strlen($base58) === 0) {
      return '';
    }
    $indexes = array_flip(str_split($alphabet));
    $chars = str_split($base58);
    foreach ($chars as $char) {
      if (isset($indexes[$char]) === false) {
        return false;
      }
    }
    $decimal = $indexes[$chars[0]];
    for ($i = 1, $l = sizeof($chars); $i < $l; $i++) {
      $decimal = bcmul($decimal, $base);
      $decimal = bcadd($decimal, $indexes[$chars[$i]]);
    }
    $output = '';
    while ($decimal > 0) {
      $byte = bcmod($decimal, 256);
      $output = pack('C', $byte) . $output;
      $decimal = bcdiv($decimal, 256, 0);
    }
    foreach ($chars as $char) {
      if ($indexes[$char] === 0) {
        $output = "\x00" . $output;
        continue;
      }
      break;
    }
    return $output;
  }
}