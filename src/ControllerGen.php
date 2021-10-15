<?php
namespace Brianpando\Plantumlgen;
use File;

class ControllerGen extends PlantFile{

    private $controller_path;

    public function __construct($path=null){
        parent::__construct($path);
        $this->controller_path=$this->base_path."/app/Http/Controllers";
    }

    public function read_classes($plant_content){
        $class_pattern = "/class controllers.\w*\{\s*[\w*\(\)\s]*\}/";
         preg_match_all($class_pattern,$plant_content,$classes);
         $classes=$classes[0];
         return $classes;
    }

    public function add_example_content(){
        $path=$this->base_path."/plantuml.pu";
        File::put($path,"@startuml\n".
        "class controllers.IndexController{\n".
            "\tlist()\n".
            "\tstore()\n".
                
        "}\n".
        "class controllers.BookController{\n".
            "\tlist()\n".
            "\tstore()\n".
        "}\n".
        "@enduml");
        return true;
    }

    public function create_controller_file($class_content){
        
        $class_data=$this->get_class_data($class_content);
        $mode="create";
        if(  $file = $this->controller_exists($class_data->class_name) ){
                $this->add_new_methods($file,$class_data->methods);
                $mode="update";
        }else{
            //4. crear un archivo de la clase y escribir el codigo de clase model.
            $controller_content=$this->controller_content($class_data->class_name,$class_data->methods );
            $filename=$class_data->class_name. "Controller" .".php";
            $filepath=$this->controller_path."/$filename";
            File::put($filepath,$controller_content);
            if( File::exists($filepath) ) return $filepath;
            else throw new \Exception("file $filename. creation failed.");
        }
        return false;
    }


    public function add_new_methods($filename,$methods){
        
        $methods=$this->extract_new_methods($filename,$methods);
        $content="";
        foreach($methods as $method){
            $content.="\tpublic function $method{\n".
                "\t\t//your code here\n".
                "\t}\n";
        }
        $file_content=File::get($this->controller_path."/$filename");
        $new_content=preg_replace("/\}\s*$/",$content,$file_content);
        $new_content.="}";
        File::put($this->controller_path."/$filename", $new_content);
    }

    public function get_class_data($class_content){
        $pattern="/class controllers.(\w*)\{\s*([\w*\(\)\s]*)\}/";
        preg_match_all($pattern,$class_content,$clzz);
       
        $class_name=$clzz[1][0];
        $class_methods=$clzz[2][0];
        $pattern_methods="/(\w+)\(\)\n/";
        preg_match_all($pattern_methods,$class_methods,$methods);    
         
        $methods = $methods[1];
        return (object)[
            'class_name'=>$class_name,
            'methods'=>$methods,
        ];
        
    }

    public function controller_exists($class_name){
        $file=$class_name.".php"; 
        if ( ! File::exists($this->controller_path."/".$file) ) $file=false;
        return $file;
    }

    function controller_content($class_name,$methods ){
        $ControllerName = $class_name . "Controller";
        $content ="<?php\n".
        "namespace App\Http\Controllers;\n".
        "use Illuminate\Http\Request;\n".
        "use App\Models\\" . $class_name . ";\n".
        "\n".
        "class $ControllerName extends Controller{\n\n";

        foreach( $methods as $method ){
            switch ($method) {
                case 'store':
                    $content .= "\tpublic function $method(Request $"."request){\n".
                        "\t\t$"."params = $"."request->all();\n".
                        "\t\t$"."query = ".$class_name."::post($"."params);\n".
                        "\t\treturn $"."query;\n".
                        "\t}\n\n";
                    break;
                case 'list':
                    $content .= "\tpublic function $method(){\n".
                                "\t\t$"."query = ".$class_name."::select('*')->whereNull('deleted_at')->get();\n".
                                "\t\treturn $"."query;\n".
                                "\t}\n\n";
                    break;
                case 'update':
                    $content .= "\tpublic function $method(Request $"."request, $"."id){\n".
                        "\t\t$"."params = $"."request->all();\n".
                        "\t\t$"."query = ".$class_name."::where('id', $"."id)->update($"."params);\n".
                        "\t\treturn $"."query;\n".
                        "\t}\n\n";
                    break;
                case 'delete':
                    $content .= "\tpublic function $method($"."id){\n".
                        "\t\t$"."query = ".$class_name."::find($"."id);\n".
                        "\t\t$"."query->delete();\n".
                        "\t\treturn $"."query;\n".
                        "\t}\n\n";
                    break;
                default:
                    $content .= "\tpublic function $method(){\n".
                                "\t\t//your code here\n".
                                "\t}\n\n";
                    break;
            }
        }
        $content.="}";
        return  $content;
    }
    
    function extract_new_methods($filename, $methods){
        $content = File::get($this->controller_path."/$filename");
        //preg_match_all("/public class \w*\(\w*\s\w*\){\n(\.*)\n}/", $content);
        foreach($methods as $i => $method){
            if( preg_match_all("/".$method."\(\w*\s*\w*\)/",$content)  ){
                unset($methods[$i]);
            }
        }
        $methods = array_values($methods);
        return $methods;
    }
}