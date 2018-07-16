<?php
namespace app\modules;

use std, gui, framework, app;


class crypt extends AbstractModule
{
    private static function rc4_crypt($str, $key) {
        $s = array();
        for ($i = 0; $i < 256; $i++) {
            $s[$i] = $i;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
        }
        $i = 0;
        $j = 0;
        $res = '';
        for ($y = 0; $y < strlen($str); $y++) {
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
            $res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
        }
        return $res;
    }
    
    public static function encrypt($text, $password)
    {
        return base64_encode(self::rc4_crypt($text, $password));
    }
    
    public static function decrypt($text, $password)
    {
        return self::rc4_crypt(base64_decode($text), $password);
    }

}