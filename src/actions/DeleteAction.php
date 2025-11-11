<?php


namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use Yii;
use yii\db\ActiveRecord;

/**
 * Class DeleteAction
 *
 * @package carono\yii2crud\actions
 * @property CrudController $controller
 * @method getMessageOnDelete()
 */
class DeleteAction extends Action
{
    public $softDeleteAttribute = 'deleted_at';
    public $preventAjaxRedirect = false;
    public $messageOnDelete = 'Model deleted';
    public $redirect;

    public function run()
    {
        $model = $this->findModel($this->modelClass);
        $model->delete();
        if ($model->hasErrors() || $this->hasSoftDeleteError($model)) {
            $msg = current($model->getFirstErrors());
            Yii::$app->session->setFlash('error', $msg ?: Yii::t('errors', 'Fail Deleting Model'));
        } else {
            Yii::$app->session->setFlash('success', $this->getMessageOnDelete($model));
        }

        if (Yii::$app->request->isAjax && !$this->preventAjaxRedirect) {
            return $this->redirect($model);
        }

        return $this->redirect($model);
    }

    /**
     * @param ActiveRecord $model
     * @return bool
     */
    public function hasSoftDeleteError($model): bool
    {
        if ($model->hasAttribute($this->softDeleteAttribute)) {
            return empty($model->{$this->softDeleteAttribute});
        }
        return false;
    }
}