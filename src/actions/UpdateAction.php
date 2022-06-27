<?php

namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Yii;

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

    public function run()
    {
        $id = ArrayHelper::getValue($this->params, $this->primaryKeyParam);
        $model = $this->controller->findModel($id, $this->modelClass ?: $this->controller->updateClass);
        $this->trigger(CrudController::EVENT_BEFORE_UPDATE_LOAD, new Event(['data' => [$model]]));
        if ($model->load(Yii::$app->request->post())) {
            $this->trigger(CrudController::EVENT_AFTER_UPDATE_LOAD, new Event(['data' => [$model]]));
            if ($model->save()) {
                $this->trigger(CrudController::EVENT_AFTER_UPDATE, new Event(['data' => [$model]]));
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
                $this->trigger(CrudController::EVENT_ERROR_UPDATE, new Event(['data' => [$model]]));
            }
            Yii::$app->session->setFlash('error', Html::errorSummary($model));
        }
        return $this->render($this->view, ['model' => $model]);
    }
}