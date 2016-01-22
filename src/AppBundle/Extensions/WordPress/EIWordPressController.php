<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 16:10
 */

namespace AppBundle\Extensions\WordPress;

use AppBundle\EIconfig;
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
                'siteLanguage' => $request->request->get('site-language')
            );
            $response = new JsonResponse();

            if ( ! file_exists( EIconfig::$coreDirectoryPath . 'wordpress-' . $wordpressData->getVersion() . '-' . $params['siteLanguage']  . '.zip' ) ) {
                file_put_contents( EIconfig::$coreDirectoryPath . 'wordpress-' . $wordpressData->getVersion() . '-' . $params['siteLanguage']  . '.zip', file_get_contents( $wordpressData->getFullPackageForLanguage($params['siteLanguage']) ) );
                $response->setData(array(
                    'action' => 'Download WordPress',
                    'status' => 'success'
                ));
            } else{
                $response->setData(array(
                    'action' => 'Download WordPress',
                    'status' => 'error'
                ));
            }

            return $response;
        }

        return new Response('This is not ajax!', 400);
    }
}