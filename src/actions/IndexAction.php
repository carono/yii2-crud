<?php


namespace carono\yii2crud\actions;


use carono\yii2crud\CrudController;
use Closure;
use yii\db\ActiveRecord;

/**
 * Class IndexAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 */
class IndexAction extends Action
{
    public $view = 'index';
    public $modelSearchClass;
    public $query;
    public $dataProvider;
    public $condition;
    public $params;

    public function run()
    {
        /**
         * @var ActiveRecord $searchModel
         */
        $searchModel = $this->modelSearchClass ? new $this->modelSearchClass : null;
        $query = is_callable($this->query) ? call_user_func($this->query, $this->modelClass, $this) : $this->query;
        $dataProvider = is_callable($this->dataProvider) ? call_user_func($this->dataProvider, $query, $this) : $this->dataProvider;

        if (is_callable($this->condition)) {
            call_user_func($this->condition, $query, $dataProvider, $searchModel, $this);
        } elseif ($this->condition) {
            $query->andWhere($this->condition);
        }

        return $this->render($this->view, ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }
}