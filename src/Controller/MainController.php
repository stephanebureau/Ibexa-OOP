<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\LoadBalancer;



/**
 * basic controller forwarding all received request to laoad balancer service
 */
class MainController extends AbstractController
{

    public function homepage(Request $request, LoadBalancer $loadbalancer): Response
    {

        return $loadbalancer->handleRequest( $request );
    }
}