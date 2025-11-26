<?php


namespace carono\yii2crud;


use carono\yii2crud\actions\CreateAction;
use carono\yii2crud\actions\DeleteAction;
use carono\yii2crud\actions\DeleteBatch;
use carono\yii2crud\actions\IndexAction;
use carono\yii2crud\actions\UpdateAction;
use carono\yii2crud\actions\ViewAction;
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
    public $viewClass;

    public $updateView = 'update';
    public $indexView = 'index';
    public $viewView = 'view';
    public $createView = 'create';

    public $breadcrumbsAppdend = true;
    public $breadcrumbsNamespace = 'app\breadcrumbs';
    public $breadcrumbsParam = 'breadcrumbs';

    protected $params = [];

    public $primaryKey = ['id'];

    const EVENT_BEFORE_CREATE = 'beforeCreate';
    const EVENT_AFTER_CREATE = 'afterCreate';
    const EVENT_ERROR_CREATE = 'errorCreate';

    const EVENT_BEFORE_UPDATE_LOAD = 'beforeUpdateLoad';
    const EVENT_AFTER_UPDATE_LOAD = 'afterUpdateLoad';
    const EVENT_AFTER_UPDATE = 'afterUpdate';
    const EVENT_ERROR_UPDATE = 'errorUpdate';

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
        $query = $this->getModelQuery($class);
        foreach ($id as $key => $value) {
            $query->andWhere([$key => $value]);
        }
        $query = $this->findModelCondition($query);
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
                    current($modelClass::getDb()->getTableSchema($table)->primaryKey) => SORT_ASC
                ]
            ],
        ];
        return new ActiveDataProvider(array_merge(['query' => $query], $options));
    }

    public function render($view, $params = [])
    {
        if (\Yii::$app->request->isAjax) {
            return parent::renderAjax($view, $params);
        }
        if ($this->breadcrumbsAppdend) {
            Breadcrumbs::$crumbsNamespace = $this->breadcrumbsNamespace;
            \Yii::$app->view->params[$this->breadcrumbsParam] = Breadcrumbs::formCrumbs($this->action, $params);
        }
        return parent::render($view, $params);
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

    /**
     * @param ActiveQuery $query
     * @param BaseDataProvider $dataProvider
     * @param $searchModel
     */
    public function applySearch(ActiveQuery $query, BaseDataProvider $dataProvider, $searchModel): void
    {
        if (method_exists($searchModel, 'updateQuery')) {
            $searchModel->updateQuery($query);
        } else {
            QueryHelper::regular($searchModel, $query);
        }
        if (method_exists($searchModel, 'updateDataProvider')) {
            $searchModel->updateDataProvider($dataProvider);
        }
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
     * @param $model
     */
    public function beforeCreate($model): void
    {
    }

    /**
     * @param $model
     * @return array|string
     */
    public function createRedirect($model)
    {
        return ['index'];
    }

    /**
     * @param $model
     * @return array|string
     */
    public function updateRedirect($model)
    {
        return ['index'];
    }

    /**
     * @param $model
     * @return array|string
     */
    public function deleteRedirect($model)
    {
        return ['index'];
    }

    /**
     * @param $model
     * @return array|string
     */
    public function deleteBatchRedirect()
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
                'view' => $this->updateView,
                'primaryKeyParam' => $this->primaryKey,
                'redirect' => function ($model) {
                    return $this->updateRedirect($model);
                }
            ],
            'index' => [
                'class' => IndexAction::class,
                'view' => $this->indexView,
                'modelClass' => $this->modelClass,
                'modelSearchClass' => $this->modelSearchClass,
                'query' => function ($class, $action) {
                    return $this->getModelQuery($action->modelClass);
                },
                'dataProvider' => function ($query, IndexAction $action) {
                    $searchModel = $action->getSearchModel();
                    $dataProvider = $this->queryToDataProvider($query);
                    $this->applySearch($query, $dataProvider, $searchModel);
                    return $dataProvider;
                },
                'condition' => function ($query, IndexAction $action) {
                    $this->indexCondition($query);
                },
                'renderParams' => function ($params) {
                    return $this->indexParams($params);
                }
            ],
            'view' => [
                'class' => ViewAction::class,
                'primaryKeyParam' => $this->primaryKey,
                'view' => $this->viewView
            ],
            'create' => [
                'class' => CreateAction::class,
                'view' => $this->createView,
                'redirect' => function ($model) {
                    return $this->createRedirect($model);
                }
            ],
            'delete' => [
                'class' => DeleteAction::class,
                'primaryKeyParam' => $this->primaryKey,
                'redirect' => function ($model) {
                    return $this->deleteRedirect($model);
                }
            ],
            'delete-batch' => [
                'class' => DeleteBatch::class
            ]
        ];
    }
}