<?php
namespace Dweineratl\LaravelModelHelper\Driver;

use Dweineratl\LaravelModelHelper\Column;
use Dweineratl\LaravelModelHelper\DriverInterface;
use Illuminate\Database\Eloquent\model as Eloquent;
use phpDocumentor\Reflection\DocBlockFactory;

/**
 * Class DocBloc
 *
 * Get a list of database columns from the Model's DocBlock if present
 *
 * @package Dweineratl\LaravelModelHelper\Driver
 */
class DocBlocDriver implements DriverInterface {

  /**
   * @param \Illuminate\Database\Eloquent\model $model
   *
   * @return array
   */
  public function getColumns(Eloquent $model) {
    $factory = DocBlockFactory::createInstance();
    $reflectionClass = new \ReflectionClass($model);
    $docblock = $factory->create($reflectionClass->getDocComment());
    $columns = [];

    foreach($docblock->getTagsByName('property') as $column) {
      $name = $column->getVariableName();

      if (isset($name)) {
        $columns = new Column($name, $column->getType()->__toString());
      }
    }

    return $columns;
  }
}