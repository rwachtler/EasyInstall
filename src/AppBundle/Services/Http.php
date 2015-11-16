<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 15:02
 */

namespace AppBundle\Services;


class Http
{
    public function performGetRequest($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response  = curl_exec($ch);

        return json_decode($response);
    }
}