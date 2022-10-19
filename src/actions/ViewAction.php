<?php


namespace carono\yii2crud\actions;


use carono\yii2crud\CrudController;
use Yii;

/**
 * Class ViewAction
 *
 * @package app\actions
 * @property CrudController $controller
 */
class ViewAction extends Action
{
    public $view = 'view';
    public $updateIfPost = true;
    public $updateAction = 'update';

    public function run()
    {
        $model = $this->findModel($this->modelClass ?: $this->controller->viewClass);
        if ($this->updateIfPost && Yii::$app->request->isPost) {
            return $this->controller->runAction($this->updateAction, $this->params);
        }
        return $this->controller->render($this->view, ['model' => $model]);
    }
}