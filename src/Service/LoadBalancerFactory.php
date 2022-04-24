<?php
namespace App\Service;

use App\Entity\Host;
use Symfony\Contracts\HttpClient\HttpClientInterface;



/**
 * LoadBalancerFactory instantiates LoadBalancerService with hosts entities
 * Hosts entities are created from hostnames given in config/services.yaml file
 */
class LoadBalancerFactory
{

    private $algorithm;
    private $hostnames;
    private $httpclient;

    // list of hostnames and algorithm are defined in config/services.yaml file
    public function __construct(HttpClientInterface $httpclient, array $hostnames, string $algorithm = 'roundrobin')
    {
        $this->hostnames = $hostnames;
        $this->algorithm = $algorithm;
        $this->httpclient = $httpclient;
    }

    // instantiate LoadBalancerService
    public function __invoke() {
        $hosts = [];
        foreach( $this->hostnames as $hostname ) $hosts[] = new Host($this->httpclient, $hostname);

        return new LoadBalancer( $hosts, $this->algorithm );
    }

}