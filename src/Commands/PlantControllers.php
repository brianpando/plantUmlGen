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
        $this->apiRouteDependencies = '';
        $this->apiRouteMethods = '';
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

            foreach($classes as $class_content){
                $controlador_nombre = $class_content[1];
                $metodos_raw = $class_content[2];
                // Parsear los métodos y generar código de ruta
                $this->apiRouteMethods .= $this->parsearMetodos($controlador_nombre, $metodos_raw);
                // Generar codigo de dependencias de ruta
                $this->apiRouteDependencies .= $this->crearDependencia($contenido_api, $controlador_nombre);

                if ($filename = $controller_gen->create_controller_file($class_content) ){
                    $this->line("<info>Created Conntroller:</info> $filename");
                    $newApiRouteContent = $this->crearApiRoute($contenido_api);
                    $controller_gen->add_routes($newApiRouteContent);
                } else {
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
    function crearDependencia($contenido_api, $controlador_nombre) {
        // Verificar si la dependencia ya está agregada
        $dependencia = "use App\\Http\\Controllers\\{$controlador_nombre}Controller;\n";
        if (strpos($contenido_api, $dependencia) === false) {
            return $dependencia;
        }
        return '';
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

    function crearApiRoute($contenido_api) {

        if (strpos($contenido_api, "use Illuminate\\Support\\Facades\\Route;") !== false) {
            // Si existe, insertar los controladores después de este bloque
            $position = strpos($contenido_api, "use Illuminate\\Support\\Facades\\Route;") + strlen("use Illuminate\\Support\\Facades\\Route;");
            $newString = substr_replace($contenido_api, "\n" . $this->apiRouteDependencies, $position, 0);
        } else {
            // Si no existe, crear el bloque 'use' y añadir los controladores
            $newString = "<?php\n\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\Route;\n" . $this->apiRouteDependencies;
        }

        $lastRoutePos = strrpos($newString, "Route::");

        if ($lastRoutePos !== false) {
            // Encontrar el fin de la línea después de la última ocurrencia de "Route::"
            $endOfLinePos = strpos($newString, "\n", $lastRoutePos);
            if ($endOfLinePos !== false) {
                // Si se encuentra el fin de la línea, insertar las nuevas rutas justo después
                $newString = substr_replace($newString, "\n" . $this->apiRouteMethods, $endOfLinePos + 1, 0);
            } else {
                // Si no hay un salto de línea después de la última ruta (algo inusual), simplemente añadir al final
                $newString = $newString . "\n" . $this->apiRouteMethods;
            }
        } else {
            // Si no se encuentra "Route::", es decir, no hay rutas, añadir las nuevas rutas al final
            $newString = $newString . "\n" . $this->apiRouteMethods;
        }

        return $newString;
    }
}
