<?php
namespace Limkie;

use Phroute\Phroute\HandlerResolverInterface;

class RouteResolver implements HandlerResolverInterface{
    

    /**
     * The route resolver main method
     *
     * @param string|array $handler
     * @return string|array
     */
    public function resolve($handler){
        if(is_string($handler)){
            $bits   = explode('@',$handler,2);
            $method = (isset($bits[1]))?$bits[1]:'index';
            $nsBits = preg_split('#\.#',$bits[0],null,PREG_SPLIT_NO_EMPTY);

            $endingCls = ["app","Http","Controller"];
            foreach($nsBits as $nsBit){
                $endingCls[] = $nsBit;
            }
            $endingCls = implode("\\",$endingCls);

            if(!class_exists($endingCls)){
                throw new \Exception("Controller {$endingCls} does not exists");
            }
            
            if(method_exists($endingCls,$method)){
                $reflectionMethod = new \ReflectionMethod($endingCls,$method);
                
                if(!$reflectionMethod->isPublic()){
                    throw new \Exception("Method {$method} is not a public method in ".$endingCls);
                }

                if(!$reflectionMethod->isStatic()){
                    $instance = new $endingCls;
                }
                else{
                    $instance = $endingCls;
                }
            }
            else{
                throw new \Exception("Method {$method} does not exists in ".$endingCls);
            }
    
            $handler = [$instance,$method];
        }
        
        return $handler;
    }
}