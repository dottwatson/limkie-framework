<?php 

namespace Limkie\Console\Command\Create;

use Limkie\Console\Console;
use Limkie\Console\Command;
use Limkie\Storage;

class Controller extends Command{
    protected $description = 'Create a basic Controller using dot notation as namespace';

    protected $options = [
        'force'     => ['force override of file.',false,'boolean']
    ];


    protected $arguments = [
        'name' => ['The controller name, with its namespaces separated by dot (eg. Bar.Foo). The file will be create under app/Http/Controller[name]']
    ];

    private $model = '<?php

    namespace __NAMESPACE__;
    
    use Limkie\Http\Controller;
    
    /**
     *  Controller __CLASS__
     *  Created on __DATE__ using console
     */
    class __CLASS__ extends Controller{
    
        public function __construct(){
            parent::__construct();

            //other stuff
        }

        //code here
    }
    ';

    public function handle(){
        $model = $this->model;

        $bits       = preg_split('#\.#',$this->arg('name'),null,PREG_SPLIT_NO_EMPTY);

        $basePath = ['app','Http','Controller'];
        $clsName  = array_pop($bits);

        foreach($bits as $subPath){
            $basePath[]=$subPath;
        }
        
        $namespace  = implode("\\",$basePath);
        $endPath    = implode("/",$basePath);

        $code = str_replace(
            ['__NAMESPACE__','__CLASS__','__DATE__'],
            [$namespace,$clsName,date('Y-m-d H:i:s')],
            $model
        );

        $storage = new Storage(__APP_PATH__);


        if(!$storage->isDir($endPath)){
            $storage->createDir($endPath);
        }

        $storage->moveTo($endPath);

        if($this->opt('force',false) == false && $storage->isFile("{$clsName}.php")){
            Console::error("File {$endPath}/{$clsName}.php already exists. Use --force to force the overwrite");
            Console::critical("Operation abort");
        }



        $storage->createFile("{$clsName}.php",$code);
        Console::notice("Controller successful created.");

    }
}