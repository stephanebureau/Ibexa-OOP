<?php
namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\LoadBalancer;
use App\Entity\Host;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class LoadBalancerTest extends TestCase
{
    private $request;
    private $host1;
    private $host2;
    private $host3;
    private $host4;

    protected function setUp(): void
    {
        $this->request = $this->createStub(Request::class);
        
        $this->host1 =  $this->createStub(Host::class);
        $this->host1->method('getLoad')->willReturnOnConsecutiveCalls( 0.8, 0.7 );
        $this->host1->method('handleRequest')->willReturn( new Response( 'host1', Response::HTTP_OK ) );

        $this->host2 =  $this->createStub(Host::class);
        $this->host2->method('getLoad')->willReturn( 0.5 );
        $this->host2->method('handleRequest')->willReturn( new Response( 'host2', Response::HTTP_OK ) );

        $this->host3 =  $this->createStub(Host::class);
        $this->host3->method('getLoad')->willReturnOnConsecutiveCalls( 0.9, 0.7 );
        $this->host3->method('handleRequest')->willReturn( new Response( 'host3', Response::HTTP_OK ) );

        $this->host4 =  $this->createStub(Host::class);
        $this->host4->method('getLoad')->willReturn( 0.8 );
        $this->host4->method('handleRequest')->willReturn( new Response( 'host4', Response::HTTP_OK ) );

    }

    // test loadbalancer with roudrobin strategy
    public function testRoundRobin(): void
    {
        $loadbalancer = new LoadBalancer([$this->host1, $this->host2],'roundrobin');

        $this->assertSame('host1', $loadbalancer->handleRequest($this->request)->getContent() );
        $this->assertSame('host2', $loadbalancer->handleRequest($this->request)->getContent() );
        $this->assertSame('host1', $loadbalancer->handleRequest($this->request)->getContent() );
    }


    // test loadbalancer with loadsharing strategy where one host has a load < 0.75
    public function testLoadSharingWithLowLoadedHost(): void
    {
        $loadbalancer = new LoadBalancer([$this->host1, $this->host2],'loadsharing');

        $this->assertSame('host2', $loadbalancer->handleRequest($this->request)->getContent() );
        $this->assertSame('host1', $loadbalancer->handleRequest($this->request)->getContent() );
    }


    // test loadbalancer with loadsharing strategy where all hosts have a load > 0.75
    public function testLoadSharingWithOnlyHighLoadedHost(): void
    {
        $loadbalancer = new LoadBalancer([$this->host3, $this->host4],'loadsharing');

        $this->assertSame('host4', $loadbalancer->handleRequest($this->request)->getContent() );
        $this->assertSame('host3', $loadbalancer->handleRequest($this->request)->getContent() );
    }
}