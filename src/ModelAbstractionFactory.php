<?php

namespace Dweineratl\LaravelModelHelper;

use Dweineratl\LaravelModelHelper\Driver\DocBlocDriver;
use Dweineratl\LaravelModelHelper\Driver\DoctrineDriver;
use Dweineratl\LaravelModelHelper\Driver\FillableDriver;

class ModelAbstractionFactory
{

    /**
     * Factory to return an abstraction class to get information about a model
     *
     * @param $model
     *
     * @return \Dweineratl\LaravelModelHelper\Driver\DocBlocDriver|\Dweineratl\LaravelModelHelper\Driver\DoctrineDriver|\Dweineratl\LaravelModelHelper\Driver\FillableDriver
     * @throws \ReflectionException
     */
    public static function create($model)
    {
        $hasDoctrine = method_exists($model->getConnection(), 'getDoctrineDriver');

        if ($hasDoctrine) {
            return new DoctrineDriver($model);
        } else {
            $defaultDbDriver = config('database.default');
            $driverName = 'Dweineratl\\LaravelModelHelper\\Driver\\' . ucfirst($defaultDbDriver) . 'Driver';

            if (class_exists($driverName)) {
                return new $driverName($model);
            } elseif (self::hasDocBloc($model)) {
                return new DocBlocDriver($model);
            } else {
                return new FillableDriver($model);
            }
        }
    }

    /**
     * Instantiate a LaravelModelHelper driver and return an array of Dweineratl\LaravelModelHelper\Column objects
     *
     * @param $model
     * @return array
     * @throws \ReflectionException
     */
    public static function getColumns($model)
    {
        return self::create($model)->getColumns($model);
    }

    /**
     * Does this model have a DocBlock?
     *
     * @param $model
     *
     * @return bool
     * @throws \ReflectionException
     */
    private static function hasDocBloc($model)
    {
        $reflection = new \ReflectionClass($model);

        return !$reflection->getDocComment() ? false : true;
    }
}