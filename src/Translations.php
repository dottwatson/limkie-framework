<?php

namespace Limkie;

class Translations{
    protected static $translations;

    /**
     * Load translations based on specific (if not null) locale or application locale
     *
     * @param string $locale
     * @return void
     */
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

    /**
     * Get translation based on its key
     *
     * @param string $key
     * @param string $default
     * @return string|null
     */
    public static function get(string $key,string $default = null){
        $locale = config('app.locale');
        self::loadLocale($locale);

        return self::$translations->get("{$locale}.{$key}",$default);
    }

    /**
     * Get translations based on key and specific locale
     *
     * @param string $locale
     * @param string $key
     * @param string $default
     * @return string|null
     */
    public static function getLocale($locale,$key,string $default = null){
        $locale = ($locale)?$locale:config('app.locale');
        self::loadLocale($locale);

        return self::$translations->get("{$locale}.{$key}",$default);

    }

    /**
     * return all current translations loaded
     *
     * @return array
     */
    public static function getTranslations(){
        return (!is_null(self::$translations))
            ?self::$translations->all()
            :[];
    }

}