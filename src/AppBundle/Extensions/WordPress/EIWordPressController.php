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

class EIWordPressController extends Controller
{
    /**
     * @Route("/wordpress", name="_wordpress")
     */
    public function indexAction(){

        $wordpressData = new EIWordPressExtension($this->container);

        // TODO: Render the template for WordPress
        return $this->render('extensions/wordpress.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'wordpressData' => $wordpressData
        ));

    }
}