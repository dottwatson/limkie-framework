<?php 
namespace Limkie;

use claviska\SimpleImage;

class Image extends SimpleImage{
    protected $outputMimeTypeResponse;
    protected $outputQualityResponse; 

    /**
     * Store output settings for response
     *
     * @param string $mimeType
     * @param integer $quality
     * @return self
     */
    public function as(string $mimeType=null, int $quality = null){
        $this->outputMimeTypeResponse = $mimeType;
        $this->outputQualityResponse = $quality;
        return $this;
    }


    /**
     * the image string for response
     *
     * @return string;
     */
    public function toResponseString(){
        return $this->toString($this->outputMimeTypeResponse,$this->outputQualityResponse);
    }
}
?>