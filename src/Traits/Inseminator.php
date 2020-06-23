<?php 

namespace Limkie\Traits;

use Limkie\App;
use Limkie\Http\Request;
use Limkie\Route;
use Limkie\DataContainer;
use ReflectionClass;

trait Inseminator{

    /**
     * Inspect current object and ,based on protected variables names, 
     * assign app or request or route
     * 
     * Current data will be inseminated
     * 
     * app: the app
     * request: current instantiated object request
     * route: the resolved route informations
     * session: the current session
     * 
     * @return void
     */
    protected function inseminate(){
        $ref = new ReflectionClass($this);
        $atts = $ref->getDefaultProperties();

        if(isset($atts['app'])){
            $this->app = App::getInstance();
        }

        if(isset($atts['request'])){
            $this->request  = new Request;
        }

        if(isset($atts['route'])){
            $routerData = Route::getDispatcher()->getRouteInfo();
            $this->route = new DataContainer($routerData);
        }

        if(isset($atts['session'])){
            $this->session = app()->session;
        }


    }

}

?>