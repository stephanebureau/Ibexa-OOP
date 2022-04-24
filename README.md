# Ibexa : OOP Task
**LoadBalancer**


I have implemented the load balancer in PHP using Symfony v5.4 framework even if it can be achieved in any language.

In order to have a simpler integration with Symfony logic, I have modifed a bit the requirement: the method handleRequest(Request $request) of the host class managing the remote host returns a Response object (rather than nothing (void)).


## files
The code is splitted in the following files:
* **src\Service\LoadBalancer.php** : the load balancer service as requested with 2 modes of loadbalancing (round robin and load sharing)
* **src\Entity\HostInterface.php** : description of the interface that shall be supported by all host instances
* **tests\Service\LoadBalancerTest.php** : unit test (in PHPUnit) of the loadbalancer service


## extra
I have coded an example on how the loadbalancer service can be used in Symfony to implement a real load balancer. This coce is just an example and shall be improved to correctly work in production.

The example can be found in following files :
* **src\Entity\Host.php** : an basic implementation of the HostInterface. Remote server load is recovered via SNMP, requests are simply forwarded to remote server. More work is needed to make this code work properly with any remote host (including via https).
* **src\Service\LoadBalancerFactory.php** : a factory that instantiates the load balancer service with Host entities. hostnames shall be defined in _conf/services.yaml file_.
* **src\Controller\MainController.php** : the minimalist controller that implements load balancer
