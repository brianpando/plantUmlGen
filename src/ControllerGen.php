<?php
namespace Brianpando\Plantumlgen;
use File;

class ControllerGen extends PlantFile {

    private $controller_path;
    private $request_path;
    private $route_path;

    public function __construct($path=null){
        parent::__construct($path);
        $this->controller_path=$this->base_path."/app/Http/Controllers";
        $this->request_path=$this->base_path."/app/Http/Requests";
        $this->route_path=$this->base_path."/routes";
    }

    public function read_classes($plant_content){
        $class_pattern = "/class controllers\.(\w+)\s*\{\s*([^\}]+)\}/";
        preg_match_all($class_pattern,$plant_content,$classes,PREG_SET_ORDER);
        //dd($classes[2]);
        $classes=$classes[0];
        return $classes;
    }

    public function add_example_content(){
        $path=$this->base_path."/plantuml.pu";
        File::put($path,"@startuml\n".
        "class controllers.IndexController{\n".
            "    list()\n".
            "    store()\n".
                
        "}\n".
        "class controllers.BookController{\n".
            "    list()\n".
            "    store()\n".
        "}\n".
        "@enduml");
        return true;
    }

    public function create_controller_file($class_content){
        $class_data=$this->get_class_data($class_content);
        $mode="create";
        if(  $file = $this->controller_exists($class_data->class_name) ){
                $this->add_new_methods($file,$class_data->methods,$class_data->class_name);
                $mode="update";
        }else{
            //4. crear un archivo de la clase y escribir el codigo de clase model.
            $controller_content=$this->controller_content($class_data->class_name,$class_data->methods );
            $filename=$class_data->class_name.".php";
            $filepath=$this->controller_path."/$filename";
            File::put($filepath,$controller_content);
            if( File::exists($filepath) ) return $filepath;
            else throw new \Exception("file $filename. creation failed.");
        }
        //$this->add_routes($class_data->class_name,$class_data->methods);
        return false;
    }

    public function add_routes($class_name, $methods){
        $new_content = "<?php\n\n".
        "use Illuminate\Support\Facades\Route;\n\n";
        foreach ($methods as $key => $method) {
            if (strtolower($method) == 'resource') {
                $new_content .= "Route::resource('".$class_name."', ".ucfirst($class_name)."Controller::class);\n";
            }
        }
        file_put_contents($this->route_path."/api.php", $new_content . PHP_EOL, FILE_APPEND);
    }


    public function add_new_methods($filename,$methods,$class_name){
        
        $methods=$this->extract_new_methods($filename,$methods);
        $content="";
        foreach($methods as $key => $method){
            if (in_array(strtolower($method), ['index','store','show','update','destroy'])) {
                $content .= $this->create_resource_methods($class_name,$method);
            } else {
                $content .= "    public function $method()\n".
                "    {\n".
                "        //your code here\n".
                "    }\n";
                $this->create_requests($class_name, $method);
            }
        }
        $file_content=File::get($this->controller_path."/$filename");
        $new_content=preg_replace("/\}\s*$/",$content,$file_content);
        $new_content.="}\n";
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
        $content ="<?php\n".
        "\nnamespace App\Http\Controllers;\n\n".
        "use Illuminate\Http\Request;\n".
        "use Illuminate\Http\JsonResponse;\n".
        "use App\Http\Request\\" . $class_name . " as " . $class_name . "Request;\n".
        "\n".
        "class " . $class_name . "Controller extends Controller\n{\n";

        foreach( $methods as $method ){
            if (strtolower($method) == 'resource') {
                $content .= $this->create_resource_methods($class_name, 'index');
                $content .= $this->create_resource_methods($class_name, 'update');
                $content .= $this->create_resource_methods($class_name, 'show');
                $content .= $this->create_resource_methods($class_name, 'destroy');
                $content .= $this->create_resource_methods($class_name, 'store');
            } else {
                $content .= "    public function $method()\n".
                "    {\n".
                "        //your code here\n".
                "    }\n";
                $this->create_requests($class_name, $method);
            }
        }
        $content.="}\n";
        return  $content;
    }
    
    function extract_new_methods($filename, $methods){
        $resources = ['index','store','update','destroy','show'];
        $content = File::get($this->controller_path."/$filename");
        //preg_match_all("/public class \w*\(\w*\s\w*\){\n(\.*)\n}/", $content);
        foreach($methods as $i => $method){
            if (strtolower($method) == 'resource') {
                unset($methods[$i]);
                foreach ($resources as $resource) {
                    if (preg_match_all("/public\s+function\s+".$resource."\(/",$content) == 0) {
                        $methods[] = $resource;
                    }
                }
            } elseif (preg_match_all("/".$method."\(\w*\s*\w*\)/",$content)) {
                unset($methods[$i]);
            }
        }
        $methods = array_values($methods);
        return $methods;
    }

    function create_resource_methods($class_name, $method) {
        switch (strtolower($method)) {
            case 'index':
                $content = "    /**\n".
                "     * Display a listing of the resource.\n".
                "     * @author PlantUmlGen\n".
                "     * @param " .$class_name. "Request\\" .$class_name.  "IndexRequest \$request\n".
                "     * @return JsonResponse\n".
                "     */\n".
                "    public function index(" .$class_name. "Request\\" .$class_name.  "IndexRequest \$request): JsonResponse\n".
                "    {\n".
                "        return \$request->response();\n".
                "    }\n";
                break;
            
            case 'store':
                $content = "\n    /**\n".
                "     * Store a newly created resource in storage.\n".
                "     * @author PlantUmlGen\n".
                "     * @param " .$class_name. "Request\\" .$class_name.  "StoreRequest \$request\n".
                "     * @return JsonResponse\n".
                "     */\n".
                "    public function store(" .$class_name. "Request\\" .$class_name.  "StoreRequest \$request): JsonResponse\n".
                "    {\n".
                "        return \$request->response();\n".
                "    }\n";
                break;

            case 'show':
                $content = "\n    /**\n".
                "     * Display the specified resource.\n".
                "     * @author PlantUmlGen\n".
                "     * @param " .$class_name. "Request\\" .$class_name.  "ShowRequest \$request\n".
                "     * @return JsonResponse\n".
                "     */\n".
                "    public function show(" .$class_name. "Request\\" .$class_name.  "ShowRequest \$request): JsonResponse\n".
                "    {\n".
                "        return \$request->response();\n".
                "    }\n";
                break;

            case 'update':
                $content = "\n    /**\n".
                "     * Update the specified resource in storage.\n".
                "     * @author PlantUmlGen\n".
                "     * @param " .$class_name. "Request\\" .$class_name.  "UpdateRequest \$request\n".
                "     * @return JsonResponse\n".
                "     */\n".
                "    public function update(" .$class_name. "Request\\" .$class_name.  "UpdateRequest \$request): JsonResponse\n".
                "    {\n".
                "        return \$request->response();\n".
                "    }\n";
                break;
            
            case 'destroy':
                $content = "\n    /**\n".
                "     * Remove the specified resource from storage.\n".
                "     * @author PlantUmlGen\n".
                "     * @param " .$class_name. "Request\\" .$class_name.  "DestroyRequest \$request\n".
                "     * @return JsonResponse\n".
                "     */\n".
                "    public function destroy(" .$class_name. "Request\\" .$class_name.  "DestroyRequest \$request): JsonResponse\n".
                "    {\n".
                "        return \$request->response();\n".
                "    }\n";
                break;
            
            default:
                $content = "";
                break;
        }
        $this->create_resource_requests($class_name, strtolower($method));
        return $content;
    }

    function create_resource_requests($class_name, $method) {
        $dir_name = $this->request_path . "/" . $class_name;
        File::makeDirectory($dir_name, 0755, true, true);
        $filename=$class_name . ucfirst($method) . "Request.php";
        $filepath=$this->request_path."/".$class_name."/".$filename;
        $content = $this->request_content($class_name,ucfirst($method) . "Request");
        File::put($filepath,$content);
    }

    function create_requests($class_name, $method) {
        $dir_name = $this->request_path . "/" . $class_name;
        File::makeDirectory($dir_name, 0755, true, true);
        $filename=$class_name . $method. "Request.php";
        $filepath=$this->request_path."/".$class_name."/".$filename;
        $content = $this->request_content($class_name,$method);
        File::put($filepath,$content);
    }

    function request_content($class_name,$resource) {
        $tb = "    ";
        $content = "<?php\n".
        "\nnamespace App\Http\Requests\\".$class_name.";\n\n".
        "use Illuminate\Foundation\Http\FormRequest;\n".
        "use Illuminate\Http\JsonResponse;\n\n".
        "class ". $class_name . $resource . " extends FormRequest\n{\n".
        "    /**\n".
        "     * Get the validation rules that apply to the request.\n".
        "     * @author PlantUmlGen\n".
        "     * @return array\n".
        "     */\n".
        "    public function rules(): array\n".
        "    {\n".
        "        return [\n".
        "            '' => '',\n".
        "        ];\n".
        "    }\n\n".
        "    /**\n".
        "     * messages\n".
        "     * @author PlantUmlGen\n".
        "     * @return array\n".
        "     */\n".
        "    public function messages(): array\n".
        "    {\n".
        "        return [\n".
        "            'required' => 'The :attribute is required.',\n".
        "            'max' => 'The :attribute is very long.',\n".
        "            'unique' => 'The :attribute has already been taken.',\n".
        "            'exists' => 'Could not find :attribute',\n".
        "        ];\n".
        "    }\n\n".
        "    /**\n".
        "     * response\n".
        "     * @author PlantUmlGen\n".
        "     * @return JsonResponse\n".
        "     */\n".
        "    public function response(): JsonResponse\n".
        "    {\n".
        "        // your code here\n".
        "    }\n".
        "}\n";
        return $content;
    }

    public function get_attributes()
    {
        $class_pattern = "/class models.(\w*)\{/";
            $class_pattern="/class models.(\w*)\{\s*([\w*:\w*\s]*)\}/";
            $model_path=base_path()."/app/Models";
            preg_match_all($class_pattern,$content,$classes);
    }
}