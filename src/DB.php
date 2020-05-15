<?php
namespace Limkie;

class DB{

    protected static $connections = [];
    protected static $handlers = [];    

    public function __construct($name,$config=[]){
        if(isset(self::$connections[$name])){
            return self::$connections[$name];
        }
    }


    public static function get($name=null){
        $handler = null;

        if(is_null($name)){
            $name = getenv('DATABASE_CONNECTION_NAME');
        }
        
        if(!isset(self::$handlers[$name])){
            if(!isset(self::$connections[$name])){
                throw new \Exception("Database connection {$name} does not exists");
                return false;
            }
            else{
                $config                 = self::$connections[$name];
                $databaseHandler        = config("adapter.database.{$config['type']}");

                self::$handlers[$name]  = new $databaseHandler($config);
                $handler = self::$handlers[$name];
            }
        }
        else{
            $handler = self::$handlers[$name];
        }
    
        return $handler;
    }

    public static function register($name,$config=[]){
        if(!isset(self::$connections[$name])){
            self::$connections[$name] = $config;
            return true;
        }
        else{
            throw new \Exception("Database connection {$name} already defined");
            return false;
        }
    }

    public static function init(){
        $connections = config('database');
        foreach($connections as $connectionName=>$connectionInfo){
            self::register($connectionName,$connectionInfo);
        }
    }
}