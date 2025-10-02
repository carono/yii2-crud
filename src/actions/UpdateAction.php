<?php

namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use carono\yii2crud\Event;
use Yii;
use yii\helpers\Html;

/**
 * Class UpdateAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 * @method getMessageOnUpdate(\yii\db\ActiveRecord $model)
 */
class UpdateAction extends Action
{
    public $view = 'update';
    public $messageOnUpdate = 'Model Successful Updated';
    public $redirect;
    public $properties = [];

    public function run()
    {
        $model = $this->findModel($this->modelClass ?: $this->controller->updateClass);
        $this->controller->trigger(CrudController::EVENT_BEFORE_UPDATE_LOAD, new Event(['model' => $model, 'action' => $this]));
        foreach ($this->properties as $property => $value) {
            $model->$property = $value;
        }
        if ($model->load(Yii::$app->request->post())) {
            $this->controller->trigger(CrudController::EVENT_AFTER_UPDATE_LOAD, new Event(['model' => $model, 'action' => $this]));
            if ($model->save()) {
                $this->controller->trigger(CrudController::EVENT_AFTER_UPDATE, new Event(['model' => $model, 'action' => $this]));
                Yii::$app->session->setFlash('success', $this->getMessageOnUpdate($model));
                if ($this->redirect instanceof \Closure) {
                    $url = call_user_func($this->redirect, $model);
                } else {
                    return $this->controller->refresh();
                }
                if (Yii::$app->request->isPjax) {
                    return Yii::$app->response->redirect($url, 302, false);
                }
                return $this->controller->redirect($url);
            } else {
                $this->controller->trigger(CrudController::EVENT_ERROR_UPDATE, new Event(['model' => $model, 'action' => $this]));
            }
            Yii::$app->session->setFlash('error', Html::errorSummary($model));
        }
        return $this->render($this->view, ['model' => $model]);
    }
}