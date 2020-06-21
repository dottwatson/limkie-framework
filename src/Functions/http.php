<?php
use Limkie\Http\Request;
use Limkie\Http\Response;
use Limkie\Route;

/**
 * Return The request object
 *
 * @return Request
 */
function request(){
    static $request;

    if(is_null($request)){
        $reques = new Request;
    }

    return $request;
}

/**
 * Make response with passed contents
 *
 * @param string $contents
 * @return Response;
 */
function response(string $contents = null){
    $response = new Response($contents);

    return $response;
}

/**
 * Return an url based on current application url
 *
 * @param string $subPath
 * @param array $queryData
 * @return string
 */
function get_url(string $subPath='',array $queryData=[]){
    $url = Request::baseUrl();

    if($subPath){
        $subPath = preg_replace('#^\/*#','',trim((string)$subPath));
        $url .= $subPath;
    }

    if($queryData){
        $url .= '?'.http_build_query($queryData);
    }

    return $url;
}

/**
 * retrieve full url from named route
 *
 * @param string $name
 * @param array $vars The variables to replace in the route 
 * @param array $queryData Optional query string data
 * @return bool|string The full quailfied url or false on failure
 */
function route(string $name,array $vars=[],array $queryData = []){
    if(Route::hasRoute($name)){
        $subPath = Route::route($name,$vars);
        return get_url($subPath,$queryData);
    }

    return false;
}

/**
 * call a view for rendering
 *
 * @param string $file
 * @param array $params
 * @return string
 */
function view(string $file,array $params= []){
    $resource       = parseResourceName($file);
    $pathRewriteTo  = false;


    if($resource['source_type'] == 'module'){
        $pathTo         = path(getEnv('MODULES_DIR')."/{$resource['source']}/View/{$resource['target']}");
        $pathRewriteTo  = path("app/View/modules/{$resource['source']}/{$resource['target']}");
    }
    elseif($resource['source_type'] == 'package'){
        $pathTo = path("vendor/{$resource['source']}/View/{$resource['target']}");
        $pathRewriteTo  = path("app/View/vendor/{$resource['source']}/{$resource['target']}");
    }
    else{
        $pathTo = path("app/View/{$resource['target']}");
    }

    // dumpe($pathTo,$pathRewriteTo);

    if($pathRewriteTo && is_file($pathRewriteTo)){
        $file = $pathRewriteTo;
    }
    else{
        $file = $pathTo;
    }
    
    return call_user_func_array("App\\View::render",[$file,$params]);
}



/**
 * instantiate a model with optional arguments
 *
 * @param string $name
 * @param array $params
 * @return object
 */
function model(string $name,array $params= []){
    $resource       = parseResourceName($name);

    if($resource['source_type'] == 'module'){
        $realFile   = str_replace('.','/',$resource['target']).'.php';
        $pathTo     = path(getEnv('MODULES_DIR')."/{$resource['source']}/Model/{$realFile}");
        require_once $pathTo;
        
        $namespacedCls = "Modules\\{$resource['source']}\\Model\\";
    }
    else{
        $namespacedCls = "App\\Model\\";
    }

    $namespacedCls .= str_replace('.','\\',$resource['target']);
    

    if(version_compare(PHP_VERSION, '5.6.0', '>=')){
        $instance = new $namespacedCls(...$params);
    } else {
        $reflect  = new ReflectionClass($namespacedCls);
        $instance = $reflect->newInstanceArgs($params);
    }

    return $instance;
}



/**
 * instantiate a controller with optional arguments
 *
 * @param string $name
 * @param array $params
 * @return object
 */
function controller(string $name,array $params= []){
    $resource       = parseResourceName($name);

    if($resource['source_type'] == 'module'){
        $realFile   = str_replace('.','/',$resource['target']).'.php';
        $pathTo     = path(getEnv('MODULES_DIR')."/{$resource['source']}/Http/Controller/{$realFile}");
        require_once $pathTo;
        
        $namespacedCls = "Modules\\{$resource['source']}\\Http\\Controller\\";
    }
    else{
        $namespacedCls = "App\\Http\\Controller\\";
    }

    $namespacedCls .= str_replace('.','\\',$resource['target']);

    if(version_compare(PHP_VERSION, '5.6.0', '>=')){
        $instance = new $namespacedCls($params);
    } else {
        $reflect  = new ReflectionClass($namespacedCls);
        $instance = $reflect->newInstanceArgs($params);
    }

    return $instance;
}




/**
 * Make speficif response type with passed init arguments and contents
 *
 * @param string $name
 * @param array $params
 * @return object
 */
function responseOf(string $name,array $params = []){
    $resource       = parseResourceName($name);

    if($resource['source_type'] == 'module'){
        $realFile   = str_replace('.','/',$resource['target']).'.php';
        $pathTo     = path(getEnv('MODULES_DIR')."/{$resource['source']}/Http/REsponse/{$realFile}");
        require_once $pathTo;
        
        $namespacedCls = "Modules\\{$resource['source']}\\Http\\Response\\";
    }
    else{
        $namespacedCls = "App\\Http\\Response\\";
    }

    $namespacedCls .= str_replace('.','\\',$resource['target']);
    

    if(version_compare(PHP_VERSION, '5.6.0', '>=')){
        $instance = new $namespacedCls($params);
    } else {
        $reflect  = new ReflectionClass($namespacedCls);
        $instance = $reflect->newInstanceArgs($params);
    }

    return $instance;
}

