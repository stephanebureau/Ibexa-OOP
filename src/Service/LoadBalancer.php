<?php
namespace App\Service;

use App\Entity\HostInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * loadbalancer that dispatches requests between different hosts according 2 modes (algorithms)
 */
class LoadBalancer
{

    private array $hosts;       // list of hosts entities
    private string $algorithm;  // loadbalancing algorithm: 'roundrobin'|'loadsharing'

    private int $nextHost = 0;  // used for roundrobin: id of the host to which next request shall be sent 


    /**
     * constructor
     * @param array     $hosts      array of hosts entities (each host shall implement HostInterface)
     * @param string    $algorithm  loadbalancing algorithm: 'roundrobin' (default) | 'loadsharing'
     * 
     * Exception is generated if no host is defined
     */
    public function __construct(array $hosts, string $algorithm = 'roundrobin')
    {
        if ( count($hosts) === 0 ) throw new \Exception('no target host has been defined');

        $this->hosts = $hosts;
        $this->algorithm = $algorithm;
    }


    /**
     * sends a request to one of the available hosts according defined algorithm
     * 
     * @param Request   request to be sent
     * @return Response response received from remote host
     */
    public function handleRequest(Request $request): Response {
        $host = $this->algorithm === 'loadsharing' ? $this->getHostByLoadSharing() : $this->getHostByRoundRobin();

        return $host->handleRequest( $request );
    }


    /**
     * select the host according round robin strategy
    */ 
    private function getHostByRoundRobin (): HostInterface {

        if ( $this->nextHost >= count($this->hosts) )  $this->nextHost = 0;

        return $this->hosts[ $this->nextHost++ ];
    }


    /**
     * select the host according load sharing strategy
     */
    private function getHostByLoadSharing (): HostInterface {
        $selectedHost = $this->hosts[0];    // selected host: initialize to first one
        $minLoad = 1;

        foreach( $this->hosts as $host ) {
            $load = $host->getLoad();

            if ( $load < 0.75 ) { $selectedHost = $host; break; }   // select first host with load < 0.75
            
            if ( $load < $minLoad ) {       // recursively find the host with min load if all hosts have a load > 0.75
                $selectedHost = $host;  
                $minLoad = $load;
            }
        }

        return $selectedHost;
    }

}