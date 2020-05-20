<?php
namespace Limkie;

use Tracy\Debugger as DebuggerTool;
use Tracy\Helpers;
use Limkie\Http\Request;
use Limkie\DebugWatcher;


class Debugger{

        public static function init(){

            $appEnv = getenv('ENVIRONMENT');

            $debugMode = DebuggerTool::DETECT;
            if($appEnv == 'PRODUCTION'){
                $debugMode = DebuggerTool::PRODUCTION;
            }
            if($appEnv == 'DEVELOPMENT'){
                $debugMode = DebuggerTool::DEVELOPMENT;
            }
    
            if(config("env.{$appEnv}.debug.enabled")){
                DebuggerTool::enable($debugMode, config('storage.logs') );
                DebuggerTool::$showBar      = config("env.{$appEnv}.debug.bar",false);
                DebuggerTool::$strictMode   = config("env.{$appEnv}.debug.error.level");
                DebuggerTool::$logSeverity  = config("env.{$appEnv}.debug.log.severity");

                DebuggerTool::$maxDepth  = config("env.{$appEnv}.debug.maxDepth",5);
                DebuggerTool::$maxLength = config("env.{$appEnv}.debug.maxLength",150);

                DebuggerTool::$email = config("env.{$appEnv}.debug.notify");

            }
    


            DebuggerTool::getBlueScreen()->addPanel(function (?\Throwable $e) { // catched exception

                $contextData = DebugWatcher::getInstance()->all();
                $counter = 0;
                
                $contents = '';
                foreach($contextData as $contextKey=>$contextInfo){
                    $name = '<p><a href="#tracy-addons-contextData-'.$counter.'" class="tracy-toggle"><b>'.$contextKey.'</b></a></p>';
                    
                    $contextInfo = (
                            is_object($contextInfo) && 
                            ( 
                                get_class($contextInfo)  == DataContainer::class || 
                                is_subclass_of($contextInfo,DataContainer::class) 
                            )
                        )
                        ?$contextInfo->all()
                        :$contextInfo;


                    $content = Helpers::capture(function() use ($contextInfo){
                        dump( $contextInfo );
                    });

                    $content = '<div id="tracy-addons-contextData-'.$counter.'">'.$content.'</div>';
    
                    $contents.=$name.$content;
                
                    $counter++;
                }

                return [
                    'tab' => 'Context',
                    'panel' =>'
                    <div class="tracy-inner">
                        <div class="tracy-inner-container">
                        '.$contents.'
                        </div>
                    </div>
                    ',
                    'bottom'=> true
                ];

            
            });
        }

}