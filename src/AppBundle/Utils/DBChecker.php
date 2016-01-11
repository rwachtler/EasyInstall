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

class DBChecker
{
    /**
     * @Route("/check-connection", name="check-db-connection")
     */
    public function asyncDBCheck(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            // TODO call checkConnection return a succeed JSON if true
            $databaseArgs = array(
                'name' => $request->request->get('name'),
                'user' => $request->request->get('username'),
                'pass' => $request->request->get('pass'),
                'prefix' => $request->request->get('prefix')
            );
            $connectionStatus = $this->checkConnection($databaseArgs);
            if($connectionStatus){
                return new JsonResponse(array('action' => 'db_check', 'status' => 'success'));
            } else{
                return new JsonResponse(array('action' => 'db_check', 'status' => 'failure'));
            }
        }

        return new Response('This is not ajax!', 400);
    }

    private function checkConnection($params){
        // TODO if no connection return false / if connection succeeded --> return true
        $connection = new \mysqli('localhost', $params->user, $params->pass, $params->name);

        if($connection->connect_error) {
            return false;
        }
        return true;
    }
}