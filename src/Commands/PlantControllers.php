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
    protected $signature = 'plant:controllers --route';

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
        $contenido_api = file_get_contents($controller_gen->get_base_path(). "/routes/api.php");
        dd($classes);
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

    function parsearMetodos($controlador_nombre, $metodos_raw) {
        // Divide los métodos por líneas
        $lineas_metodos = explode("\n", $metodos_raw);

        // Inicializar código de ruta
        $codigo_ruta = "";

        // Iterar sobre los métodos
        foreach ($lineas_metodos as $linea) {
            // Eliminar espacios en blanco al inicio y final de la línea
            $linea = trim($linea);

            // Verificar si la línea es un método
            if ($linea !== '') {
                // Extraer el nombre del método
                preg_match('/(\w+)\(\)/', $linea, $matches);
                $nombre_metodo = $matches[1] ?? '';

                // Verificar si el método es "resource"
                if ($nombre_metodo === 'resource') {
                    // Generar código para Route::resource
                    $codigo_ruta .= "Route::resource('/{$controlador_nombre}', {$controlador_nombre}Controller::class);\n";
                } else {
                    // Generar código para otro tipo de método
                    $codigo_ruta .= "Route::post('/{$controlador_nombre}/{$nombre_metodo}', '{$controlador_nombre}Controller@{$nombre_metodo}');\n";
                }
            }
        }

        return $codigo_ruta;
    }

    // Función para insertar la dependencia del controlador en el archivo api.php
    function insertarDependencia($contenido_api, $controlador_nombre) {
        // Verificar si la dependencia ya está agregada
        $dependencia = "use App\\Http\\Controllers\\{$controlador_nombre}Controller;";
        if (strpos($contenido_api, $dependencia) === false) {
            // Patrón para encontrar el espacio donde insertar la dependencia
            $patron_espacio = '/use Illuminate\\Support\\Facades\\Route;/';

            // Insertar la dependencia después de la línea con "use Illuminate\\Support\\Facades\\Route;"
            $contenido_api = str_replace($patron_espacio, "$0\n\n{$dependencia}\n", $contenido_api);
        }

        return $contenido_api;
    }

    // Función para insertar el código de ruta después de la dependencia del controlador
    function insertarCodigoRuta($contenido_api, $codigo_ruta) {
        // Patrón para encontrar la dependencia del controlador
        $patron_dependencia = "/use App\\\\Http\\\\Controllers\\\\(\w+)Controller;/";

        // Encontrar la última dependencia del controlador
        preg_match_all($patron_dependencia, $contenido_api, $matches);
        $ultima_dependencia = end($matches[0]);

        // Insertar el código de ruta después de la última dependencia
        return str_replace($ultima_dependencia, "{$ultima_dependencia}\n\n{$codigo_ruta}", $contenido_api);
    }
}
