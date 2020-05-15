<?php

namespace Limkie\Http;

use Rakit\Validation\Validator;
use Limkie\Http\Request;


class Controller{

    /**
     * The default response class , declared in dot notation
     * The name is intended as namespace, with starts from app/Http/response
     * e.g: for app/Http/Response/Bar/Foo  use 'bar.foo'
     *
     * @var string
     */
    protected $defaultResponse;

    public function __construct(){
        $this->validator    = new Validator();
        $this->request      = new Request;
        $this->response     = ($this->defaultResponse)
            ?$this->withResponse($this->defaultResponse)
            :response();

        $this->app = app();
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


