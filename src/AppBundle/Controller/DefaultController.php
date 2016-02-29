<?php

namespace AppBundle\Controller;

use AppBundle\Utils\EIcmsHelper;
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
            $this->setupUserFolder($session);
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
        $dbName = EIcmsHelper::generateRandomString(8);
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
     * Setup user folder
     */
    private function setupUserFolder($session){
        $dirName = EIcmsHelper::generateRandomString(8) . '_' . time() . '/';
        $dirPath = EIconfig::$coreDirectoryPath . $dirName;
        mkdir($dirPath, 0755);
        $session->set('user_folder', $dirName);
    }

}
