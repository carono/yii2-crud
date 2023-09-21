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
    protected $searchModel;

    public function getSearchModel()
    {
        return $this->searchModel;
    }

    public function run()
    {
        /**
         * @var ActiveRecord $searchModel
         */
        $this->searchModel = $this->modelSearchClass ? new $this->modelSearchClass : null;
        $query = is_callable($this->query) ? call_user_func($this->query, $this->modelClass, $this) : $this->query;

        if (is_callable($this->condition)) {
            call_user_func($this->condition, $query, $this);
        } elseif ($this->condition) {
            $query->andWhere($this->condition);
        }

        $dataProvider = is_callable($this->dataProvider) ? call_user_func($this->dataProvider, $query, $this) : $this->dataProvider;

        return $this->render($this->view, ['searchModel' => $this->searchModel, 'dataProvider' => $dataProvider]);
    }
}