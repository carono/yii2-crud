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
 * @method getMessageOnCreate(\yii\db\ActiveRecord $model)
 */
class CreateAction extends Action
{
    public $view = 'create';
    public $loadDefaultValues = true;
    public $loadGetParams = true;
    public $skipIfSet = true;
    public $messageOnCreate = 'Model Successful Created';

    public function run()
    {
        /**
         * @var ActiveRecord $class
         * @var ActiveRecord $model
         */
        $class = $this->modelClass ?: $this->controller->createClass;
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
                Yii::$app->session->setFlash('success', $this->getMessageOnCreate($model));
                return $this->controller->redirect($this->controller->createRedirect($model));
            }
            Yii::$app->session->setFlash('error', Html::errorSummary($model));
        }
        return $this->controller->render($this->view, ['model' => $model]);
    }
}