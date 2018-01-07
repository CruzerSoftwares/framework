<?php

namespace Cruzer\Framework\Core;

class Handler {

	public function __construct( array $loadedModules=array(), array $route = array() ){

	}

	public function serve( $routeInfo ) {
        list( $controller, $action )  = explode('@', $routeInfo[1]);
        $params                       = $routeInfo[2];
        $controllerStr = "\\App\\Controllers\\".$controller;
        $controllerObj = new $controllerStr();
        $controllerObj->$action();
	}

    public function hanldeError( $error_type, $msg )
	{
		if( ERROR_LEVEL == 'development' ){
            trigger_error($msg, E_USER_ERROR);
            // echo $msg;
        }
        header("HTTP/1.0 404 Not Found");
        exit;
	}

}

