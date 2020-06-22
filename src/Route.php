<?php
namespace Limkie;

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Limkie\RouteDispatcher;
use Limkie\RouteResolver;
use Limkie\Http\Request;
use Limkie\Storage;
use Limkie\Image;


class Route{
    
    /**
     * The internal router
     *
     * @var RouteCollector
     */
    protected static $router;

    /**
     * The internal dispatcher
     *
     * @var RouteDispatcher
     */
    protected static $dispatcher;


    /**
     * Bridge on real router
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments){
        if(!self::$router){
            self::$router = new RouteCollector;
        }

        return call_user_func_array([self::$router,$name],$arguments);
    }

    /**
     * Create  gate from a closure
     *
     * @param string $alias
     * @param \Closure $closure
     * @return void
     */
    public static function gate(string $alias,\Closure $closure){
        $router = self::$router;
        $router->filter($alias,$closure);
    }

    /**
     * Dispatch current request
     *
     * @return void
     */
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
        elseif($response instanceof Image){
            return $response->toResponseString();
        }
        elseif($response instanceof \Exception){
            $response = response($response->getMessage());
        }
        
        echo (string)$response;

        exit;
    }

    /**
     * Returns current Dispacher
     *
     * @return RouteDispatcher
     */
    public static function getDispatcher(){
        return self::$dispatcher;
    }
}