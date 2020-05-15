<?php 
namespace Limkie\Traits;

use Limkie\Events as EventsManager;

trait Events{

    public function on($eventName,$callBack){
        return EventsManager::onCls(static::class,$eventName,$callBack);
    }

    public function trigger($eventName){
        $args       = func_get_args();
        array_shift($args);
        return call_user_func_array('Limkie\\Events::triggerCls',[static::class,$eventName,$this]+$args);
    }

    public function off($eventName){
        return EventsManager::offCls(static::class,$eventName);
    }
}


?>