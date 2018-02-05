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

	public function initilize( ) {
		//get dotenv
		$dotenv = new \Dotenv\Dotenv(ROOT_DIR);
		$dotenv->load();

		date_default_timezone_set( env('APP_TIMEZONE', 'Asia/Calcutta'));
		define('ERROR_LEVEL', env('APP_ENV', 'production'));
		define('DEBUG_ERROR', env('APP_DEBUG', 'On'));

		switch (ERROR_LEVEL) {
		    case 'development':
		        ini_set('display_startup_errors',1);
		        ini_set('display_errors', DEBUG_ERROR);
		        error_reporting(E_ALL);
		    break;
		    case 'testing'    : error_reporting( E_ALL ^ E_NOTICE );
		                        ini_set('display_errors', DEBUG_ERROR); break;
		    case 'production' : ini_set('display_errors', DEBUG_ERROR); break;
		    default           : error_reporting( E_ALL );
		                        ini_set('display_errors', DEBUG_ERROR); break;
		}

		if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		define('APP_DIR', env('APP_DIR','App'));
		define('APP_URL', env('APP_URL'));
		define('APP_UNDER_DIR', env('APP_UNDER_DIR'));
		define('APP_TITLE', env('APP_NAME'));
		define('VIEW_DIR', env('VIEW_DIR','Views'));
		define('CONTROLLER_DIR', env('CONTROLLER_DIR','Controllers'));
		define('UPLOAD_DIR', env('UPLOAD_DIR'));
		define('THEME_DIR', env('THEME_DIR'));
		define('THEME_PATH', APP_URL.APP_DIR.DS.THEME_DIR.DS);
		define('MODULE_DIR', env('MODULE_DIR'));
		define('META_DESCRIPTION', 'Cruzer Framework');
		define('META_KEYWORDS', 'Cruzer Framework');
		define('COPYRIGHT', env('APP_NAME'));
		define('COPYRIGHT_URL', 'https://www.cruzersoftwares.com');
		define('ADMIN_DIR', env('ADMIN_DIR'));
		define('ADMIN_PATH', APP_URL.DS.ADMIN_DIR.DS);
		define('ADMIN_LOGIN_REDIRECT', ADMIN_PATH.'dashboard');
		define('ADMIN_LOGOUT_REDIRECT', ADMIN_PATH.'auth');
		require_once APP_DIR.DS."Config".DS."app.php";
	}

	public function enableDebuger( ) {
		//include tracy
		Debugger::enable(Debugger::DETECT, LOG_DIR);
		Debugger::$strictMode = true;
		$this->getDebuger();
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
	}

	public function getDebuger(){
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

