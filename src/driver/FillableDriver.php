<?php
namespace Dweineratl\LaravelModelHelper\Driver;

use Dweineratl\LaravelModelHelper\Column;
use Dweineratl\LaravelModelHelper\DriverInterface;
use Illuminate\Database\Eloquent\model as Eloquent;

/**
 * Class FillableDriver
 *
 * Bare bones support for table colums, as no Doctrine support was found, no
 * custom driver for the database type was found and no docblock was found
 *
 * @package Dweineratl\LaravelModelHelper\Driver
 */
class FillableDriver implements DriverInterface {

  /**
   * @param \Illuminate\Database\Eloquent\model $model
   *
   * @return array
   */
  public function getColumns(Eloquent $model) {
    // TODO: Implement getColumns() method.
    $columns = [];

    foreach($model->getFillable() as $column) {
      $columns[] = new Column($column, 'string');
    }

    return $columns;
  }
}