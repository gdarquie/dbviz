# Welcome to DBviz documentation page

## Install project

The project is a Symfony 4 project. It doesn't use database.

```
git clone https://github.com/gdarquie/dbviz.git
```

Cd in the project

```
composer install
```

Launch the server

```
php bin/console server:run
```

Voilà!

##Generate viz

```
dot -Tpng export/viz.dot -o export/viz.png
```

## About the project

DB Viz is a tool for generating DOT language for creating scheme with .yaml files configuration. 