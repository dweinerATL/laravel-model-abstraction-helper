<?php
namespace Dweineratl\LaravelModelHelper;

/**
 * Class Column
 *
 * Abstract out column name and types
 *
 * @package Dweineratl\LaravelModelHelper
 *
 * @author David Weiner <David.L.Weiner30030@gmail.com>
 */
class Column {

  /** @var string $name Column Name */
  public $name = '';

  /** @var string $type Column Type */
  public $type = '';

  /**
   * Column constructor.
   *
   * @param $name Column Name
   * @param $type Column Type
   */
  public function __construct($name, $type) {
    $this->setName($name);
    $this->setType($type);
  }

  /**
   * Get the column name
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get the column type
   *
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Set the column name
   *
   * @param $name
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * Set the column type
   *
   * @param $type
   */
  public function setType($type) {
    $this->type = $type;
  }
}