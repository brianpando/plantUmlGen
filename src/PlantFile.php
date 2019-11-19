<?php
namespace Brianpando\Plantumlgen;
use File;

class PlantFile{
    
    protected $base_path;

    public function __construct($path=null){
        $this->base_path=$path==null?".":$path;
    }


    public function get_base_path(){
        return $this->base_path;
    }

    public function create_plant_file(){
        //dump($this->base_path."/plantuml.pu"); exit;
        $path=$this->base_path."/plantuml.pu";
         if(! File::exists($path) ) {
             File::put($path,"@startuml\n@enduml");
             if(! File::exists($path) )
             return false;
         }
         return true;
     }
}