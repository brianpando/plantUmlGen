<?php 
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//Generated By PlantUML Command
class CreateAuthors extends Migration{
	public function up(){ 
 		Schema::create('authors', function (Blueprint $table) { 
			$table->bigIncrements('id');
			$table->string('name');
			$table->timestamps();
		});
 	} 
	public function down(){
 
	} 
}