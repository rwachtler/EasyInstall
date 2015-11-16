<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 13:31
 */

namespace AppBundle\Extensions\WordPress;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Interfaces\EICms;

class EIWordPressExtension extends Controller implements EICms
{

    private $version, $url, $packages;

    public function __construct($container = null){
        $this->url = "http://wordpress.org";
        $this->container = $container;
        $this->versionCheckData = $this->get("http")->performGetRequest(self::versionURL);
        $this->version = $this->versionCheckData->offers[0]->version;
        $this->packages = $this->versionCheckData->offers[0]->packages;
    }

    public function getName()
    {
        return self::name;
    }

    public function getVersion()
    {
         return $this->version;
    }

    public function getURL()
    {
        return $this->url;
    }

    public function getPackages(){
        return $this->packages;
    }
}