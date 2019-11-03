<?php

namespace Brianpando\Plantumlgen\Commands;

use Illuminate\Console\Command;
use File;
use Brianpando\Plantumlgen\Migration;

class PlantMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant:migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the migrations from models namespace';

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
        //$migration=new Migration(base_path());
        $migration=new Migration();
        //$mode=$this->argument('mode')||'create';
        try {
            //1. leer el contenido del archivo plant
            $path=$migration->get_base_path()."/plantuml.pu";
            
            $this->line("=> Searching file $path");
            if( ! $migration->create_plant_file() ) {
                $message = "File $path doesnt exist.";
                $this->line("<fg=black;bg=red>ERROR:</>".$message);
                die($message);
            }
            $content = File::get($path);
            //2. poner las clases en un array de objetos.
            $classes=$migration->read_classes($content);
            if(count($classes)<1 ){
                $message="None class found";
                $this->line("<fg=black;bg=red>ERROR:</>".$message);
                die($message);
            }
        $this->line(count($classes)." classes found:");
         //3. recorrer la lista de clases.
         foreach($classes as $class_content){
             if( $filename = $migration->create_migration_file($class_content) ){
                 $this->line("<info>Created Migration:</info> $filename");
             }else{
                 $this->line("<warning>Created Migration: No need migration.</warning>");
            }    
        }
        } catch (\Exception $e) {
            $this->line("<fg=black;bg=red>ERROR:</> ".$e->getMessage());
        }
        
    }
}
