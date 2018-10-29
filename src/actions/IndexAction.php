<?php


namespace carono\yii2crud\actions;


use app\helpers\QueryHelper;
use app\interfaces\Search;
use carono\yii2crud\CrudController;
use Yii;
use yii\db\ActiveRecord;

/**
 * Class IndexAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 */
class IndexAction extends Action
{
    public function run()
    {
        /**
         * @var ActiveRecord $searchModel
         */
        $classModel = $this->controller->modelClass;
        $searchModel = null;

        $query = $this->controller->getModelQuery($classModel);
        $this->controller->indexCondition($query);

        $dataProvider = $this->controller->queryToDataProvider($query);


        return $this->controller->render('index', $this->controller->indexParams([
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]));
    }
}