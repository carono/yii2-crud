<?php


namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use Yii;

/**
 * Class DeleteBatch
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 */
class DeleteBatch extends Action
{
    public function run()
    {
        $ids = (array)Yii::$app->request->post('ids');
        $errors = [];
        foreach ($ids as $id) {
            $this->controller->runAction('delete', ['id' => $id]);
            if (Yii::$app->session->hasFlash('error')) {
                $errors[] = Yii::$app->session->getFlash('error', null, true);
            }
        }
        if (!empty($errors)) {
            Yii::$app->session->removeFlash('success');
            Yii::$app->session->setFlash('error', implode('<br>', $errors));
        }
        if (Yii::$app->request->isAjax) {
            return Yii::$app->response->redirect(Yii::$app->request->referrer, 302, false);
        }

        return $this->controller->redirect($this->controller->deleteBatchRedirect());
    }
}