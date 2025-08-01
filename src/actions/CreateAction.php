<?php


namespace carono\yii2crud\actions;


use carono\yii2crud\CrudController;
use carono\yii2crud\Event;
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
         * @var ActiveRecord|Model $model
         */
        $class = $this->modelClass ?: ($this->controller->createClass ?: $this->controller->modelClass);
        $model = new $class();
        $this->controller->trigger(CrudController::EVENT_BEFORE_CREATE, new Event(['model' => $model]));
        if ($this->loadDefaultValues && method_exists($model, 'loadDefaultValues')) {
            $model->loadDefaultValues($this->skipIfSet);
        }
        if ($this->loadGetParams && method_exists($model, 'load')) {
            $model->load(\Yii::$app->request->get());
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', $this->getMessageOnCreate($model));
                $this->controller->trigger(CrudController::EVENT_AFTER_CREATE, new Event(['model' => $model]));
                return $this->controller->redirect($this->controller->createRedirect($model));
            } else {
                $this->controller->trigger(CrudController::EVENT_ERROR_CREATE, new Event(['model' => $model]));
            }
            Yii::$app->session->setFlash('error', Html::errorSummary($model));
        }
        return $this->controller->render($this->view, ['model' => $model]);
    }
}