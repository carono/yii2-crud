<?php


namespace carono\yii2crud\actions;


use carono\yii2crud\CrudController;
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

    public function run()
    {
        /**
         * @var ActiveRecord $searchModel
         */
        $classModel = $this->controller->modelClass;
        $searchModel = $this->controller->modelSearchClass ? new $this->controller->modelSearchClass : null;
        $query = $this->controller->getModelQuery($classModel);
        $dataProvider = $this->controller->queryToDataProvider($query);

        $this->controller->indexCondition($query);
        $this->controller->applySearch($query, $dataProvider, $searchModel);

        $params = $this->controller->indexParams(['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
        return $this->controller->render($this->controller->indexView ?: $this->view, $params);
    }
}