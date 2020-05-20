<?php
namespace Limkie;

use Phroute\Phroute\RouteCollector;
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

       
        $routeUrl = preg_replace(
            '#^'.preg_quote(Request::baseUrl(PHP_URL_PATH),'#').'#',
            '',
            Request::url(PHP_URL_PATH)
        );


        try{
            $requestMethod = (app()->isCommandLine())?'GET':$_SERVER['REQUEST_METHOD'];
            $response = self::$dispatcher->dispatch($requestMethod,$routeUrl);
        }
        catch(\Exception $e){
            if($e instanceof \Phroute\Phroute\Exception\HttpRouteNotFoundException){
                $contents404 = (new Storage('public'))->contentFile('404.html');
                $response = response($contents404)->setStatus(404);
            }
            else{
                $response = $e->getMessage();
            }
        }
        
        echo (string)$response;

        exit;
    }


    public static function getDispatcher(){
        return self::$dispatcher;
    }
}