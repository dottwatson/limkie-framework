<?php

namespace Limkie\Console;

use Exception;
use Garden\Cli\Cli;
use Garden\Cli\TaskLogger;
use Limkie\Console\StreamLogger;

class Console{
    protected static $cli;
    protected static $log;
    protected static $commands = [];

    protected $tmpFile;


    protected static $instance;

    public function __construct(){
        if(self::$instance){
            return self::$instance;
        }


        if(!defined('STDOUT')){
            $stdOut = fopen('php://temp','wr+');
            define('STDOUT',$stdOut);
        }

        if(!defined('STDERR')){
            $stdErr = fopen('php://temp','wr+');
            define('STDERR',$stdErr);
        }

        self::$instance = $this;
    }

    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new static;
        }

        return self::$instance;
    }

    public static function formatMessageConsole($message=''){
        if(!self::$log){
            $streamLogger = new StreamLogger(STDOUT);
            $streamLogger->setLineFormat('[{time}] - {level}- {message}');
            $streamLogger->setLevelFormat('strtoupper');


            self::$log = new TaskLogger($streamLogger);
        }

        $message = (!is_string($message))
            ?var_export($message,true)
            :$message;

        return $message;
    }
    
    public static function debug($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->debug($message);
    }

    public static function info($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->info($message);
    }

    public static function notice($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->notice($message);
    }

    public static function warning($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->warning($message);
    }

    public static function error($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->error($message);
    }

    public static function critical($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->critical($message);
    }

    public static function alert($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->alert($message);
    }

    public static function emergency($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->emergency($message);
    }

    public static function success($message = ''){
        $message = self::formatMessageConsole($message);
        self::$log->log('success',$message);
    }


    public function registerCommand($commandName = '',$commandCls){
        $commandName = trim($commandName);
        if(array_key_exists($commandName,self::$commands)){
            throw new Exception("Command {$commandName} already defined");
            return false;
        }
        
        if(class_exists($commandCls)){
            
            $reflectionClass  = new \ReflectionClass($commandCls);
            $reflectionProps  = $reflectionClass->getDefaultProperties();

            $commandArguments = (isset($reflectionProps['arguments']) && is_array($reflectionProps['arguments']))
                ?$reflectionProps['arguments']
                :[];
            
            $commandOptions = (isset($reflectionProps['options']) && is_array($reflectionProps['options']))
                ?$reflectionProps['options']
                :[];

            $description = (isset($reflectionProps['description']))
                ?(string)$reflectionProps['description']
                :'';

            self::$cli
                ->command($commandName)
                ->description($description);
            
            foreach($commandArguments as $argName=>$argInfo){
                array_unshift($argInfo,$argName);
                call_user_func_array([self::$cli,'arg'],$argInfo);
            }

            foreach($commandOptions as $optName=>$optionInfo){
                array_unshift($optionInfo,$optName);
                call_user_func_array([self::$cli,'opt'],$optionInfo);
            }    
            
            self::$commands[$commandName] = $commandCls;

            return $this;
        }
        else{
            throw new Exception("Command class {$commandCls} not found");
            return false;
        }
    }
    
    
    public function loadCommands(){
        self::$cli          = Cli::create();
        $definedCommands    = config('console.commands',[]);

        foreach($definedCommands as $commandName=>$commandCls){
            $this->registerCommand($commandName,$commandCls);
        }
    }

    public function listen($argv){
        $commandInfo        = self::$cli->parse($argv)->jsonSerialize();
        $commandCls         = self::$commands[ $commandInfo['command'] ];
        $commandInstance    = new $commandCls(
            $commandInfo['args'],
            $commandInfo['opts'],
            $commandInfo['meta']
        );

        return $commandInstance->handle();
    }
}