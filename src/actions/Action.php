<?php


namespace carono\yii2crud\actions;


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
}