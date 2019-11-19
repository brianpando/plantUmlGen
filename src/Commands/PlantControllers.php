<?php

namespace Brianpando\Plantumlgen\Commands;

use Illuminate\Console\Command;
use File;
use Brianpando\Plantumlgen\ControllerGen;

class PlantControllers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant:controllers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the controllers from controllers namespace';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $controller_gen=new ControllerGen();
        try {
            //1. leer el contenido del archivo plant
            $path=$controller_gen->get_base_path()."/plantuml.pu";
            
            $this->line("=> Searching file $path");
            if( ! $controller_gen->create_plant_file() ) {
                $message = "File $path doesnt exist.";
                $this->line("<fg=black;bg=red>ERROR:</>".$message);
                die($message);
            }
            $content = File::get($path);
            //2. poner las clases en un array de objetos.
            $classes=$controller_gen->read_classes($content);
            if(count($classes)<1 ){
                $message="None class found";
                $this->line("<fg=black;bg=red>ERROR:</>".$message);
                die($message);
            }
        $this->line(count($classes)." classes found:");
         //3. recorrer la lista de clases.
        foreach($classes as $class_content){
             if( $filename = $controller_gen->create_controller_file($class_content) ){
                 $this->line("<info>Created Conntroller:</info> $filename");
             }else{
                 $this->line("<warning>Created Controller: No need migration.</warning>");
            }    
        }
        } catch (\Exception $e) {
            $this->line("<fg=black;bg=red>ERROR:</> ".$e->getMessage());
        }
        
    }
}
