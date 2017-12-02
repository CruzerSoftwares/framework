<?php

class Handler {

    public $request_params;
	public $route;

	public function __construct( $loadedModules=array(), $route = array() ){
		$this->loadedModules = $loadedModules;
		$this->route         = $route;
	}

	//process urls
	public function serve( ) {
        $this->request_params = explode( "/", $_SERVER['REQUEST_URI'] );

		if( end( $this->request_params) == '' ) {
            array_pop( $this->request_params );
        }
        
        if( isset($this->request_params) && isset($this->request_params[1]) && ROOT == $this->request_params[1] ){
            unset($this->request_params[1]);
            $this->request_params = array_values($this->request_params);
        }
        
		if( count( $this->request_params ) >1 && !empty($this->request_params[1] )){
            if( $this->checkIfModule( $this->request_params[1] ) ){
				$moduleFolder = $this->loadedModules[$this->request_params[1]];

                require_once MODULE_PATH."/".$moduleFolder."/Config/routes.php";
                $this->route         = $route;

		        if( count( $this->request_params ) >2 && !empty($this->request_params[2] )){
		        	$class = $this->cleanClassName($this->request_params[2]);
		        	$this->loadClassFile( $class, MODULE_PATH.'/'.$moduleFolder."/");
		        }else{
		        	$this->loadDefaultClassFile(MODULE_PATH.'/'.$moduleFolder."/");
		        }
			} else{
				$class = $this->cleanClassName($this->request_params[1]);
		        $this->loadClassFile( $class, APPNAME.'/');
			}
		} else{
			$this->loadDefaultClassFile(APPNAME.'/');
		}
	}

	public function checkIfModule( $urlToCheck )
    {
        if( in_array( $urlToCheck, array_keys($this->loadedModules))){
            return true;
        }   
        return false;
    }

    public function getLoadedBasePath( )
    {
        $request_params = explode( "/", $_SERVER['REQUEST_URI'] );

        if( end( $request_params) == '' ) {
            array_pop( $request_params );
        }

        if( isset($request_params) && isset($request_params[1]) && ROOT == $request_params[1] ){
            unset($request_params[1]);
            $request_params = array_values($request_params);
        }

        if( count( $request_params ) >1 && !empty($request_params[1] )){
            if( $this->checkIfModule( $request_params[1] ) ){
                $moduleFolder = $this->loadedModules[$request_params[1]];

                if( count( $request_params ) >2 && !empty($request_params[2] )){
                    $class = $this->cleanClassName($request_params[2]);
                    return MODULE_PATH.'/'.$moduleFolder."/";
                }else{
                    return MODULE_PATH.'/'.$moduleFolder."/";
                }
            } else{
                $class = $this->cleanClassName($request_params[1]);
                return APPNAME.'/';
            }
        } else{
            return APPNAME.'/';
        }
    }

    public function checkModuleLoaded( )
	{
		$request_params = explode( "/", $_SERVER['REQUEST_URI'] );

        if( end( $request_params) == '' ) {
            array_pop( $request_params );
        }

        if( isset($request_params) && isset($request_params[1]) && ROOT == $request_params[1] ){            
            unset($request_params[1]);
            $request_params = array_values($request_params);
        }
        
        if( count( $request_params ) >1 && !empty($request_params[1] )){
            if( $this->checkIfModule( $request_params[1] ) ){
                return $moduleFolder = $this->loadedModules[$request_params[1]];
            }
        }
	}

    public function checkQueryStringLoaded($urlToProcess='')
    {
        //check if url contains ? or & as get paramas if so then clean class name
        $que_pos = strpos($urlToProcess ,'?');
        $amp_pos = strpos($urlToProcess ,'&');
        $min     = 0;

        if( $que_pos && $amp_pos ){
           $min = ( $que_pos < $amp_pos ? $que_pos : $amp_pos );
        } elseif( $que_pos && !$amp_pos ){
           $min = $que_pos;
        } elseif( !$que_pos && $amp_pos ){
           $min = $amp_pos;
        }
        return $min;

    }

	public function cleanClassName( $urlToProcess, $format = true )
    {
        $controller = '';

        $class = $urlToProcess;
        $min = $this->checkQueryStringLoaded($urlToProcess);

        if( $min ) {
            $class = substr($urlToProcess,0,$min).$controller;
        }

        if($format === true ) {
            $class = ucfirst($class).'Controller';
        }
       
        return $class;
    }

	public function checkMethodAvailable( $className, array $method_from = array(), $index=0, $methodName = null )
    {
        $app     = new $className;

        if( $methodName == null && is_array( $method_from ) && count( $method_from ) ){
            $method  = $method_from[$index];

            $method_params = array();

            foreach ($method_from as $key => $value) {
                if( $key > $index ){
                    $method_params[] =  $value;
                }   
            }

            $min = $this->checkQueryStringLoaded($method);

            if( $min ) {
                $method = substr($method,0,$min);
            }
        } else{
            $method = $methodName;
            $method_params = array();

            foreach ($this->request_params as $key => $value) {
                if( $key > 0 ){
                    $method_params[] =  $value;
                }   
            }
        }

        if(!method_exists($app, $method)){
            return [ 'err' => true, 'method' => $method, 'params' => $method_params ];
        }

        return [ 'err' => false, 'method' => $method, 'params' => $method_params ];
    }

    public function loadMethod( $className, $classPath, array $method_from = array(), $index=0, $methodName = null )
    {
        $app     = new $className;
        $checkMethod = $this->checkMethodAvailable( $className, $method_from, $index, $methodName );

        if( $checkMethod['err'] === true ){
            $this->hanldeError('missing_method',"Method ".$checkMethod['method']." not found in class ".$className);
        }

        $this->checkAuth( $className, $classPath );
        $this->checkRole( $className, $classPath );

        call_user_func_array(array($app,$checkMethod['method']), $checkMethod['params']);
    }

    public function checkAuth( $className, $classPath )
    {
        if( $className == 'AuthController' ) {return;}

        $reflectionObj = new ReflectionClass($className);
        // $constructor = $reflectionObj->getConstructor();
        // $constructor = $reflectionObj->getConstants();
        // $constructor = $reflectionObj->getDefaultProperties();
        // $constructor = $reflectionObj->getMethod($method);
        // $constructor = $reflectionObj->getMethod($method)->getParameters();
        // $constructor = $reflectionObj->getMethods();
        // $constructor = $reflectionObj->getProperties();
        // $constructor = $reflectionObj->getProperty('auth_level');
        // $constructor = $reflectionObj->hasProperty('auth_level');
        // $constructor = $reflectionObj->hasConstant('auth_level');
        // $constructor = $reflectionObj->hasMethod($method);
        // $constructor = $reflectionObj->hasConstant($method);
        // $constructor = $reflectionObj->getParentClass();
        // _pr($constructor);
        // _pr(get_defined_vars());
        $class_vars = get_class_vars($className);

        if( isset($class_vars['auth']) && $class_vars['auth']=== true ){
            if( _auth( true ) !== true ){
                _redirect( SITE_URL.strtolower(str_replace(MODULE_PATH,'',$classPath)).'auth' );
            }
        }
    }

    public function checkRole( $className, $classPath )
    {
        if( $className == 'AuthController' ) {return;}

        $reflectionObj = new ReflectionClass($className);
        $class_vars = get_class_vars($className);
        
        if( isset($class_vars['auth']) && isset($class_vars['auth_level']) && $class_vars['auth']=== true ){
            if( is_array($class_vars['auth_level'])){
                $roles = $class_vars['auth_level'];
            } else{
                $roles = explode(',',$class_vars['auth_level']);
            }
            
            if( !in_array( _role( true ) , $roles )){
                _setFlash('warning','You do not have permission to access this resource!',true);
                _redirect( SITE_URL.strtolower(str_replace(MODULE_PATH,'',$classPath)).'dashboard' );
            }
        }
    }

    public function loadRouteClassFile( $className, $method )
    {
        $classPath = $this->getLoadedBasePath();
        if( file_exists( $classPath."Controllers/".$className.".php" ) ) {
            include_once $classPath."Controllers/".$className.".php";
            
            if( !empty($method)){//method name exists
                $this->loadMethod( $className, $classPath, array(),null, $method );
            } else{
                $this->checkAuth( $className, $classPath );
                $this->checkRole( $className, $classPath );
                $app = new $className;

                $method_params = array();
                foreach ($this->request_params as $key => $value) {
                    if( $key > 0 ){
                        $method_params[] =  $value;
                    }   
                }
                call_user_func_array(array($app,'index'), $method_params);
            }
        } 
    }

    public function loadClassFile( $className, $classPath )
	{
		if( file_exists( $classPath."Controllers/".$className.".php" ) ) {
            include_once $classPath."Controllers/".$className.".php";
            // $indexOfMethod = 3;
            $indexOfMethod = 2;
            if( $this->checkModuleLoaded () ){
                $indexOfMethod = 3;
            }
            
            
            if( count( $this->request_params ) >= $indexOfMethod && !empty($this->request_params[$indexOfMethod])){//method name exists
                $checkMethod = $this->checkMethodAvailable( $className, $this->request_params, $indexOfMethod );
                
                if($checkMethod['err'] === true ){
                    if( count($this->request_params)){
                         foreach ($this->request_params as $key => $param) {
                             if($param!='') $paramsAr[$key] = $this->cleanClassName($param, false);
                         }
                         
                         if( isset($paramsAr) && count($paramsAr)){
                             $paramStr = implode('/',$paramsAr);
                         }

                         if( isset($this->route) && isset($paramStr) && isset( $this->route[$paramStr])){
                            $routeData = explode('@',$this->route[$paramStr]);
                            $this->loadRouteClassFile($routeData[0],$routeData[1]);
                         } 

                         foreach ($paramsAr as $key => $params) {
                             if( isset($params) && isset($this->route[$params.'/(:any)'])){
                                if(in_array($params, $this->request_params)){
                                    unset($this->request_params[array_search($params, $this->request_params)]);
                                }
                                $routeData = explode('@',$this->route[$params.'/(:any)']);
                                $this->loadRouteClassFile($routeData[0],$routeData[1]);
                             }
                         }

                         if( isset($this->route) && isset($this->route['(:any)'])){
                            $routeData = explode('@',$this->route['(:any)']);
                            $this->loadRouteClassFile($routeData[0],$routeData[1]);
                         }
                     }
                    $this->hanldeError('missing_method',"Method ".$checkMethod['method']." not found in class ".$className);
                }

                $this->loadMethod( $className, $classPath, $this->request_params, $indexOfMethod );
            } else{
                $this->checkAuth( $className, $classPath );
                $this->checkRole( $className, $classPath );
                $app = new $className;

                $method_params = array();
                foreach ($this->request_params as $key => $value) {
                    if( $key > 1 ){
                        $method_params[] =  $value;
                    }   
                }
                call_user_func_array(array($app,'index'), $method_params);
            }
        } else {
             if( count($this->request_params)){
                 foreach ($this->request_params as $key => $param) {
                     if($param!='') $paramsAr[$key] = $this->cleanClassName($param, false);
                 }
                 
                 if( isset($paramsAr) && count($paramsAr)){
                     $paramStr = implode('/',$paramsAr);
                 }

                 if( isset($this->route) && isset($paramStr) && isset( $this->route[$paramStr])){
                    $routeData = explode('@',$this->route[$paramStr]);
                    $this->loadRouteClassFile($routeData[0],$routeData[1]);
                 } 

                 foreach ($paramsAr as $key => $params) {
                     if( isset($params) && isset($this->route[$params.'/(:any)'])){
                        if(in_array($params, $this->request_params)){
                            unset($this->request_params[array_search($params, $this->request_params)]);
                        }
                        $routeData = explode('@',$this->route[$params.'/(:any)']);
                        $this->loadRouteClassFile($routeData[0],$routeData[1]);
                     }
                 }

                 if( isset($this->route) && isset($this->route['(:any)'])){
                    $routeData = explode('@',$this->route['(:any)']);
                    $this->loadRouteClassFile($routeData[0],$routeData[1]);
                 }
             }
            $this->hanldeError('missing_controller',"Missing file ".$classPath."Controllers/".$className.".php");
        }
	}

    //load default controller's action
	public function loadDefaultClassFile( $classPath )
    {
        $request_params = explode('/',$this->route['default_controller']);
        $class = ucfirst($request_params[0])."Controller";

        if( file_exists( ROOT_DIR.'/'.$classPath."Controllers/".$class.".php" ) ) { 
            include_once ROOT_DIR.'/'.$classPath."Controllers/".$class.".php";
            
            if( count( $request_params )>1 && !empty($request_params[1])){
                $this->loadMethod( $class, $classPath, $request_params,1 );
            } else{
                $this->checkAuth( $class, $classPath );
                $this->checkRole( $class, $classPath );
                $app = new $class;
                $app->index();
            }
        } else {
            $this->hanldeError('missing_controller',"Missing file ".ucfirst($this->request_params[0])."Controller.php");
        }
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

