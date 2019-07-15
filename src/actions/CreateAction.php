<?php


namespace carono\yii2crud\actions;


use carono\yii2crud\CrudController;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * Class CreateAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 */
class CreateAction extends Action
{
    public $view = 'create';
    public $loadDefaultValues = true;
    public $loadGetParams = true;
    public $skipIfSet = true;

    public function run()
    {
        /**
         * @var ActiveRecord $class
         * @var ActiveRecord $model
         */
        $class = $this->controller->createClass ?: $this->controller->modelClass;
        $model = new $class();
        $this->controller->beforeCreate($model);
        if ($this->loadDefaultValues) {
            $model->loadDefaultValues($this->skipIfSet);
        }
        if ($this->loadGetParams) {
            $model->load(\Yii::$app->request->get());
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Model Successful Created'));
                return $this->controller->redirect($this->controller->createRedirect($model));
            }
            Yii::$app->session->setFlash('error', Html::errorSummary($model));
        }
        return $this->controller->render($this->view, ['model' => $model]);
    }
}