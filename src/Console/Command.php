<?php

namespace Limkie\Console;

use Limkie\Console\Console;
use Limkie\DataContainer;


class Command{

    protected $description = '';

    protected $options   = [
        // 'optiona:a'=>['option a description',true,'integer'],   // --optiona -a required int|float
        // 'optionb:b'=>['option b description',false,'string'],   // --optionb -b not-required string
        // 'optionc:c'=>['option c description',true,'boolean'],   // --optionc -c required boolean
        // 'optiond:d'=>['option d description'],                  // --optiond -d not-required string
        // 'optione'=>['option e description'],                    // --optione  not-required string
    ];
    protected $arguments = [
        // 'arg1' =>['argumemnt1 description',true],   //required
        // 'arg2' =>['argumemnt2 description',false],  // not-required
    ];


    protected $opts;
    protected $args;
    protected $meta;


    public static function manifest(){
        return [
            'description'=> '',

            'arguments'    => [
                'arg1' =>['argumemnt1 description',true],   //required
                'arg2' =>['argumemnt2 description',false],  // not-required
            ],

            'options' => [
                'optiona:a'=>['option a description',true,'integer'],   // --optiona -a required int|float
                'optionb:b'=>['option b description',false,'string'],   // --optionb -b not-required string
                'optionc:c'=>['option c description',true,'boolean'],   // --optionc -c required boolean
                'optiond:d'=>['option d description'],                  // --optiond -d not-required string
                'optione'=>['option e description'],                    // --optione  not-required string
            ]
        ];
    }

    public function __construct($arguments = [],$options = [],$meta = []){
        $this->args = new DataContainer($arguments);
        $this->opts = new DataContainer($options);
        $this->meta = new DataContainer($meta);
    }


    public function arg($key='',$default= null){
        return $this->args->get($key,$default);
    }

    public function opt($key='',$default= null){
        return $this->opts->get($key,$default);
    }

    public function handle(){

    }
}