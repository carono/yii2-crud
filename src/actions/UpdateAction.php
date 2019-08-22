<?php

namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
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

    public function run()
    {
        $id = \Yii::$app->request->get('id');
        $model = $this->controller->findModel($id, $this->controller->updateClass);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', $this->getMessageOnUpdate($model));
                $url = $this->controller->updateRedirect($model);
                if (Yii::$app->request->isPjax) {
                    return Yii::$app->response->redirect($url, 302, false);
                }
                return $this->controller->redirect($url);
            }
            Yii::$app->session->setFlash('error', Html::errorSummary($model));
        }
        return $this->controller->render($this->view, ['model' => $model]);
    }
}