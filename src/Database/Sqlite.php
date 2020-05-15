<?php

namespace Limkie\Database;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;



class Sqlite{
    protected $config;
    protected $connection;
    protected $queryBuilder;
    protected $instance;    


    public function __construct($config){
        $this->config       = $config;
        $this->connection   = new Connection('sqlite',$config);
        $this->queryBuilder = new QueryBuilderHandler($this->connection); 
    
        return $this->queryBuilder;        
    }


    public function __call($name,$args){
        return call_user_func_array([$this->queryBuilder,$name],$args);
    }


    public function __get($name){
        return $this->queryBuilder->{$name};
    }

    public function __set($name,$value){
        return $this->queryBuilder->{$name} = $value;
    }

}