<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 13:40
 */

namespace AppBundle\Interfaces;


interface EICms
{
    const name = "WordPress";
    const versionURL = "http://api.wordpress.org/core/version-check/1.7/";

    public function getName();

    public function getVersion();

    public function getURL();

    public function getPackages();
}