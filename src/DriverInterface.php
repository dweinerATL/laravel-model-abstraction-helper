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
   * @param \Illuminate\Database\Eloquent\model $model
   *
   * @return array
   */
  public function getColumns(Eloquent $model);
}