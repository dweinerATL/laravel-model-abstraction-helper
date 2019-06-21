<?php

namespace Dweineratl\LaravelModelHelper\Driver;

use Dweineratl\LaravelModelHelper\Column;
use Dweineratl\LaravelModelHelper\DriverInterface;
use \Illuminate\Database\Eloquent\model as Eloquent;

class DoctrineDriver implements DriverInterface {

  /**
   * @param \Illuminate\Database\Eloquent\model $model
   *
   * @return array
   */
  public function getColumns(Eloquent $model) {
    $table = $model->getConnection()->getTablePrefix() . $model->getTable();
    $schema = $model->getConnection()->getDoctrineSchemaManager($table);
    $databasePlatform = $schema->getDatabasePlatform();
    $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

    $platformName = $databasePlatform->getName();
    $customTypes = $this->laravel['config']->get("ide-helper.custom_db_types.{$platformName}",
      []);
    foreach ($customTypes as $yourTypeName => $doctrineTypeName) {
      $databasePlatform->registerDoctrineTypeMapping($yourTypeName,
        $doctrineTypeName);
    }

    $database = NULL;
    if (strpos($table, '.')) {
      list($database, $table) = explode('.', $table);
    }

    $columns = [];

    foreach ($schema->listTableColumns($table, $database) as $column) {
      $columns[] = new Column($column->getName(),
        $column->getType()->getName());
    }

    return $columns;
  }
}