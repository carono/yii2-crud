<?php


namespace carono\yii2crud\actions;


abstract class Action extends \yii\base\Action
{
    public $layout;

    public function init()
    {
        if ($this->layout) {
            $this->controller->layout = $this->layout;
        }
        parent::init();
    }
}