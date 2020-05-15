<?php

namespace Limkie;

class Translations{
    protected static $translations;

    public static function loadLocale(string $locale = null){
        $locale = ($locale)?$locale:config('app.locale');

        if(is_null(self::$translations)){
            self::$translations = new DataContainer([]);
        }

        if(!self::$translations->has($locale)){
            $path  = path('var/translations/'.$locale);
            $files = glob("{$path}/*.php");
            foreach($files as $file){
                $tmp        = include $file;
                $fileKey    = basename($file,'.php');
                self::$translations->set("{$locale}.{$fileKey}",$tmp);
            }
        }
    }

    public static function get($key,string $default = null){
        $locale = config('app.locale');
        self::loadLocale($locale);

        return self::$translations->get("{$locale}.{$key}",$default);

    }

    public static function getLocale($locale,$key,string $default = null){
        $locale = ($locale)?$locale:config('app.locale');
        self::loadLocale($locale);

        return self::$translations->get("{$locale}.{$key}",$default);

    }

    public static function getTranslations(){
        return (!is_null(self::$translations))
            ?self::$translations->all()
            :[];
    }

}