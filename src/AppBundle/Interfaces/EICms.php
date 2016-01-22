<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 13:40
 */

namespace AppBundle\Interfaces;


interface EIcms
{
    public function getName();

    public function getVersion();

    public function getSiteUrl();

    public function getPackages();

    public function getAvailableLanguages($version);
}