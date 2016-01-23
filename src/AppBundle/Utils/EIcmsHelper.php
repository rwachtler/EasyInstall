<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 23.01.16
 * Time: 15:15
 */

namespace AppBundle\Utils;


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

    public static function unzipFile($dir){
        $zip = new \ZipArchive();

        if($zip->open($dir)){
            $zip->extractTo('wordpress/');
            $zip->close();
        }
    }
}