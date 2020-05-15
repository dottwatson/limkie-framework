<?php

namespace Limkie;

class Modules{

    public static function buildList(){
        //load data from composer installed file
        $composerFile   = path('vendor/composer/installed.json');
        $data           = file_get_contents($composerFile);
        $packagesList   = json_decode($data,true);
        
        $cacheData      = [];

        foreach($packagesList as $packageInfo){
            $packageKey     = $packageInfo['source']['reference'];
            $sources        = [];
            $aliases        = [];
            $classes        = [];

            if(isset($packageInfo['autoload'],$packageInfo['autoload']['psr-4'])){
                foreach($packageInfo['autoload']['psr-4'] as $name=>$autoloadPath){
                    $packageAliased = self::packageNameToAlias($packageInfo['name']);
                    $declaredClasses = self::getNamespacedCLasses($name);

                    //remove ending backslash
                    $namespaceAliased = preg_replace('#\\$#','',$name);

                    $sources[$name] = path("vendor/{$packageInfo['name']}/{$autoloadPath}");
                    $aliases[$name] = "Modules\\{$packageAliased}\\{$namespaceAliased}";
                }

                $cacheData[$packageKey]    = [
                    'name'      =>$packageInfo['name'],
                    'path'      =>path("vendor/{$packageInfo['name']}"),
                    'src'       =>$sources,
                    'aliases'   =>$aliases,
                    'classes'   =>$declaredClasses
                ];
            }

        }

        return $cacheData;
    }


    public static function discover(){
        //generate modules list
        $list = self::buildList();

        dumpe($list);

        foreach($list as $uKey=>$packageInfo){
            if($packageInfo['src']){
                foreach($packageInfo['src'] as $orginalCls=>$packageSrcPath){
                    dump("dichiaro {$orginalCls} come {$packageInfo['aliases'][$orginalCls]}");
                    class_alias($orginalCls,$packageInfo['aliases'][$orginalCls]);
                }
            }
        }

        // [NAMESPACE] as Modules\vendorName\PackageName\[NAMESPACE] as MODULES\

    }


    protected static function packageNameToAlias($packageName){
        $name = str_replace(['_','-'],' ',$packageName);
        $name = ucwords($name);

        return str_replace([' ','/'],['','\\'],$name);
    }

    public static function getNamespacedClasses($needleNamespace){
        $classes = get_declared_classes();
        $neededClasses = array_filter($classes, function($i) use ($needleNamespace) {
            return strpos($i, $needleNamespace) === 0;
        });

        return $neededClasses;
    }
}