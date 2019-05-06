<?php

namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use yii\base\Action;
use yii\helpers\Html;
use Yii;

/**
 * Class UpdateAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 */
class UpdateAction extends Action
{
    public $view = 'update';

    public function run()
    {
        $id = \Yii::$app->request->get('id');
        $model = $this->controller->findModel($id, $this->controller->updateClass);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Model Successful Updated'));
                $url = $this->controller->updateRedirect($model);
                if (Yii::$app->request->isPjax) {
                    return Yii::$app->response->redirect($url, 302, false);
                }
                return $this->controller->redirect($url);
            }
            Yii::$app->session->setFlash('error', Html::errorSummary($model));
        }
        return $this->controller->render($this->controller->updateView ?: $this->view, ['model' => $model]);
    }
}