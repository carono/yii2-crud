<?php


namespace carono\yii2crud\actions;


use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

abstract class Action extends \yii\base\Action
{
    public $primaryKeyParam;
    public $layout;
    public $view;
    public $modelClass;
    public $renderParams;
    protected $params = [];

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

    public function render($view, $params = [])
    {
        if ($this->renderParams instanceof \Closure) {
            $params = array_merge($params, call_user_func($this->renderParams, null));
        }
        if (is_array($this->renderParams)) {
            $params = array_merge($params, $this->renderParams);
        }
        return $this->controller->render($view, $params);
    }

    public function runWithParams($params)
    {
        $this->params = $params;
        return parent::runWithParams($params); // TODO: Change the autogenerated stub
    }

    public function getPrimaryKeys()
    {
        $ids = [];
        foreach ((array)$this->primaryKeyParam as $param => $field) {
            if (is_integer($param)) {
                $ids[$field] = ArrayHelper::getValue($this->params, $field);
            } else {
                $ids[$field] = ArrayHelper::getValue($this->params, $param);
            }
        }
        return $ids;
    }

    public function findModel($class)
    {
        return $this->controller->findModel($this->getPrimaryKeys(), $class);
    }
}