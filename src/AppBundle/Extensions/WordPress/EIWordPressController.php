<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 16:10
 */

namespace AppBundle\Extensions\WordPress;

use AppBundle\EIconfig;
use AppBundle\Utils\EIcmsHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
class EIwordPressController extends Controller
{


    /**
     * @Route("/wordpress", name="_wordpress")
     */
    public function indexAction(Request $request){
        $wordpressData = new EIwordPressExtension($this->container);
        $session = $request->getSession();
        $dbName = $session->get('dbName');
        // TODO: Render the template for WordPress
        return $this->render('extensions/wordpress.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'wordpressData' => $wordpressData,
            'dbName' => $dbName,
            'availableLanguages' => $wordpressData->getAvailableLanguages($wordpressData->getVersion())
        ));

    }

    /**
     * @Route("/wp-download", name="_wp-download")
     */
    public function downloadWpAction(Request $request){
        $wordpressData = new EIwordPressExtension($this->container);
        if ($request->isXMLHttpRequest()) {
            $params = array(
                'siteLanguage' => $request->request->get('site-language'),
                'noContent' => $request->request->get('no-content')
            );
            $response = new JsonResponse();
            $wpZipPackagePath = EIconfig::$coreDirectoryPath . 'wp-' . $wordpressData->getVersion() . '-' . $params['siteLanguage']  . '.zip';
            if ( ! file_exists( $wpZipPackagePath ) ) {
                if($params['noContent'] == 'on'){
                    file_put_contents( $wpZipPackagePath, file_get_contents( $wordpressData->getNoContentPackageForLanguage($params['siteLanguage']) ) );

                } else{
                    file_put_contents( EIconfig::$coreDirectoryPath . 'wp-' . $wordpressData->getVersion() . '-' . $params['siteLanguage']  . '.zip', file_get_contents( $wordpressData->getFullPackageForLanguage($params['siteLanguage']) ) );
                }
                if(EIcmsHelper::unzipFile($wpZipPackagePath)){
                    $response->setData(array(
                        'action' => 'Download & Unzip WordPress',
                        'status' => 'success'
                    ));
                } else{
                    $response->setData(array(
                        'action' => 'Download & Unzip WordPress',
                        'status' => 'error'
                    ));
                }

            } else{
                $response->setData(array(
                    'action' => 'Download & Unzip WordPress',
                    'status' => 'error'
                ));
            }

            return $response;
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/wp-config", name="_wp-config")
     */
    public function configureWordPress(Request $request){
        $response = new JsonResponse();
        if($request->isXmlHttpRequest()){

            $params = array(
                'siteTitle' => urldecode($request->request->get('site-title')),
                'siteLanguage' => $request->request->get('site-language'),
                'siteUrl' => urldecode($request->request->get('user-site-url')),
                'dbName' => $request->request->get('database-name'),
                'username' => urldecode($request->request->get('username')),
                'password' => $request->request->get('password'),
                'email' => urldecode($request->request->get('email')),
                'privacy' => (bool)(int)$request->request->get('privacy')
            );

            // Get the configuration file sample
            $configFile = file(EIconfig::$coreDirectoryPath . 'wordpress/wp-config-sample.php');

            // Get WP-Security keys
            $securityKeys = explode( "\n", file_get_contents( 'https://api.wordpress.org/secret-key/1.1/salt/' ) );
            foreach ( $securityKeys as $k => $v ) {
                $securityKeys[$k] = substr( $v, 28, 64 );
            }

            // We change the data
            $key = 0;
            foreach ( $configFile as &$line ) {
                if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
                    $line = '$table_prefix  = \'' . EIcmsHelper::generateRandomString(4) . "_';\r\n";
                    continue;
                }
                if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) ) {
                    continue;
                }
                $constant = $match[1];
                switch ( $constant ) {
                    case 'DB_NAME'     :
                        $line = "define('DB_NAME', '" . $params['dbName'] . "');\r\n";
                        break;
                    case 'DB_USER'     :
                        $line = "define('DB_USER', '" . EIconfig::$dbUser . "');\r\n";
                        break;
                    case 'DB_PASSWORD' :
                        $line = "define('DB_PASSWORD', '" . EIconfig::$dbPass . "');\r\n";
                        break;
                    case 'DB_HOST'     :
                        $line = "define('DB_HOST', '" . EIconfig::$dbHost . "');\r\n";
                        break;
                    case 'AUTH_KEY'         :
                    case 'SECURE_AUTH_KEY'  :
                    case 'LOGGED_IN_KEY'    :
                    case 'NONCE_KEY'        :
                    case 'AUTH_SALT'        :
                    case 'SECURE_AUTH_SALT' :
                    case 'LOGGED_IN_SALT'   :
                    case 'NONCE_SALT'       :
                        $line = "define('" . $constant . "', '" . $securityKeys[$key++] . "');\r\n";
                        break;
                    case 'WPLANG' :
                        $line = "define('WPLANG', '" . $params['siteLanguage'] . "');\r\n";
                        break;
                }
            }
            unset( $line );
            $handle = fopen( EIconfig::$coreDirectoryPath . 'wordpress/wp-config.php', 'w' );
            foreach ( $configFile as $line ) {
                fwrite( $handle, $line );
            }
            fclose( $handle );

            // Set rights
            chmod( EIconfig::$coreDirectoryPath . 'wordpress/wp-config.php', 0666 );

            $response->setData($this->installWordPress($params));

            return $response;
        }

        return new Response("This is not ajax!", 400);
    }

    /**
     * @param $params - user inputs [siteTitle, siteLanguage, siteUrl, dbName, username, password, email, privacy]
     * @return array - execution status array
     */
    private function installWordPress($params){

        define( 'WP_INSTALLING', true );

        /** Load WordPress installation files */
        require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-load.php' );
        require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-admin/includes/upgrade.php' );
        require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-includes/wp-db.php' );

        /** Install WordPress */
        wp_install( $params['siteTitle'], $params['username'], $params['email'], $params['privacy'], '', $params['password'], $params['siteLanguage'] );
        update_option( 'siteurl', $params['siteUrl'] );
        update_option( 'home', $params['siteUrl'] );
        return array(
            'action' => 'Install WordPress',
            'status' => 'success'
        );
    }
}