<?php
namespace App\Entity;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * example of implementation of the HostInterface
 * host is a server simply identify by its hostname
 * remote host load is recovered using SNMP MiB. A cache is implemented to limit SNMP requests
 */
class Host implements HostInterface
{

    private string $oid = '1.3.6.1.4.1.2021.11.9';  // SNMP MiB OID: CPU load
    private int $maxAge = 60000;        // cache age for load (1 min)
    private float $loadcache;           // cache for load
    private int $cachetimestamp = 0;    // cache timestamp

    private string $hostname;           // hostname of remote server
    
    private HttpClientInterface $httpclient;


    // constructor
    public function __construct( HttpClientInterface $httpclient, string $hostname ) {
        $this->httpclient = $httpclient;
        $this->hostname = $hostname;
    }


    // return the load of the remote host (from cache or from get SNMP)
    public function getLoad(): float {

        $current = \DateTime::getTimestamp();

        if ( $current - $this->cachetimestamp > $this->maxAge ) {
            // refresh cache if too old
            $load = snmp2_get( $this->hostname, 'public', $this->oid );
            $this->loadcache = $load !== false ? $load : 1; // maximum value if remote host cannot answer to get SNMP 
            $this->cachetimestamp = $current;
        }

        return $this->loadcache;
    }
    

    // sends the given request to the remote host and returns the received response
    // this code is quick&dirty and cannot work properly...
    // in particular, headers and response shall be parsed and not just forwarded to fix security problems with CORS or SNI or...
    public function handleRequest(Request $request): Response {
        $response = $this->httpclient->request(
            $request->getMethod(),
            $this->hostname.$request->getBaseUrl(),
            [ 'headers' => $request->headers->all() ]
        );

        return new Response(
            $response->getContent(false),
            $response->getStatusCode(),
            $response->getHeaders(false)
        );
    }

}