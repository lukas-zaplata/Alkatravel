<?php

namespace App;

use Nette\Application\Routers\CliRouter;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{
	private $container;
	
	public function __construct(Nette\DI\Container $container) {
		$this->container = $container;
	}

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		
		if ($this->container->parameters['consoleMode']) {
			$router[] = new CliRouter(array('action' => 'Cli:default'));
		} 
		else {
			$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		}
		
		return $router;
	}

}
