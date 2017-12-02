<?php 

namespace Controllers;

class BaseController{
    protected $load;
    protected $container = array();

    public function __construct( ) {
        $this->load = $this;

        require_once "Handler.php";
        global $loadedModules;

        $handler = new Handler( $loadedModules, array());
        $modelName = $handler->checkModuleLoaded( );
        if( in_array($modelName, $loadedModules)){
            $modelPath = $handler->getLoadedBasePath( );
            include_once ($modelPath.'Config/app.php');
        }
    }

    public function model( $model = null){
        if( empty( $model )){
            $trace = debug_backtrace();
            $model = substr( $trace[0]['class'],0,-10);
            if( isset($trace[1])){
                $model = substr( $trace[1]['class'],0,-10);
            }
            //return debug_backtrace();
        } 
        //check if model class exists actually
        require_once "Handler.php";
        global $loadedModules;

        $handler = new Handler( $loadedModules, array());
        $modelFolder = $handler->getLoadedBasePath( );

        if($modelFolder=='') $modelFolder = APPNAME.'/Models/';
        else $modelFolder = $modelFolder.'/Models/';

        if( file_exists( $modelFolder.$model.'.php')){
            require_once $modelFolder.$model.'.php';
            return new $model;
        } else{
            $this->handleError('missing_model','Model class '.$model.' not found in '.$modelFolder.$model.'.php');
        }
        
    }
    
    public function _view( $view = null){
        $trace = debug_backtrace(); //_pr($trace,1);
        if( empty( $view )){
            if( isset($trace[1])){
                $view = $trace[1]['function'];
                $viewFolder = substr($trace[1]['class'],0,-10);
                $mainObjLayOutTheme = (array)$trace[1]['object'];
            } else{
                $view = $trace[0]['function'];
                $viewFolder = substr($trace[0]['class'],0,-10);
                $mainObjLayOutTheme = (array)$trace[0]['object'];
            }
       } else{
           if( isset($trace[1])){
                $viewFolder = substr($trace[1]['class'],0,-10);
                $mainObjLayOutTheme = (array)$trace[1]['object'];
            } else{
                $viewFolder = substr($trace[0]['class'],0,-10);
                $mainObjLayOutTheme = (array)$trace[0]['object'];
            }
       }
        //check if view class exists actually
        require_once "Handler.php";
        global $loadedModules;

        $handler = new Handler( $loadedModules, array());
        $viewFolderReal = $handler->getLoadedBasePath( );
        if($viewFolderReal=='') $viewFolderReal = APPNAME.'/Views/';
        else $viewFolderReal = $viewFolderReal.'/Views/';

        if( file_exists( $viewFolderReal.$viewFolder.'/'.$view.'.php')){
            foreach( $this->container as $key=>$val ){
                $$key = $val;
            } //_pr($mainObjLayOutTheme);exit;
             $oldContent = ob_get_clean();
             ob_start();
             $pageTitle1 = empty($mainObjLayOutTheme['pageTitle']) ? '' : $mainObjLayOutTheme['pageTitle'];
             include ( $viewFolderReal.$viewFolder.'/'.$view.'.php');
             $bodyContent = ob_get_clean();
             //now we need to load this content inside our theme's layout file
             //theme and layout may be defined in place 
             //1. calling function 
             //2. calling function's controller 
             //3. calling functions' controller's parent class and so on...
             $loadTheme = empty($mainObjLayOutTheme['theme']) ? 'default' : $mainObjLayOutTheme['theme'];
             $loadLayOut = empty($mainObjLayOutTheme['layout']) ? 'default' : $mainObjLayOutTheme['layout'];
             ///now load template and themes
             ob_start();
             include ( APPNAME.'/Themes/'.$loadTheme.'/'.$loadLayOut.'.php');
             $themeNlayOut = ob_get_clean();
             echo $themeNlayOut;exit;
        } else{
            $this->handleError('missing_view','view class '.$view.' not found in '.$viewFolderReal.$viewFolder.'/'.$view.'.php');
        }
    }
    
    public function handleError( $error_type, $msg )
    {
        ob_start();
        if( ERROR_LEVEL == 'development' ){
            $bodyContent = $msg;
        }
        $loadTheme = empty($mainObjLayOutTheme['theme']) ? 'default' : $mainObjLayOutTheme['theme'];
        include ( APPNAME.'/Themes/'.$loadTheme.'/404.php');
        $themeNlayOut = ob_get_clean();
        echo $themeNlayOut;
        header("HTTP/1.0 404 Not Found");
        exit;
    }

    public function load( ){
        //$trace = debug_backtrace(); _pr($trace);
    }
    
    public function set( $var, $data=null){
        $this->container[$var] = $data;
    }
    
}