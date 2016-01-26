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
use AppBundle\Utils\MySQLDump;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EIwordPressController extends Controller
{

    /** --- Routes --- */

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
                'siteLanguage' => $request->request->get('site-language')
            );
            $response = new JsonResponse();
            $wpZipPackagePath = EIconfig::$coreDirectoryPath . 'wp-' . $wordpressData->getVersion() . '-' . $params['siteLanguage']  . '.zip';
            if ( ! file_exists( $wpZipPackagePath ) ) {

                // Download package
                file_put_contents( $wpZipPackagePath, file_get_contents( $wordpressData->getFullPackageForLanguage($params['siteLanguage']) ) );

                if(EIcmsHelper::unzipFile($wpZipPackagePath, EIconfig::$coreDirectoryPath)){
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
                'privacy' => (bool)(int)$request->request->get('privacy'),
                'noContent' => $request->request->get('no-content')
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
     * @Route("/wp-install-theme", name="_wp-install-theme")
     */
    public function installThemes(Request $request){
        if ($request->isXMLHttpRequest()) {
            $response = new JsonResponse();
            // Load WordPress Admin Upgrade API
            require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-load.php' );
            require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-admin/includes/upgrade.php' );

            $themes = $request->request->get('user_themes');

            // Create a temporary directory for the theme ZIPs
            $tmp_dir_path = EIconfig::$coreDirectoryPath.'_tmp/';
            mkdir($tmp_dir_path,0755);
            /** Download theme ZIPs, unpack them inside the temporary directory
                move the unpacked folders to /wordpress/wp-content/themes/
             */
            foreach($themes as $theme){
                $theme = json_decode(json_encode($theme));
                // Extract ZIP name from URL
                $keys = parse_url($theme->url);
                $urlPath = explode("/", $keys['path']);
                $zipName = end($urlPath);
                // Download ZIP
                file_put_contents( $tmp_dir_path.$zipName, file_get_contents( $theme->url ) );
                if(EIcmsHelper::unzipFile($tmp_dir_path.$zipName, $tmp_dir_path)){
                    // Get theme directory name
                    $themeDir = strtok($zipName,".");
                    // Move theme directory to wordpress/wp-content/themes/
                    rename($tmp_dir_path.$themeDir, EIconfig::$coreDirectoryPath.'wordpress/wp-content/themes/'.$themeDir);
                    // Check if theme should be enabled
                    if($theme->enable == "true"){
                        switch_theme($themeDir, $themeDir);
                    }
                } else{
                    $response->setData(array(
                        'action' => 'Installing User Themes',
                        'status' => 'error'
                    ));
                }
            }
            // Remove temporary directory
            rmdir($tmp_dir_path);
            $response->setData(array(
                'action' => 'Installing User Themes',
                'status' => 'success'
            ));
            return $response;
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/wp-install-plugin", name="_wp-install-plugin")
     */
    public function installPlugins(Request $request){
        if ($request->isXMLHttpRequest()) {
            $response = new JsonResponse();
            // Load WordPress Plugin API
            require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-load.php' );
            require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-admin/includes/plugin.php' );

            $plugins = $request->request->get('user_plugins');

            // Create a temporary directory for the theme ZIPs
            $tmp_dir_path = EIconfig::$coreDirectoryPath.'_tmp/';
            mkdir($tmp_dir_path,0755);
            /** Download plugin ZIPs, unpack them inside the temporary directory
            move the unpacked folders to /wordpress/wp-content/plugins/ and activate if needed
             */
            foreach($plugins as $plugin){
                $plugin = json_decode(json_encode($plugin));
                // Extract ZIP name from URL
                $keys = parse_url($plugin->url);
                $urlPath = explode("/", $keys['path']);
                $zipName = end($urlPath);
                // Get theme directory name
                $pluginDir = strtok($zipName,".");
                // Download ZIP
                file_put_contents( $tmp_dir_path.$zipName, file_get_contents( trim($plugin->url) ) );
                if(EIcmsHelper::unzipFile($tmp_dir_path.$zipName, $tmp_dir_path)){
                    // Move plugin directory to wordpress/wp-content/plugins/
                    rename($tmp_dir_path.$pluginDir, EIconfig::$coreDirectoryPath.'wordpress/wp-content/plugins/' . $pluginDir);
                }
                else{
                    $response->setData(array(
                        'action' => 'Installing User Plugins',
                        'status' => 'error'
                    ));
                }
            }
            // Remove temporary directory
            rmdir($tmp_dir_path);
            $response->setData(array(
                'action' => 'Installing User Plugins',
                'status' => 'success'
            ));
            return $response;
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/wp-activate-plugin", name="_wp-activate-plugin")
     */
    public function activatePlugins(Request $request){
        if($request->isXmlHttpRequest()) {
            $response = new JsonResponse();
            // Load WordPress Plugin API
            require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-load.php');
            require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-admin/includes/plugin.php');

            $plugins = $request->request->get('user_active_plugins');

            foreach($plugins as $plugin){
                $plugin = json_decode(json_encode($plugin));
                if($plugin->enable == "true"){
                    // Get keys from all existing plugins
                    $allPluginKeys = array_keys(get_plugins());
                    // Extract plugin name from URL
                    $keys = parse_url($plugin->url);
                    $urlPath = explode("/", $keys['path']);
                    $pluginDir = strtok(end($urlPath), ".");

                    foreach($allPluginKeys as $pluginPath){
                        if(strpos($pluginPath, $pluginDir) !== false){
                            activate_plugin($pluginPath);
                        }
                    }
                }
            }
            $response->setData(array(
                'action' => 'Activating User Plugins',
                'status' => 'success'
            ));
            return $response;
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/wp-insert-post", name="_wp-insert-post")
     */
    public function insertPosts(Request $request){
        if($request->isXmlHttpRequest()) {
            $response = new JsonResponse();
            // Load WordPress API
            require_once( EIconfig::$coreDirectoryPath . 'wordpress/wp-load.php');

            $posts = $request->request->get('user_posts');

            foreach($posts as $post){
                $post = json_decode(json_encode($post));
                if ( isset( $post->title ) && !empty( $post->title ) ) {
                    $parent = get_page_by_title( trim( $post->parent ) );
                    $parent = $parent ? $parent->ID : 0;

                    $args = array(
                        'post_title' 		=> trim( $post->title ),
                        'post_name'			=> $post->slug,
                        'post_content'		=> trim( $post->content ),
                        'post_status' 		=> $post->status,
                        'post_type' 		=> $post->type,
                        'post_parent'		=> $parent,
                        'post_author'		=> 1,
                        'post_date' 		=> date('Y-m-d H:i:s'),
                        'post_date_gmt' 	=> gmdate('Y-m-d H:i:s'),
                        'comment_status' 	=> 'closed',
                        'ping_status'		=> 'closed'
                    );
                    wp_insert_post( $args );
                }
            }
            $response->setData(array(
                'action' => 'Inserting posts',
                'status' => 'success'
            ));
            return $response;
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("wp-export-package", name="_wp-export-package")
     */
    public function exportZipPackage(Request $request){

        $session = $request->getSession();
        $dbName = $session->get('dbName');
        $folderPath = EIconfig::$coreDirectoryPath;

        EIcmsHelper::createMySQLDump($folderPath, $dbName);

        $zipName = EIcmsHelper::zipFolder($folderPath, "wp_".EIcmsHelper::generateRandomString(5));

        $session->set('zipName', $zipName);

        $pathToZipFile = $folderPath.$zipName;

        $response = new BinaryFileResponse($pathToZipFile);

        $response->headers->set('Content-Type', mime_content_type($pathToZipFile));
        $response->headers->set('Content-Length', filesize($pathToZipFile));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $zipName
        );

        return $response;
    }

    /**
     * @Route("wp-cleanup", name="_wp-cleanup")
     */
    public function cleanup(Request $request){

        $session = $request->getSession();
        $response = new JsonResponse();

        EIcmsHelper::deleteDatabase($session->get('dbName'));
        EIcmsHelper::removeDir(EIconfig::$coreDirectoryPath.'wordpress');
        unlink(EIconfig::$coreDirectoryPath.$session->get('zipName'));
        unlink(EIconfig::$coreDirectoryPath.$session->get('dbName').'.sql');
        $response->setData(array(
            'action' => 'Cleaning up',
            'status' => 'success'
        ));

        return $response;
    }

    /** --- Private Methods --- */

    /**
     * @param $params - user inputs [siteTitle, siteLanguage, siteUrl, dbName, username, password, email, privacy, noContent]
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

        if($params['noContent'] == 'on'){
            wp_delete_post(1, true);
            wp_delete_post(2, true);
        }

        return array(
            'action' => 'Install WordPress',
            'status' => 'success'
        );
    }

}