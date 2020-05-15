<?php
namespace Limkie;

use Limkie\DebugWatcher;
use Limkie\Config;
use Limkie\DB;
use Limkie\Storage;
use Limkie\Console\Console;
use Limkie\Traits\Events;
use Limkie\Debugger as InternalDebugger;
use Dotenv\Dotenv;

class App{
    use Events;

    protected static $components = [];
    protected static $instance;

    public $config;
    public $watcher;
    public $console;
    public $session;

    protected $mode;


    public function __construct(){
        if(self::$instance){
            return self::$instance;
        }

        //load env parameters
        $dotenv     = Dotenv::createImmutable(__APP_PATH__);

        $dotenv->load();

        $dotenv->required([
            'ENVIRONMENT',
            'LOGS_DIR','CACHE_DIR','TMP_DIR',
            'SESSION_NAME', 
            'SECRET_KEY',
            'DATABASE_CONNECTION_NAME'
        ]);

        $dotenv->required('ENVIRONMENT')->allowedValues(['PRODUCTION','STAGING','DEVELOPMENT']);
        $dotenv->required('LOGS_DIR')->notEmpty();
        $dotenv->required('CACHE_DIR')->notEmpty();
        $dotenv->required('TMP_DIR')->notEmpty();
        $dotenv->required('SESSION_NAME')->notEmpty();
        
        //init session
        $sessionKey = getEnv('SESSION_NAME');
        if(!array_key_exists($sessionKey,$_SESSION)){
            $_SESSION[$sessionKey] = [];
        }
        
        if(!array_key_exists("__tracker",$_SESSION[$sessionKey])){
            $_SESSION[$sessionKey]["__tracker"] = [];
        }

        //set uniqid usefull for csrf and other porpouses
        if(!array_key_exists("__uniqid",$_SESSION[$sessionKey])){
            $_SESSION[$sessionKey]["__uniqid"] = encrypt(uniqid());
        }

        $this->session = new DataContainer();
        $this->session->setReference($_SESSION[$sessionKey]);

        //load autoloader for custom classes discovery
        $this->setAutoload();

        //set configuration access from app
        $this->config   = Config::getInstance();

        //set console access from app
        $this->console  = Console::getInstance();

        //init watcher (the internal data logger)
        $this->watcher  = DebugWatcher::getInstance();
        $this->watcher->register('config',$this->config);


        //load class adapters
        // dumpe($this->config->get('adapter',[]));
        
        foreach($this->config->get('adapter',[]) as $coreItem=>$overrideCls){
            switch($coreItem){
                case 'model':
                case 'view':
                case 'storage':
                    $clsName = 'App\\'.ucfirst($coreItem);
                break;
                case 'controller':
                case 'response':
                    $clsName = 'App\\Http\\'.ucfirst($coreItem);
                break;
                default:
                    continue 2;
                break;
            }

            if(class_exists($overrideCls)){
                $aliasCls = $overrideCls;
                $aliased = class_alias($aliasCls,$clsName,false);
            }
        }

        //initialize database
        DB::init();

        //init tracy debugger
        InternalDebugger::init();

        self::$instance = $this;
    }

    /**
     * Singleton
     *
     * @param string $component
     * @return self
     */
    public static function getInstance($component=null){
        $instance  = self::$instance;

        if($component){
            return (array_key_exists($component,self::$components))
                ?self::$components[$component]
                :null;
        }

        return $instance;
    }

    /**
     * Initialize the app
     *
     * @return void
     */
    public function init(){
        $this->mode = PHP_SAPI;

        if($this->isMaintenanceActive()){
            $maintenanceMessage = ($this->isCommandLine())
                ?"Under Maintenance\n"
                :(new Storage('public'))->contentFile('maintenance.html');

            return response($maintenanceMessage);
        }

        $this->loadFilter();

        $this->loadRoutes();

        $this->console->loadCommands();

        
        $this->trigger('init');
    }

    public function getMode(){
        return $this->mode;
    }

    /**
     * Load routes based on current environment
     *
     * @return void
     */
    public function loadRoutes(){
        $env    = getEnv('ENVIRONMENT');
        $files  = glob(__APP_PATH__."/app/Http/Route/*.php"); 
        foreach($files as $routeFile){
            $allowed = $this->config->get("env.{$env}.routes",[]);
            if(in_array(basename($routeFile,'.php'),$allowed)){
                include_once $routeFile;
            }
        }
    }

 
    /**
     * Load the router Filters
     *
     * @return void
     */
    public function loadFilter(){
        //load Filter
        $files = glob(__APP_PATH__."/app/Http/Filter/*.php");
        foreach($files as $filterFile){
            require $filterFile;
        }

        //list all classes under filter namespace
        $classes = get_declared_classes();
        foreach($classes as $className){
            $lowerClsName = strtolower($className);
            if(strpos($lowerClsName,'app\\http\\filter\\') === 0){
                $reflectionCLs  = new \ReflectionClass($className);
                $properties     = $reflectionCLs->getDefaultProperties();
                $alias = (isset($properties['alias']))
                    ?trim((string)$properties['alias'])
                    :null;

                if($alias){
                    Route::filter($alias,function() use($className){
                        $instance = new $className;
                        $response = response();
                        
                        $nextStep = $instance->handle();

                        if($nextStep instanceOf \Limkie\Http\Response){
                            echo (string)$nextStep;
                            exit;
                        }

                        $continue = ($nextStep === true)?null:false;

                        return $continue;
                    });
                }
            }            
        }
    }

    /**
     * Initialize the autoload system
     *
     * @return void
     */
    protected function setAutoload(){
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            if (file_exists(__APP_PATH__."/{$file}")) {
                require (__APP_PATH__."/{$file}");
                return true;
            }
            return false;
        });
    }


    /**
     * CHeck if site is under maintenance
     *
     * @return boolean
     */
    public function isMaintenanceActive(){
        return (new Storage('var'))->isFile('maintenance.lock');
    }

    /**
     * Check if is running in console
     *
     * @return boolean
     */
    public function isCommandLine(){
        return $this->mode == 'cli';
    }

    /**
     * Watch for debug porpouse
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function watch(string $key,$value){
        if(is_scalar($value) || is_array($value)){
            $value = &$value;
        }

        return $this->watcher->register($key,$value);
    }

    /**
     * returns all watched elements, or a specific key if sent
     *
     * @param string $key
     * @return mixed
     */
    public function getWatched(string $key = null){
        if($key){
            return $this->watcher->get($key,null);
        }

        return $this->watcher->all();
    }


    /**
     * Add entry in session tracking
     *
     * @param [type] $trackedItem
     * @return void
     */
    public function track($trackedItem){
        $sessionKey = getEnv('SESSION_NAME');
        $this->session["{$sessionKey}_tracker"][] = $trackedItem;
    }

    /**
     * Execute a console command line
     * return s the command line response
     *
     * @param string $command
     * @return string
     */
    public function execCommand(string $command=''){
        $argumens   = [];
        $command    = trim($command);

        if(stripos($command,'console') === false){
            $command = 'console '.$command;
        }

        preg_match_all ('/(?<=^|\s)([\'"]?)(.+?)(?<!\\\\)\1(?=$|\s)/', $command, $ms);
        $argumens = $ms[2];

        $this->console->listen($argumens);
        $contents = stream_get_contents(STDOUT, -1, 0);
        $contents2 = stream_get_contents(STDERR, -1, 0);


        var_dump($contents,$contents2);
        die();
    
        // try{

        //     $this->console->listen($argumens);
        //     $contents = stream_get_contents(STDOUT, -1, 0);
        //     $contents2 = stream_get_contents(STDERR, -1, 0);


        //     var_dump($contents,$contents2);
        //     die();
        //     return $contents;
        // }
        // catch(\Exception $e){
        //     echo "errore!!";
        //     die();
        //     return $e->getMessage();    
        // }
    }
}