<?php
namespace Dweineratl\LaravelModelHelper;

use \Illuminate\Database\Eloquent\model as Eloquent;
/**
 * Interface ModelHelperInterface
 *
 * @package Dweineratl\LaravelModelHelper
 */
interface DriverInterface {

  /**
   * Process the model and return an array of Dweineratl\LaravelModelHelper\Column objects
   *
   * @param \Illuminate\Database\Eloquent\model $model
   *
   * @return array
   */
  public function getColumns(Eloquent $model);
}