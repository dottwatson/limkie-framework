<?php

namespace Limkie\Console;

use Limkie\DataContainer;


abstract class Command{

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

    /**
     * Initialize the command
     *
     * @param array $arguments
     * @param array $options
     * @param array $meta
     */
    public function __construct($arguments = [],$options = [],$meta = []){
        $this->args = new DataContainer($arguments);
        $this->opts = new DataContainer($options);
        $this->meta = new DataContainer($meta);
    }

    /**
     * Return a command argument, or default
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function arg($key='',$default= null){
        return $this->args->get($key,$default);
    }

    /**
     * Return a command option, or default
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function opt($key='',$default= null){
        return $this->opts->get($key,$default);
    }

    /**
     * The main method called when command is executed
     *
     * @return void
     */
    abstract public function handle();
}