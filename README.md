## Laravel Model Abstraction Helper

### Why go to all this effort?

I'm trying to backfill tests in my Laravel project, and I really wanted to use
[Laravel Test Factory Generator](https://github.com/mpociot/laravel-test-factory-helper) to generate 
[factories](https://laravel.com/docs/master/database-testing#writing-factories) for the tests.  But I had a
problem -- I used [Jens Segers'](https://github.com/jenssegers) [Laravel MongoDB](https://github.com/jenssegers/laravel-mongodb) Eloquent extension for Laravel.

Due to the nature of how MongoDB works, it doesn't have a Doctrine Driver.  So I couldn't use it.  Or could I?  I have 
been adding full PHP DocBlocks to my models, using `@property` to help with hinting and 
variable completion in my IDE.  

I was discussing this approach with [Jason McCreary](https://github.com/jasonmccreary) 
because he was the one that pointed me towards Laravel Test Factory Generator.  He suggested abstracting it out into 
a factory that could use Doctrine, DocBlocks, custom drivers and ultimately, fallback to using the `$fillable` 
array that should be present in a Laravel Model, and _Laravel Model Abstraction Helper_ was born.

### Install

Require this package with composer using the following command:

```bash
composer require dweineratl/laravel-model-abstraction-helper
```

### How to use

For example, Laravel Test Factory Generator uses this code snippet to get the column names and types for a Model:

```php
    protected function getPropertiesFromTable($model)
    {
        $table = $model->getConnection()->getTablePrefix() . $model->getTable();
        $schema = $model->getConnection()->getDoctrineSchemaManager($table);
        $databasePlatform = $schema->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        $platformName = $databasePlatform->getName();
        $customTypes = $this->laravel['config']->get("ide-helper.custom_db_types.{$platformName}", array());
        foreach ($customTypes as $yourTypeName => $doctrineTypeName) {
            $databasePlatform->registerDoctrineTypeMapping($yourTypeName, $doctrineTypeName);
        }

        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }

        $columns = $schema->listTableColumns($table, $database);

        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();
                if (in_array($name, $model->getDates())) {
                    $type = 'datetime';
                } else {
                    $type = $column->getType()->getName();
                }
                if (!($model->incrementing && $model->getKeyName() === $name) &&
                    $name !== $model::CREATED_AT &&
                    $name !== $model::UPDATED_AT
                ) {
                    if (!method_exists($model, 'getDeletedAtColumn') || (method_exists($model, 'getDeletedAtColumn') && $name !== $model->getDeletedAtColumn())) {
                        $this->setProperty($name, $type);
                    }
                }
            }
        }
    }
```

It can be refactored to

```php
    protected function getPropertiesFromTable($model)
    {
        $columns = ModelAbstractionFactory::getColumns($model);

        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();
                if (in_array($name, $model->getDates())) {
                    $type = 'datetime';
                } else {
                    $type = $column->getType();
                }
                if (!($model->incrementing && $model->getKeyName() === $name) &&
                    $name !== $model::CREATED_AT &&
                    $name !== $model::UPDATED_AT
                ) {
                    if (!method_exists($model, 'getDeletedAtColumn') || (method_exists($model, 'getDeletedAtColumn') && $name !== $model->getDeletedAtColumn())) {
                        $this->setProperty($name, $type);
                    }
                }
            }
        }
    }
```

### Drivers
Laravel Model Abstraction Helper uses the concept of "drivers" to handle different types of models.
Laravel Model Abstraction Helper ships with three drivers.  The ModelAbstractionFactory will detect which 
driver to use
 * Doctrine Driver
   
   This driver is for models using a RDBMS that uses Doctrine, such as MySQL, Postgres or SQLite.
 * DocBloc
 
   This driver is for models that don't use a traditional RDBMS such as MongoDB that does not use Doctrine and has a 
   PHP DocBloc with `@property` tags
 * Fillable
 
   This driver is the fallback when the model does not use Doctrine and does not have a DocBlock with at least one 
   `@property` tag.  It uses the `$fillable` array that should be present in every Laravel model

### Class Methods

#### ModelAbstractionFactory

The ModelAbstractionFactory has two public static methods:

```php
  /**
   * Factory to return an abstraction class to get information about a model
   *
   * @param $model
   *
   * @return \Dweineratl\LaravelModelHelper\Driver\DocBlocDriver|\Dweineratl\LaravelModelHelper\Driver\DoctrineDriver|\Dweineratl\LaravelModelHelper\Driver\FillableDriver
   * @throws \ReflectionException
   */
  public static function create($model)
  
  /**
   * Instantiate a LaravelModelHelper driver and return an array of Dweineratl\LaravelModelHelper\Column objects
   * 
   * @param $model
   * @return array
   * @throws \ReflectionException
   */
    public static function getColumns($model)
```
### DriverInterface

Every driver implements the `Dweineratl\LaravelModelHelper\DriverInterface` which only has a single method

```php
  /**
   * Process the model and return an array of Dweineratl\LaravelModelHelper\Column objects
   * 
   * @param \Illuminate\Database\Eloquent\model $model
   *
   * @return array
   */
  public function getColumns(Eloquent $model);
```

### Column

```php
  /**
   * Column constructor.
   *
   * @param $name Column Name
   * @param $type Column Type
   */
  public function __construct($name, $type)

  /**
   * Get the column name
   *
   * @return string
   */
  public function getName()

  /**
   * Get the column type
   *
   * @return string
   */
  public function getType()

  /**
   * Set the column name
   *
   * @param $name
   */
  public function setName($name)

  /**
   * Set the column type
   *
   * @param $type
   */
  public function setType($type)
```
### Custom Drivers
To support other methods of obtaining the columns for a model, _Laravel Model Abstraction Helper_ supports drivers that 
are in the `Dweineratl\LaravelModelHelper\Driver` namespace, and the class name is value of `config('database.default')` 
concated with 'Driver' and in PascalCase (i.e, `Dweineratl\LaravelModelHelper\Driver\CouchdbDriver`).  It must implement 
the `Dweineratl\LaravelModelHelper\DriverInterface` at a minimum, providing the `getColumns()` method that returns an 
array of `Dweineratl\LaravelModelHelper\Column` objects.  `ModelAbstractionFactory` will first check if the model supports
Doctrine.  If it does not support Doctrine, it will then get the value of the default database driver using `config('database.default')`
and check if a custom driver for that database exists.  If it does not, it will then check to see if the model has a
suitable DocBlock.  Finally, if none of the previous drivers are usable, the default fallback of using the `$fillable` 
array will be used.

### License

The Laravel Model Abstraction Helper is free software licensed under the MIT license.
