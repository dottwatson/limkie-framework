<?php 

namespace Limkie\Console\Command\Create;

use Limkie\Console\Console;
use Limkie\Console\Command;
use Limkie\Storage;

class Module extends Command{
    protected $description = 'Create a module scheleton';

    protected $options = [
        'force'     => ['force override of file.',false,'boolean']
    ];


    protected $arguments = [
        'name' => ['The module name, it, will b the path of your module. The name will be converted in camelcase if necessary. The namespaces will follow module name']
    ];

    private $model = '<?php

    namespace Modules;
    
    use Limkie\Module as CoreModule;

    /**
     *  Module __CLASS__
     *  Created on __DATE__ using console
     */
    class Module extends CoreModule{
    
        public function __construct(){
            parent::__construct();

            //other stuff
        }

        //code here
    }
    ';

    public function handle(){
        $model = $this->model;

        $moduleName = preg_replace('#[^a-z0-9\_]+#i',' ',$this->arg('name'));
        $moduleName = preg_replace('#\s+#',' ',$moduleName);
        $moduleName = str_replace(" ","",ucwords($moduleName));


        $modulesPath    = path(getEnv('MODULES_DIR'));
        $storage        = new Storage($modulesPath);

        $code = str_replace(
            ['__CLASS__','__DATE__'],
            [$moduleName,date('Y-m-d H:i:s')],
            $model
        );


        if($this->opt('force',false) == false && $storage->isDir($moduleName)){
            Console::error("Module {$moduleName} already exists. Use --force to force the overwrite");
            Console::critical("Operation abort");
            return;
        }


        if(!$storage->isDir($moduleName)){
            $storage->createDir($moduleName);
        }


        $storage->moveTo($moduleName);

        $folders = [
            'config',
            'Http',
            'Http/Controller',
            'Http/Gate',
            'Http/Response',
            'Http/Route',
            'public',
            'resources',
            'View'
            'Model'
        ];

        foreach($folders as $folder){
            if(!$storage->isDir($folder)){
                $storage->createDir($folder);
            }
        }

        $storage->createDir($folder);

        $storage->createFile(
            'Http/Route/web.php',
            "<?php\n\nuse Limkie\Route;\nuse Limkie\Http\Request;\nuse Limkie\Http\Response;\n\n\n"
        );

        $storage->createFile(
            'Http/Route/api.php',
            "<?php\n\nuse Limkie\Route;\nuse Limkie\Http\Request;\nuse Limkie\Http\Response;\n\n\n"
        );

        $storage->createFile("Module.php",$code);
        Console::notice("Module {$moduleName} successful created");
    }
}