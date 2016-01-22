<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Extensions\WordPress\EIwordPressExtension;
use AppBundle\EIconfig;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        //$session->clear();
        if(!($session->has('db_exists'))){
            $this->setupDatabase($session);
            $session->set('db_exists',true);
        }
        // Acts as a container for available systems
        $extensions = array(
            "wordpress" => new EIwordPressExtension($this->container)
        );

        // return the default temlate
        return $this->render('main/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'extensions' => $extensions
        ));
    }

    /**
     * Setup database for user
     */
    private function setupDatabase($session){
        $connection = new \mysqli(EIconfig::$dbHost, EIconfig::$dbUser, EIconfig::$dbPass);

        if($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }
        $dbName = $this->generateRandomString(8);
        $session->set('dbName', $dbName);
        $sql = "CREATE DATABASE ".$dbName;
        if ($connection->query($sql) === TRUE) {
            return "Database created successfully";
        } else {
            echo "Error creating database: " . $connection->error;
        }

        $connection->close();
    }

    /**
     * Generates random string (lowercase) with a defined length
     * @param $length - string length
     * @return string - generated string
     */
    private function generateRandomString($length){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $ret_string = '';
        for ($i = 0; $i < $length; $i++) {
            $ret_string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $ret_string;
    }
}
