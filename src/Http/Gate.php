<?php
namespace Limkie\Http;

use Limkie\Traits\Globalizer;

abstract class Gate{
    use Globalizer;
    
    protected $app;
    protected $request;
    protected $route;


    protected $alias;


    public function __construct(){
        $this->globalize();
    }

    abstract public function handle();

    /**
     * Use a specific response using its relative namespace with dot Notation
     * 
     * e.g. withResponse('Bar.Foo') requires app/Http/Response/Bar/Foo.php
     *
     * @param string $responseCls
     * @return object The object response
     */
    public function withResponse(string $responseCls){
        $bits = preg_split('#\.#',$responseCls,null,PREG_SPLIT_NO_EMPTY);

        $requestedCls = "App\\Http\\Response\\".implode("\\",$bits);

        return (new $requestedCls);
    }
}
