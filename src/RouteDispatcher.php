<?php

namespace Limkie;

use Limkie\Http\Request;
use  Phroute\Phroute\Dispatcher;

class RouteDispatcher extends Dispatcher{

    protected $routeInfo =  [];
    
    /**
     * Dispatch a route for the given HTTP Method / URI.
     *
     * @param $httpMethod
     * @param $uri
     * @return mixed|null
     */
    public function dispatch($httpMethod, $uri)
    {
        $dispatchRoute = new \ReflectionMethod($this,'dispatchRoute');
        $dispatchRoute->setAccessible(true);

        $parseFilters = new \ReflectionMethod($this,'parseFilters');
        $parseFilters->setAccessible(true);

        $dispatchFilters = new \ReflectionMethod($this,'dispatchFilters');
        $dispatchFilters->setAccessible(true);

       
        list($handler, $filters, $vars) = $dispatchRoute->invokeArgs($this,[$httpMethod, trim($uri, '/')]);

        $this->routeInfo = [
            'uri' => trim($uri,'/'),
            'url' => Request::url(),
            'vars' => $vars
        ];

        list($beforeFilter, $afterFilter) = $parseFilters->invokeArgs($this,[$filters]);

        if(($response = $dispatchFilters->invokeArgs($this,[$beforeFilter])) !== null)
        {
            return $response;
        }
        
        $resolvedHandler = $this->handlerResolver->resolve($handler);
        
        $response = call_user_func_array($resolvedHandler, $vars);

        return $dispatchFilters->invokeArgs($this,[$afterFilter, $response]);
    }

    public function getRouteInfo(){
        return $this->routeInfo;
    }

}