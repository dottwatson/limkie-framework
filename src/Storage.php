<?php

namespace Limkie;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Storage{
    /**
     * The storage base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * The current working directory path
     *
     * @var string
     */
    protected $workingPath;

    /**
     * initiate the storage
     *
     * @param string $path
     */
    public function __construct(string $path){
        $this->basePath     = realpath($path);
        $this->workingPath  = $this->basePath;
    }

    /**
     * returns storage basepath
     *
     * @return string
     */
    public function getBasePath(){
        return $this->basePath;
    }

    /**
     * returns current working path
     *
     * @param boolean $relative
     * @return string
     */
    public function getWorkingPath(bool $relative = true){
        $returnPath = ($relative == true)
            ?preg_replace('#^'.$this->basePath.'#','',$this->workingPath)
            :$this->workingPath;
        
        return $returnPath;
    }

    /**
     * check if is requested path exists, relative to the current working path
     *
     * @param string $subpath
     * @return boolean
     */
    public function isDir(string $subpath=''){
        return is_dir($this->getFullPath($subpath));
    }

    /**
     * Check if requested file exists, relative to the current working path
     *
     * @param string $subpath
     * @return boolean
     */
    public function isFile(string $subpath=''){
        return is_file($this->getFullPath($subpath));
    }

    /**
     * Get file content
     *
     * @param string $subpath
     * @return string|boolean
     */
    public function contentFile(string $subpath){
        $file = $this->getFullPath($subpath);
        if($this->isFile($subpath)){
            return file_get_contents($file);
        }

        return false;
    }

    /**
     * Create a file with passed content
     *
     * @param string $name
     * @param string $contents
     * @return boolean
     */
    public function createFile(string $name,string $contents=null){
        $fileName = $this->getFullPath($name);
        
        try{
            $dirPath = dirname($fileName);
            if(!is_dir($dirPath)){
                mkdir($dirPath,0777,true);
            }
        
            
            file_put_contents($fileName,$contents);
            return $this;
        }
        catch(\Exception $e){
            return false;
        }
    }

    /**
     * append content to file. if file not exists, create it
     *
     * @param string $name
     * @param string $contents
     * @return boolean
     */
    public function appendFile(string $name,string $contents=null){
        $fileName = $this->getFullPath($name);
        
        try{
            $dirPath = dirname($fileName);
            if(!is_dir($dirPath)){
                mkdir($dirPath,0777,true);
            }
        
            
            file_put_contents($fileName,$contents,FILE_APPEND);
            return $this;
        }
        catch(\Exception $e){
            return false;
        }
    }


    /**
     * create directory recursively
     *
     * @param string $path
     * @return boolean
     */
    public function createDir(string $path){
        $fullPath = $this->getFullPath($path);
        
        try{
            if(!$this->isDir($path)){
                mkdir($fullPath,0777,true);
            }
            return $this;
        }
        catch(\Exception $e){
            return false;
        }

    }


    /**
     * Delete a file 
     *
     * @param string $fileName
     * @return boolean
     */
    public function deleteFile(string $fileName){
        if($this->isFile($fileName)){
            unlink($this->getFullPath($fileName));
        }

        return $this;
    }


    /**
     * delete recursive directory
     *
     * @param string $path
     * @return boolean
     */
    public function deleteDir(string $path){
        $pathToDelete = $this->getFullPath($path);

        if(!$this->isDir($pathToDelete)){
            return false;
        }
        try{
            $contents = scandir($pathToDelete);
            foreach($contents as $entry){
                if($entry != '.' && $entry != '..'){
                    if($this->isDir($entry)){
                        $tmp = new static($pathToDelete);
                        $tmp->deleteDir($entry);
                        unset($tmp);
                    }
                    elseif($this->isFile($entry)){
                        unlink($entry);
                    }
                }
            }
            return $this;
        }
        catch(\Exception $e){
            return false;
        }
    }


    /**
     * move into storage, changing the working path
     *
     * @param string $subpath
     * @return boolean|self
     */
    public function moveTo(string $subpath){
        if(strpos($subpath,'..') !== false){
            return false;
        }
        
        if($this->isDir($subpath)){
            $this->workingPath = realpath($this->workingPath.'/'.$subpath);
            return $this;
        }
        else{
            return false;
        }
    }

    /**
     * returnr to storage basepath or up for N levels
     *
     * @param integer $level
     * @return self
     */
    public function top($level = 0){
        if((int)$level > 0){
            while($this->workingPath != $this->basePath){
                $this->workingPath = realpath($this->workingPath.'/../');
            }
        }
        else{
            $this->workingPath = $this->basePath;
        }
        return $this;
    }

    /**
     * get a full path of requested target
     *
     * @param string $subpath
     * @return string
     */
    public function getFullPath(string $subpath){
        return $this->workingPath.'/'.$subpath;
    }


    /**
     * get all contents of directory, recursive
     *
     * @param string $subpath
     * @return array
     */
    public function listRecursive(string $subpath) {
        $targetPath = $this->getFullPath($subpath);

        if(!$this->isDir($subpath)){
            throw new \Exception($targetPath.' is not a valid path');
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetPath));
    
        $items = [];
        foreach ($iterator as $item){
            if(in_array(basename($item),['.','..'])){
                continue;
            }
            $items[] =$item->getPathname();
        }

        return $items;
    }
    
    /**
     * get all contents of directory
     *
     * @param string $subpath
     * @return array
     */
    public function list(string $subpath) {
        $targetPath = $this->getFullPath($subpath);

        if(!$this->isDir($subpath)){
            throw new \Exception($targetPath.' is not a valid path');
        }

        $contents = scandir($targetPath);
    
        $items = [];
        foreach ($contents as $item){
            if(in_array(basename($item),['.','..'])){
                continue;
            }

            $items[] =realpath($targetPath.'/'.$item);
        }

        return $items;
    }

}

