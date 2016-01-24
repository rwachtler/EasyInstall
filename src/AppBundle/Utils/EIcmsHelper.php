<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 23.01.16
 * Time: 15:15
 */

namespace AppBundle\Utils;


use AppBundle\EIconfig;

class EIcmsHelper
{
    /**
     * Generates random string (lowercase) with a defined length
     * @param $length - string length
     * @return string - generated string
     */
    public static function generateRandomString($length){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $ret_string = '';
        for ($i = 0; $i < $length; $i++) {
            $ret_string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $ret_string;
    }

    public static function unzipFile($sourceDir, $destinationDir){
        $zip = new \ZipArchive();

        if($zip->open($sourceDir)){
            $zip->extractTo($destinationDir);
            $zip->close();
            unlink($sourceDir);
            return true;
        }

        return false;
    }

    public static function performGetRequest($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response  = curl_exec($ch);

        return json_decode($response);
    }
}