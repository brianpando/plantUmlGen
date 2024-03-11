<?php

namespace Brianpando\Plantumlgen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class GenerateUmlFromMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate UML from database migrations';

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
        $migrationFiles = $this->getMigrationFiles();
        $tables = [];
        foreach ($migrationFiles as $migrationFile) {
            $table = new Table();
            $table->name = $this->getTableNameFromMigration($migrationFile);
            list($columns, $foreignKeys) = $this->getDetailsFromMigration($migrationFile);
            $table->columns = $columns;
            $table->foreignKeys = $foreignKeys;
            //add objet to tables
            $tables[] = $table;
        }
        $umlContent = $this->buildUmlFromTables($tables);

        // save diagram 
        $umlFilePath = base_path('database_diagram.puml');
        File::put($umlFilePath, $umlContent);

        $this->info('UML diagram generated successfully.');
    }

    /**
     * Get all migration files.
     *
     * @return array
     */
    protected function getMigrationFiles()
    {
        return File::glob(database_path('migrations/*.php'));
    }

    /**
     * Get the table name from a migration file.
     *
     * @param string $migrationFile
     * @return string
     */
    protected function getTableNameFromMigration($migrationFile)
    {

        $fileName = pathinfo($migrationFile, PATHINFO_FILENAME);

        //Extract class name from migration file
        preg_match('/create_(\w+)_table/', $fileName, $matches);

        // get mateches
        $className = $matches[1] ?? null;

        // convert class name to camel case
        return Str::studly($className);
    }

    /**
     * Get the columns and foreign keys from a migration file.
     *
     * @param string $migrationFile
     * @return array
     */
    /**
     * Get the columns and foreign keys from a migration file.
     *
     * @param string $migrationFile
     * @return array
     */
    protected function getDetailsFromMigration($migrationFile)
    {

        $migrationContent = file_get_contents($migrationFile);


        $upContent = $this->extractUpMethodContent($migrationContent);

        $columns = $this->extractColumnsFromUpMethod($upContent);
        $foreignKeys = $this->extractForeignKeysFromColumns($columns);

        return [$columns, $foreignKeys];
    }

    protected function extractUpMethodContent($migrationContent)
    {
        preg_match('/Schema::create\(\'(.+?)\',\s*function\s*\(Blueprint\s*\$table\)\s*{(.+?)\}\);/s', $migrationContent, $matches);
        if (isset($matches[2])) {
            return $matches[2];
        }
        return '';
    }

    protected function extractColumnsFromUpMethod($upContent)
    {
        $columns = [];
        preg_match_all('/\$table->([^\s\(]+)\([\'"]([^\'"]+)[\'"](?:,|\))/m', $upContent, $columnMatches, PREG_SET_ORDER);
        foreach ($columnMatches as $columnMatch) {
            $columnName = $columnMatch[2];
            $columnType = $columnMatch[1];
            $columns[] = "$columnName: $columnType";
        }
        return $columns;
    }

    protected function extractForeignKeysFromColumns($columns)
    {
        $foreignKeys = [];
        foreach ($columns as $column) {
            if (strpos($column, 'foreign') !== false) {
                $columnName = explode(':', $column)[0];
                $foreignKeys[] = $columnName;
            }
        }
        return $foreignKeys;
    }



    /**
     * build UML  from objects of table 
     *
     * @param array $tables
     * @return string
     */
    protected function buildUmlFromTables($tables)
    {
        $umlContent = '@startuml' . PHP_EOL;
        foreach ($tables as $table) {
            $umlContent .= "class {$table->name} {" . PHP_EOL;
            foreach ($table->columns as $column) {
                $umlContent .= "    $column" . PHP_EOL;
            }
            // foreach ($table->foreignKeys as $foreignKey) {
            //     $umlContent .= "    $foreignKey" . PHP_EOL;
            // }
            $umlContent .= '}' . PHP_EOL;
            foreach ($table->foreignKeys as $foreignKey) {
                //relationship
                $umlContent .= $table->name."\"*\"--\"1\""  . ucfirst(preg_replace('/_id$/', 's', $foreignKey)) . PHP_EOL;
            }
        }
        $umlContent .= '@enduml' . PHP_EOL;
        return $umlContent;
    }
}

class Table
{
    public $name;
    public $columns = [];
    public $foreignKeys = [];
}
