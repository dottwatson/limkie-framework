<?php 

namespace Limkie\Console\Command\Vendor;

use Limkie\Console\Console;
use Limkie\Console\Command;
use Limkie\Storage;

class Publish extends Command{
    protected $description = 'Publish packege items ';

    protected $options = [
        'type'      => ['view,config or assets',false,'string'],
        'force'     => ['force override of file.',false,'boolean']
    ];


    protected $arguments = [
        'name' => ['The vendor-name/package-name'],
    ];

    public function handle(){
        $packagePath = path('vendor/'.$this->arg('name'));

        // if(!is_dir($packagePath)){
        //     Console::error("Package  ".$this->arg('name')." does not exists");
        //     Console::critical("Operation abort");
        // }

        $types = ['view','config','assets','all'];
        $optionsTypes = explode(',',$this->opt('type','all'));

        array_walk($optionsTypes,'trim');
        
        $notAllowedTypes = array_diff($optionsTypes,$types);

        if($notAllowedTypes){
            Console::error(implode(',',$notAllowedTypes).' is not a valid type. Specify if view,config or assets. You can use `--type="all"` for all, or several types with `--type="view,config"`');
            Console::critical('Operation abort');

        }

        die('OK');

        // if($this->opt('force',false) == false && $storage->isFile("{$class}.php")){
        // }

        // $storage->createFile("{$class}.php",$code);
        // Console::notice("Controller successful created.");

        exit;
    }

}