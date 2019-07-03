## Laravel Model Abstraction Helper

### Why go to all this effort?

I'm trying to backfill tests in my Laravel project, and I really wanted to use
[Laravel Test Factory Generator](https://github.com/mpociot/laravel-test-factory-helper) to generate 
[factories](https://laravel.com/docs/master/database-testing#writing-factories) for the tests.  But I had a
problem -- I used Jens Segers' [Laravel MongoDB](https://github.com/jenssegers/laravel-mongodb) Eloquent extension, 
which, due to the nature of how MongoDB works, doesn't have a Doctrine Driver.  So I couldn't use Laravel Test Factory 
Generator.  Or could I?  I have been adding full PHP DocBlocks to my models, using @property to help with hinting and 
variable completion in my IDE.  I was discussing this approach with [Jason McCreary](https://github.com/jasonmccreary) 
because he was the one that pointed me towards Laravel Test Factory Generator.  He suggested abstracting it out into 
a factory that could use Doctrine, DocBlocks, custom Doctrine Drivers and ultimately, fallback to using the `$fillable` 
array that could be present in a Laravel Model, and Laravel Model Abstraction Helper was born.

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
