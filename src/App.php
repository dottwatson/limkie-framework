<?php
namespace Limkie;

// use Limkie\DebugWatcher;
// use Limkie\Config;
// use Limkie\DB;
// use Limkie\Storage;
use Limkie\Console\Console;
use Limkie\Traits\Events;
use Limkie\Debugger as InternalDebugger;
use Dotenv\Dotenv;

class App{
    use Events;

    protected static $instance;
    protected static $initialized;

    protected $config;

    protected $watcher;

    protected $console;
    protected $session;
    protected $router;

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

    public function __get($name){
        switch($name){
            case 'config':
            case 'session':
            case 'console':
                return $this->{$name};
            break;
            case 'route':
                $data = Route::getDispatcher()->getRouteInfo();
                return new DataContainer($data);
            break;
        }
    }


    /**
     * Singleton
     *
     * @param string $component
     * @return self
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * Initialize the app
     *
     * @return void
     */
    public function init(){
        if(self::$initialized){
            return;
        }
        
        $this->mode = PHP_SAPI;

        $this->console->loadCommands();

        if(!$this->isMaintenanceActive()){
            $this->registerGates();
            $this->registerRoutes();
        }


        if($this->isMaintenanceActive() && !$this->isCommandLine()){
            $publicStorage = new Storage(path('public'));
            echo response($publicStorage->contentFile('maintenance.html'));
            exit;
        }


        //initialize and instantiate modules
        Modules::init();

        $this->trigger('init');

        self::$initialized = true;
    }


    /**
     * returns the module instance 
     *
     * @param string $moduleName
     * @return object
     */
    public function module(string $moduleName){
        return Modules::getModule($moduleName);
    }

    /**
     * returns if module is loaded 
     *
     * @param string $moduleName
     * @return bool
     */
    public function isModule(string $moduleName){
        return Modules::isModule($moduleName);
    }


    /**
     * Get the context where application run
     *
     * @return void
     */
    public function getMode(){
        return $this->mode;
    }

    /**
     * Load routes based on current environment and declared in app/Http/Route
     *
     * @return void
     */
    protected function registerRoutes(){
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
     * Load application gates declared in app/Http/Gate 
     *
     * @return void
     */
    protected function registerGates(){
        //load Gate
        $files = glob(__APP_PATH__."/app/Http/Gate/*.php");
        foreach($files as $gateFile){
            require $gateFile;
        }

        //list all classes under Gate namespace
        $classes = get_declared_classes();
        foreach($classes as $className){
            $lowerClsName = strtolower($className);
            if(strpos($lowerClsName,'app\\http\\gate\\') === 0){
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

                        $continue = ($nextStep === true || $nextStep === null)?null:false;

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
        $storage = new Storage(path('var')); 
        return $storage->isFile('maintenance.lock');
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

        $this->watcher->register($key,$value);
    
        return $this;
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

        return $this;
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

        return $contents;
        // var_dump($contents,$contents2);
        // die();
    }


    public function env(string $key = null){
        $key =  ($key)?".{$key}":"";

        $envKey = 'env.'.getEnv('ENVIRONMENT').$key;

        return $this->config->get($envKey);
    }

}