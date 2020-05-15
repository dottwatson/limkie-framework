<?php 

namespace Limkie\Console\Command\Create;

use Limkie\Console\Console;
use Limkie\Console\Command;
use Limkie\Storage;

class Model extends Command{
    protected $description = 'Create a basico model using dot notation as namespace';

    protected $options = [
        'path:p'    => ['specify the path where is created the model. if not set, the default path is app/Model, otherwhire is a path startig from app',false,'string'],
        'force'     => ['force override of file.',false,'boolean']
    ];


    protected $arguments = [
        'name' => ['The model name, with its namespaces separated by dot (eg. Bar.Foo)']
    ];

    private $model = '<?php

    __NAMESPACE__
    
    use Limkie\Model;
    
    /**
     *  Model Data, implements the DataContainer
     */
    class __CLASS__ extends Model{
        protected $mutators = [];
    
        //code here
    
    }
    ';

    public function handle(){
        $model = $this->model;

        $bits       = preg_split('#\.#',$this->arg('name'),null,PREG_SPLIT_NO_EMPTY);

        if(count($bits) == 1){
            array_unshift($bits,'app','Model');            
        }
        elseif(strtolower($bits[0]) !== 'app'){
            array_unshift($bits,'app');            
        }

        $class      = array_pop($bits);
        $namespace  = ($bits)?'namespace '.implode("\\",$bits).';':'';
        
        $code = str_replace(['__NAMESPACE__','__CLASS__'],[$namespace,$class],$model);

        $storage = new Storage(__APP_PATH__.'/app');

        $path = $this->opt('path','Model');

        if(!$storage->isDir($path)){
            $storage->createDir($path);
        }

        $storage->moveTo($path);

        if($this->opt('force',false) == false && $storage->isFile("{$class}.php")){
            Console::error("File {$path}/{$class}.php already exists. Use --force to force the overwrite");
            Console::critical("Operation abort");
        }

        $storage->createFile("{$class}.php",$code);
        Console::notice("Model successful created.");

    }

}