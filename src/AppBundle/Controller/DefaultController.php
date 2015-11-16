<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Extensions\WordPress\EIWordPressExtension;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // Acts as a container for available systems
        $extensions = array(
            "wordpress" => new EIWordPressExtension($this->container)
        );

        // return the default temlate
        return $this->render('main/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'extensions' => $extensions
        ));
    }
}
