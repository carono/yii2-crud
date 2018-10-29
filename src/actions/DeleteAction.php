<?php


namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use Yii;

/**
 * Class DeleteAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 */
class DeleteAction extends Action
{
    public function run($id)
    {
        $model = $this->controller->findModel($id);
        $model->delete();
        if ($model->hasErrors() || ($model->hasAttribute('deleted_at') && !$model->deleted_at)) {
            $msg = current($model->getFirstErrors());
            Yii::$app->session->setFlash('error', $msg ?: Yii::t('errors', 'Fail Deleting Model'));
        } else {
            Yii::$app->session->setFlash('success', Yii::t('messages', 'Model deleted'));
        }

        if (Yii::$app->request->isAjax) {
            return Yii::$app->response->redirect(Yii::$app->request->referrer, 302, false);
        }

        return $this->controller->redirect(['index']);
    }
}