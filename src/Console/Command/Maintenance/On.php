<?php 

namespace Limkie\Console\Command\Maintenance;

use Limkie\Console\Console;
use Limkie\Console\Command;

class On extends Command{
    protected $description = 'Activate maintenance mode. All requests are refused with a correct http status';

    public function handle(){
        $storage = storage('var');


        if(!$storage->isFile('maintenance.lock')){
            $storage->createFile('maintenance.lock','');
            Console::notice("Maintenance successful activated.");
        }
        else{
            Console::error("Maintenance is already active");

        }

    }


}