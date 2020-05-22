<?php
namespace Limkie;

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Limkie\RouteDispatcher;
use Limkie\RouteResolver;
use Limkie\Http\Request;
use Limkie\Storage;


class Route{
    
    protected static $router;
    protected static $dispatcher;


    public static function __callStatic($name, $arguments){
        if(!self::$router){
            self::$router = new RouteCollector;
        }
    
        return call_user_func_array([self::$router,$name],$arguments);
    }

    public static function dispatch(){
        if(!self::$router){
            self::$router = new RouteCollector;
        }

        $resolver = new RouteResolver();
        if(!self::$dispatcher){
            self::$dispatcher = new RouteDispatcher(self::$router->getData(),$resolver);
        }

        $publicStorage = new Storage(__APP_PATH__.'/public');
       
        $routeUrl = preg_replace(
            '#^'.preg_quote(Request::baseUrl(PHP_URL_PATH),'#').'#',
            '',
            Request::url(PHP_URL_PATH)
        );

        $requestMethod = (app()->isCommandLine())?'GET':$_SERVER['REQUEST_METHOD'];
        $response = self::$dispatcher->dispatch($requestMethod,$routeUrl);


        if($response instanceof HttpRouteNotFoundException){
            $contents404 = $publicStorage->contentFile('404.html');
            $response = response($contents404)->setStatus(404);
        }
        elseif($response instanceof \Exception){
            $response = response($response->getMessage());
        }
        
        echo (string)$response;

        exit;
    }


    public static function getDispatcher(){
        return self::$dispatcher;
    }
}