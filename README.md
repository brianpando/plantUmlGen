# plantUmlGen
This is a first version of Laravel package to auto generate new models and migrations from PlantUml (http://plantuml.com) file.

#Install
composer install brianpando/plantumlgen

#how to Use
you need have the Plantuml file (plantuml.txt) in root project, this file must be contains the class diagram with a namespace models. For learn how to write a class diagram please visit http://plantuml.com/es/class-diagram.

you can write your diagram using online editor https://www.planttext.com.

This is an diagram class example:

@startuml
class models.Author{
    name
    lastname
}
class models.Book{
    title
    year:int
    edition
}
class models.Store{
    address
}
models.Book "1" *-- "1"models.Author
models.Store "1" o--"*" models.Book
@enduml

if you need, this package get the plantuml.jar who create a png diagram file, for use it, you must be in root project and execute:
java -jar vendor/briandpando/plantumlgen/plantuml.jar plantuml.txt
this create a png file of diagram class.


#Next
In next versions the package will generate another layers of your code using the class or package diagram from PlantUML.
