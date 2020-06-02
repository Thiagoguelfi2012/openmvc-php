<?php

/**
 * @name Framework Trivium
 * @version 1.0
 * @author João Escribano <joao.escribano@3force.com.br>
 * @copyright (c) 2012, Tri Force - Soluções digitais
 */
class Seed {

    private static $semente = 0;
    private static $hash = "246810";
    private static $key = '0q@Nto+Fu3@ZaL&s#mKSl0921_89nSUh$!@#%';
    private static $regex = "/id_.*|.*_id|id/";

    function __construct() {
        
    }

    public static function gerar($semente = 0, $min = 0, $max = 9999999, $looping = false) {
        $retorno = '';

        if (is_numeric($semente) && !$looping) {
            $retorno = self::hash($semente, $min, $max);
        } else {
            $retorno = $semente;
        }

        if (is_array($semente)) {
            foreach ($semente as $k => $v) {
                if (preg_match(self::$regex, $k)) {
                    $retorno[$k] = self::hash($v);
                    continue;
                }
                $retorno[$k] = self::gerar($semente[$k], $min, $max, true);
            }
        }

        return $retorno;
    }

    public static function decodificar($semente = 0, $min = 0, $max = 100, $looping = false) {
        $retorno = '';

        if (is_numeric($semente) && !$looping) {
            $retorno = self::unhash($semente, $min, $max);
        } else {
            $retorno = $semente;
        }

        if (is_array($semente)) {
            foreach ($semente as $k => $v) {
                if (preg_match(self::$regex, $k)) {
                    $retorno[$k] = self::unhash($v);
                    continue;
                }
                $retorno[$k] = self::decodificar($semente[$k], $min, $max, true);
            }
        }

        return $retorno;
    }

    private static function hash($semente, $min = 0, $max = 9999999) {
        self::$semente = $semente + 1;
        $hash = ((((self::$semente * self::$hash)) * ($max - $min + 1))) + $min;

        $nova_hash = "";
        for ($i = strlen($hash); $i >= 0; $i--) {
            $nova_hash .= substr($hash, $i, 1);
        }
        return $nova_hash;
    }

    private static function unhash($semente = 0, $min = 0, $max = 100) {
        $nova_hash = "";
        for ($i = strlen($semente); $i >= 0; $i--) {
            $nova_hash .= substr($semente, $i, 1);
        }
        $nova_hash = substr(($nova_hash - $min) / self::$hash, 0, -7) - 1;
        return $nova_hash;
    }

    public static function cryptCartao($numero) {
        $tmp = sha1(md5(self::$key . $numero));
        return $tmp;
    }

    public static function decryptCartao($hash) {
        //funcao para reverter o md5
        for ($i = 0; $i < 10000; $i++) {
            $j = str_pad($i, 4, "0", STR_PAD_LEFT);
            if (sha1(md5(self::$key . $j)) == $hash) {
                $ret = $j;
            }
        }
        if (isset($ret)) {
            return $ret;
        } else {
            Alertas::adicionar("Não foi possível gerar os últimos dígitos do cartão ou o código de segurança");
        }
    }

    public static function decryptCodSeguranca($hash) {
        //funcao para reverter o md5
        for ($i = 0; $i < 10000; $i++) {
            $j = str_pad($i, 4, "0", STR_PAD_LEFT);
            if (sha1(md5(self::$key . $j)) == $hash) {
                $ret = $j;
            }
            if (!isset($ret)) {
                $j = str_pad($i, 3, "0", STR_PAD_LEFT);
                if (sha1(md5(self::$key . $j)) == $hash) {
                    $ret = $j;
                }
            }
        }
        if (isset($ret)) {
            return $ret;
        } else {
            Alertas::adicionar("Não foi possível gerar os últimos dígitos do cartão ou o código de segurança");
        }
    }

}

?>