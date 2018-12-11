<?php


namespace carono\yii2crud;


use carono\yii2crud\actions\CreateAction;
use carono\yii2crud\actions\DeleteAction;
use carono\yii2crud\actions\DeleteBatch;
use carono\yii2crud\actions\IndexAction;
use carono\yii2crud\actions\UpdateAction;
use carono\yii2helpers\QueryHelper;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\BaseDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

abstract class CrudController extends Controller
{
    public const SCENARIO_CREATE = 'create';

    /**
     * @var ActiveRecord
     */
    public $modelClass;
    /**
     * @var ActiveRecord
     */
    public $modelSearchClass;

    public $createClass;

    public $updateClass;

    public $updateView = 'update';
    public $indexView = 'index';
    public $viewView = 'view';

    /**
     * @param ActiveRecord|string $class
     * @return ActiveQuery
     */
    public function getModelQuery($class)
    {
        return $class::find();
    }

    /**
     * @param $id
     * @param null $class
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    public function findModel($id, $class = null): ActiveRecord
    {
        /**
         * @var ActiveRecord $class
         */
        $class = $class ?? $this->modelClass;
        $query = $this->getModelQuery($class)->andWhere(['id' => (int)$id]);
        $this->findModelCondition($query);
        if (($model = $query->one()) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * @param ActiveQuery $query
     * @return ActiveDataProvider
     */
    public function queryToDataProvider($query): ActiveDataProvider
    {
        /**
         * @var ActiveRecord $modelClass
         */
        $modelClass = $query->modelClass;
        $table = $modelClass::tableName();
        $options = [
            'sort' => [
                'defaultOrder' => [
                    current(Yii::$app->db->getTableSchema($table)->primaryKey) => SORT_ASC
                ]
            ],
        ];
        return new ActiveDataProvider(array_merge(['query' => $query], $options));
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-batch' => ['POST']
                ],
            ],
        ]);
    }

    public function applySearch(ActiveQuery $query, BaseDataProvider $dataProvider, $searchModel): void
    {
        QueryHelper::regular($searchModel, $query);
    }

    /**
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public function findModelCondition($query): ActiveQuery
    {
        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public function indexCondition($query): ActiveQuery
    {
        return $query;
    }

    /**
     * @param $params
     * @return array
     */
    public function indexParams($params): array
    {
        return $params;
    }

    /**
     * @param $id
     * @return mixed|string
     */
    public function actionView($id)
    {
        if (Yii::$app->request->isPost) {
            return $this->runAction('update', ['id' => $this->id]);
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param $model
     */
    public function beforeCreate($model): void
    {
    }

    /**
     * @param $model
     * @return array
     */
    public function createRedirect($model): array
    {
        return ['index'];
    }

    /**
     * @param $model
     * @return array
     */
    public function updateRedirect($model): array
    {
        return ['index'];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'update' => [
                'class' => UpdateAction::class,
            ],
            'index' => [
                'class' => IndexAction::class
            ],
            'create' => [
                'class' => CreateAction::class
            ],
            'delete' => [
                'class' => DeleteAction::class
            ],
            'delete-batch' => [
                'class' => DeleteBatch::class
            ]
        ];
    }
}