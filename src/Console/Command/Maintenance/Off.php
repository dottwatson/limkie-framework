<?php 

namespace Limkie\Console\Command\Maintenance;

use Limkie\Console\Console;
use Limkie\Console\Command;

class Off extends Command{
    protected $description = 'Deactivate maintenance mode. All requests are refused with a correct http status';

    public function handle(){
        $storage = storage('var');


        if($storage->isFile('maintenance.lock')){
            $storage->deleteFile('maintenance.lock','');
            Console::notice("Maintenance successful deactivated.");
        }
        else{
            Console::error("Maintenance is already deactivated");
        }

    }


}