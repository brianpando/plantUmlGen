# plantUmlGen
:package: This is a first version of Laravel package to auto generate new models and migrations from [PlantUml](http://plantuml.com) file.

# Install
```
composer require brianpando/plantumlgen
```
# How to Use
you need have the Plantuml plain file (**plantuml.pu**) in root project, this file must be contains the class diagram with a namespace models. For learn how to write a class diagram please visit http://plantuml.com/es/class-diagram.

you can write your diagram using online editor https://www.planttext.com.

This is an class diagram example:
```
@startuml

class models.Author{
    name string
    lastname string
}
class models.Book{
    title string
    year int
    edition string
    publishing date
}
class models.Store{
    address string
}
models.Book "1" *-- "1"models.Author
models.Store "1" o--"*" models.Book

@enduml
```
Now, you can create the models or the migration file using the new commands:

### for models
```
php artisan plant:models
```
This create a models folder in your folder app/, then create each class like a model eloquent including the relationship. please be careful linking with the right relationship in your diagram.

### for migration file
```
php artisan plant:migrations
```
this create a migration file in you migrations folder with the name `[yyy-mm-dd]_create_all_tables.php`, then you can run the migration with `php artisan migrate`. that is all  :heavy_exclamation_mark:.

 :pushpin: if you need, this package get the plantuml.jar who create a png diagram file, for use it, you must be in root project and execute:
```
java -jar vendor/briandpando/plantumlgen/plantuml.jar plantuml.pu
```
this create a png file of diagram class.

# If you are using Visual Studio Code
Exist a extension for plantUML please Launch VS Code Quick Open (Ctrl + P) and type `ext install plantuml`, then install PlantUml ext. if you are using the local file plantuml.jar please you must have installed Java and Graphviz, for generate preview screen in VS. for example in Mac `brew install graphviz`.

Then you 

# Next
In next versions the package will generate another layers of your code using the class or package diagram from PlantUML.
