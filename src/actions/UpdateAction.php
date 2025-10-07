<?php

namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use carono\yii2crud\Event;
use Yii;
use yii\helpers\Html;
use yii\db\ActiveRecord;
use Closure;

/**
 * Class UpdateAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 * @method getMessageOnUpdate(ActiveRecord $model)
 */
class UpdateAction extends Action
{
    public $view = 'update';
    public $messageOnUpdate = 'Model Successful Updated';
    public $redirect;
    public $properties = [];
    public $standAloneSave = false;

    public function renderView($model)
    {
        if ($this->view) {
            return $this->render($this->view, compact('model'));
        }
        return $this->redirect($model);
    }

    public function run()
    {
        $model = $this->findModel($this->modelClass ?: $this->controller->updateClass);

        $this->applyProperties($model);

        $this->triggerBeforeUpdateLoad($model);
        if (!$model->load(Yii::$app->request->post()) && !$this->standAloneSave) {
            return $this->renderView($model);
        }
        $this->triggerAfterUpdateLoad($model);

        if (!$model->save()) {
            $this->handleError($model);
            return $this->renderView($model);
        }

        $this->handleSuccess($model);

        return $this->redirect($model);
    }

    private function triggerBeforeUpdateLoad(ActiveRecord $model): void
    {
        $this->controller->trigger(CrudController::EVENT_BEFORE_UPDATE_LOAD, new Event([
            'model' => $model,
            'action' => $this
        ]));
    }

    private function applyProperties(ActiveRecord $model): void
    {
        foreach ($this->properties as $property => $value) {
            $model->$property = $value;
        }
    }

    private function triggerAfterUpdateLoad(ActiveRecord $model): void
    {
        $this->controller->trigger(CrudController::EVENT_AFTER_UPDATE_LOAD, new Event([
            'model' => $model,
            'action' => $this
        ]));
    }

    private function handleError(ActiveRecord $model): void
    {
        $this->controller->trigger(CrudController::EVENT_ERROR_UPDATE, new Event([
            'model' => $model,
            'action' => $this
        ]));

        Yii::$app->session->setFlash('error', Html::errorSummary($model));
    }

    private function handleSuccess(ActiveRecord $model)
    {
        $this->controller->trigger(CrudController::EVENT_AFTER_UPDATE, new Event([
            'model' => $model,
            'action' => $this
        ]));

        Yii::$app->session->setFlash('success', $this->getMessageOnUpdate($model));
    }
}