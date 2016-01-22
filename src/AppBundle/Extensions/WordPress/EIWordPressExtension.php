<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 13:31
 */

namespace AppBundle\Extensions\WordPress;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Interfaces\EIcms;

class EIwordPressExtension extends Controller implements EIcms
{


    const name = "WordPress";
    const versionURL = "http://api.wordpress.org/core/version-check/1.7/";
    const versionURLlocale = "http://api.wordpress.org/core/version-check/1.7/?locale=";
    private $version, $siteUrl, $packages;

    public function __construct($container = null){
        $this->siteUrl = "http://wordpress.org";
        $this->container = $container;
        $versionCheckData = $this->get("http")->performGetRequest(self::versionURL);
        $this->version = $versionCheckData->offers[0]->version;
        $this->packages = $versionCheckData->offers[0]->packages;
    }

    public function getName()
    {
        return self::name;
    }

    public function getVersion()
    {
         return $this->version;
    }

    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    public function getPackages(){
        return $this->packages;
    }

    public function getAvailableLanguages($selectedWPversion)
    {
        $req_url = "http://api.wordpress.org/translations/core/1.0/?version=".$selectedWPversion;
        $availableLanguages = json_decode(file_get_contents($req_url))->translations;

        return $availableLanguages;
    }

    public function getFullPackageForLanguage($language){
        $wp = $this->get("http")->performGetRequest(self::versionURLlocale.$language);
        $package = $wp->offers[0]->packages->full;

        return $package;
    }

    public function getNoContentPackageForLanguage($language){
        $wp = $this->get("http")->performGetRequest(self::versionURLlocale.$language);
        $package = $wp->offers[0]->packages->no_content;

        return $package;
    }
}