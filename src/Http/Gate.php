<?php
namespace Limkie\Http;

use Limkie\Traits\Inseminator;

abstract class Gate{
    use Inseminator;
    
    protected $app;
    protected $request;
    protected $route;


    protected $alias;


    public function __construct(){
        $this->inseminate();
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
