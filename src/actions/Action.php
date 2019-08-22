<?php


namespace carono\yii2crud\actions;


use yii\helpers\StringHelper;

abstract class Action extends \yii\base\Action
{
    public $primaryKeyParam = 'id';
    public $layout;
    public $view;

    public function init()
    {
        if ($this->layout) {
            $this->controller->layout = $this->layout;
        }
        parent::init();
    }

    public function __call($name, $params)
    {
        if (StringHelper::startsWith($name, 'getMessage')) {
            $property = $this->{lcfirst(substr($name, 3))};
            if ($property instanceof \Closure) {
                return call_user_func_array($property, $params);
            }
            return $property;
        }
        return parent::__call($name, $params);
    }
}