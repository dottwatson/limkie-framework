<?php

namespace Limkie\Http;

use Limkie\Traits\Inseminator;
use Rakit\Validation\Validator;


class Controller{
    use Inseminator;

    /**
     * The default response class , declared in dot notation
     * The name is intended as namespace, with starts from app/Http/response
     * e.g: for app/Http/Response/Bar/Foo  use 'bar.foo'
     *
     * @var string
     */
    protected $defaultResponse;

    protected $app;
    protected $route;


    public function __construct(){
        $this->inseminate();

        $this->validator    = new Validator();
        $this->response     = ($this->defaultResponse)
            ?$this->withResponse($this->defaultResponse)
            :response();

    }

    
    /**
     * Use a specific response using its relative namespace with dot Notation
     * 
     * e.g. withResponse('bar.foo') requires app/Http/Response/Bar/Foo.php
     *
     * @param string $responseCls
     * @return object The object response
     */
    public function withResponse(string $responseCls){
        return responseOf($responseCls);
    }
}


