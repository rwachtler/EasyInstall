<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 16.11.15
 * Time: 16:52
 */

namespace AppBundle\Utils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\EIconfig;

class DBChecker
{
    /**
     * @Route("/check-connection", name="check-db-connection")
     */
    public function asyncDBCheck(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $databaseArgs = array(
                'name' => $request->request->get('name')
            );
            $connectionStatus = $this->checkConnection($databaseArgs);
            $response = new JsonResponse();

            if($connectionStatus){
                $response->setData(array(
                    'action' => 'db_check',
                    'status' => 'success'
                ));
            } else{
                $response->setData(array(
                    'action' => 'db_check',
                    'status' => 'failure'
                ));
            }

            return $response;
        }

        return new Response('This is not ajax!', 400);
    }

    private function checkConnection($params){
        $connection = new \mysqli('localhost', EIconfig::$dbUser, EIconfig::$dbPass, $params['name']);

        if($connection->connect_error) {
            return false;
        }
        return true;
    }
}