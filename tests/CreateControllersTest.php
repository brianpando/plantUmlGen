<?php

//declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase;
use Brianpando\Plantumlgen\ControllerGen;
use File;
use Artisan;

class CreateControllersTest extends TestCase{

    private $controller_gen;

    protected function getPackageProviders($app)
    {
        return ['Brianpando\Plantumlgen\PlantServiceProvider'];
    }

    protected function setUp():void{
        parent::setUp();
        $this->withoutMockingConsoleOutput();
        $this->controller_gen = new ControllerGen();
    }
    
    public function testCreateControllerFiles()
    {
        $result = $this->controller_gen->create_plant_file();
        if($result) $this->controller_gen->add_example_content();
        $this->assertTrue($result);
    }

    public function testReadClasses(){
        $path=$this->controller_gen->get_base_path()."/plantuml.pu";
        $content= File::get($path);
        $classes=$this->controller_gen->read_classes($content);
        $this->assertTrue( count($classes)>0 );
    }

    public function testGetClassData(){
        $content="class controllers.BookController{\n
            \tlist()\n
            \tstore()\n
            }";
        $data=$this->controller_gen->get_class_data($content);
        $this->assertTrue( $data->class_name=='BookController' && $data->methods[0] =='list' );
    }

    public function test_create_controller_file(){
        $path=$this->controller_gen->get_base_path()."/plantuml.pu";
        $content= File::get($path);
        $classes=$this->controller_gen->read_classes($content);
        $file1=$this->controller_gen->create_controller_file($classes[0]);
        $this->assertTrue( true );
    }

    public function test_extract_new_methods(){
        $methods=["list()","store()","delete()"];
        $file="IndexController.php";
        $new_methods=$this->controller_gen->extract_new_methods($file,$methods);
        $this->assertTrue($new_methods== ['delete()'] );
    }

    public function test_add_new_methods(){
        $methods=["list()","store()","delete()"];
        $file="IndexController.php";
        $this->controller_gen->add_new_methods($file,$methods);
        $this->assertTrue( true );
    }

    public function testMigrationCommand(){
         Artisan::call("plant:controllers");
         $this->assertEquals(1,1);
     }


    public function test_clean(){
        File::delete($this->controller_gen->get_base_path()."/app/Http/Controllers/IndexController.php");
        $this->assertTrue( true );
    }
}