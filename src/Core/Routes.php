<?php

namespace Cruzer\Framework\Core;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * This class handles request by dunamic passing routeInfo to the serve method
 * @author RN Kushwaha <Rn.kushwaha022@gmail.com>
 * @since version 1. Date: 15th Jan, 2018
 */

class Routes implements HttpKernelInterface{

	protected $routes;
	protected $dispatcher;

	public function __construct() {
		$this->routes     = new RouteCollection();
		$this->dispatcher = new EventDispatcher();
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
		$matcher            = new UrlMatcher($this->routes, new RequestContext());
		$dispatcher         = new EventDispatcher();
		$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

		$controllerResolver = new ControllerResolver();
		$argumentResolver   = new ArgumentResolver();
		$kernel             = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);
		$response           = $kernel->handle($request );
		$response->send();

		$kernel->terminate($request, $response);
	}

	public function addRoute($path, $controller, $method = null) {
		if( isset($method) && $method!=null){
			$method = explode('|',$method);
		}
		$this->routes->add( $path, new Route( $path, array('_controller' => $controller ), [], [], '', [], $method ));
	}

	public function add($method, $path, $controller) {
		$this->addRoute($path, $controller, $method );
	}

	public function get($path, $controller) {
		$this->addRoute($path, $controller,'GET');
	}

	public function post($path, $controller) {
		$this->addRoute($path, $controller,'POST');
	}

	public function all($path, $controller) {
		$this->addRoute($path, $controller);
	}

	public function on($event, $callback) {
		 $this->dispatcher->addListener($event, $callback);
	}

	public function fire($event)
    {
	    return $this->dispatcher->dispatch($event);
	}


	public function getRequest()
    {
	    return $request = Request::createFromGlobals();
	}

}

