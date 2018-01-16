<?php

namespace Cruzer\Framework\Core;
use Tracy\Debugger;
use DebugBar\StandardDebugBar;

/**
 * This class handles request by dunamic passing routeInfo to the serve method
 * @author RN Kushwaha <Rn.kushwaha022@gmail.com>
 * @since version 1. Date: 9th Jan, 2018
 */

class Handler {
	protected $debugbar;

	public function serve( $routeInfo ) {
        list( $controller, $action )  = explode('@', $routeInfo[1]);
        $params                       = $routeInfo[2];
        $controllerStr = "\\".APP_DIR."\\".CONTROLLER_DIR."\\".$controller;
        $controllerObj = new $controllerStr();
        $controllerObj->$action($params);
	}

	public function initilize( ) {
		//include tracy
		Debugger::enable(Debugger::DETECT, LOG_DIR);
		Debugger::$strictMode = true;

		// Debugger::$logSeverity = E_NOTICE | E_WARNING;
		// Debugger::$email = 'admin@example.com';

		/* Debugger::log('Unexpected error'); // text message
		// Debugger::log($e, Debugger::ERROR); // also sends an email notification

		// Debugger::dump([10, 20.2, true, null, 'hello']);

		// //log errors to the chrom console.
		// Debugger::fireLog('Hello World');
		// Debugger::fireLog($_SERVER);
		// Debugger::fireLog(new Exception('Test Exception'));
		// Debugger::timer();
		// sleep(2);
		// echo $elapsed = Debugger::timer();*/

		//use monolog for logging
		// use Monolog\Logger;
		// use Monolog\Handler\StreamHandler;

		// // create a log channel
		// $logger = new Logger('Cruzer');
		// $logger->pushHandler(new StreamHandler(__DIR__ . '/log/cruzer.log', Logger::DEBUG));

		// // add records to the log
		// $logger->warning('Foo');
		// $logger->error('Bar');
		// $logger->info('My logger is now ready');
		// $logger->info('Adding a new user', array('username' => 'Seldaek'));

		//get dotenv
		$dotenv = new \Dotenv\Dotenv(DIR);
		$dotenv->load();
	}

	public function enableDebuger(){
		return $this->debugbar = new StandardDebugBar();
	}

	public function renderDebugBar(){
		$debugbarRenderer = $this->debugbar->getJavascriptRenderer()->setBaseUrl(DEBUG_DIR);
		echo "<html> <head>". $debugbarRenderer->renderHead(). "</head> <body> ".$debugbarRenderer->render()."</body> </html>";
	}

	public function addMessage( $message, $tab = 'messages'){
		$this->debugbar[$tab]->addMessage($message);
	}

}

