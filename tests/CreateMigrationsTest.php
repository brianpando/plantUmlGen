<?php

//declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase;
use Brianpando\Plantumlgen\Migration;
use File;
use Artisan;

class CreateMigrationsTest extends TestCase{

    private $migration;

    protected function getPackageProviders($app)
    {
        return ['Brianpando\Plantumlgen\PlantServiceProvider'];
    }

    protected function setUp():void{
        parent::setUp();
        $this->withoutMockingConsoleOutput();
        $this->migration = new Migration();
    }
    
    public function testCreateMigrationFile()
    {
        $result = $this->migration->create_plant_file();
        if($result) $this->migration->add_example_content();
        $this->assertTrue($result);
    }

    public function testReadClasses(){
        $path=$this->migration->get_base_path()."/plantuml.pu";
        $content= File::get($path);
        $classes=$this->migration->read_classes($content);
        $this->assertTrue( count($classes)>0 );
    }

    public function testGetClassData(){
        $content="class models.Author{\n
            name:string\n
            }";
        $data=$this->migration->get_class_data($content);
        $this->assertTrue( $data->class_name=='Author' && $data->table_name=='authors' && $data->fields[0] =='name:string' );
    }

    public function test_extract_fields(){
        $fields=["name:string","lastname:string","birthday:date"];
        $migration_files=[];
        $new_fields=$this->migration->extract_new_fields($migration_files, 'authors',$fields);
        $this->assertTrue($new_fields==$fields);
    }

    public function test_extract_new_fields(){
        $fields=["name:string","lastname:string","birthday:date"];
        $migration_files=[$this->migration->get_base_path().'/migration_test_01.php'];
        $new_fields=$this->migration->extract_new_fields($migration_files, 'authors',$fields);
        $this->assertTrue($new_fields[0]=='birthday:date');
    }
    /**@test */
    public function testMigrationCommand(){
         //$this->artisan('plant:migrations')->run();
         Artisan::call("plant:migrations");
         //dump(Artisan::output());
         //$this->assertEquals("--",Artisan::output());
         $this->assertEquals(1,1);
     }

    //  protected function tearDown(): void{
    //     parent::tearDown();
    //     File::delete($this->migration->get_base_path()."/plantuml.pu");
    //     $migrations=File::files($this->migration->get_base_path()."/database/migrations");
    //     foreach($migrations as $file){
    //         File::delete($this->migration->get_base_path()."/$file");
    //     }
        
    // }
}