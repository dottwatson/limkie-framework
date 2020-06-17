<?php 

namespace Limkie\Console\Command\Create;

use Limkie\Console\Console;
use Limkie\Console\Command as CommandCls;
use Limkie\Storage;

class Command extends CommandCls{
    protected $description = 'Create a basic command';

    protected $options = [
        'force'     => ['force override of file.',false,'boolean']
    ];


    protected $arguments = [
        'name' => ['The command name, with its namespaces separated by dot (eg. Bar.Foo)']
    ];

    private $model = '<?php

__NAMESPACE__


use Limkie\Console\Console;
use Limkie\Console\Command;
    

/**
 *  Command class
 */
class __CLASS__ extends Command{

    protected $description  = \'Description for __CLASS__\';

    protected $options      = [];

    protected $arguments    = [];

    public function handle(){
        Console::notice("Command for __CLASS__ called.");
    }


}
';

    public function handle(){
        $model = $this->model;

        $bits       = preg_split('#\.#',$this->arg('name'),null,PREG_SPLIT_NO_EMPTY);

        if(count($bits) == 1){
            array_unshift($bits,'app','Command');            
        }
        elseif(strtolower($bits[0]) !== 'app'){
            array_unshift($bits,'app');            
        }

        $class      = array_pop($bits);
        $namespace  = ($bits)?'namespace '.implode("\\",$bits).';':'';
        
        $code = str_replace(['__NAMESPACE__','__CLASS__'],[$namespace,$class],$model);

        $storage = new Storage(__APP_PATH__.'/app');

        $path = $this->opt('path','Command');

        if(!$storage->isDir($path)){
            $storage->createDir($path);
        }

        $storage->moveTo($path);

        if($this->opt('force',false) == false && $storage->isFile("{$class}.php")){
            Console::error("File {$path}/{$class}.php already exists. Use --force to force the overwrite");
            Console::critical("Operation abort");
        }

        $storage->createFile("{$class}.php",$code);
        Console::notice("Command successful created.");

    }

}