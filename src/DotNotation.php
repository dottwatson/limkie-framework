<?php
namespace Limkie;

/**
 * Simple dot notation
 */
class DotNotation{

    /**
     * find a value inside a  target, using a dot notation
     *
     * @param mixed $taget
     * @param string $index
     * @return void
     */
    public static function find($taget,$index){
        $elements   = explode('.',(string)$index);
        $current    = $taget;
        foreach($elements as $index){
            try{
                if( is_object($current) ){
                    $current = &$current->{$index};
                }
                elseif(is_array($current)){
                    $current = &$current[$index];
                }
            }
            catch(\Exception $e){
                return null;
            }
        }
        
        return $current;
    }


}