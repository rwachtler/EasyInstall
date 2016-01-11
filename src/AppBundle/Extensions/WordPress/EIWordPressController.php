<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 12.11.15
 * Time: 16:10
 */

namespace AppBundle\Extensions\WordPress;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class EIwordPressController extends Controller
{
    /**
     * @Route("/wordpress", name="_wordpress")
     */
    public function indexAction(Request $request){
        $wordpressData = new EIwordPressExtension($this->container);
        $session = $request->getSession();
        $dbPrefix = $session->get('dbPrefix');
        $dbName = $session->get('dbName');
        // TODO: Render the template for WordPress
        return $this->render('extensions/wordpress.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'wordpressData' => $wordpressData,
            'dbPrefix' => $dbPrefix,
            'dbName' => $dbName
        ));

    }
}