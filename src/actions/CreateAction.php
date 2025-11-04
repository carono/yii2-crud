<?php

namespace carono\yii2crud\actions;

use carono\yii2crud\CrudController;
use carono\yii2crud\Event;
use Closure;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\Request;

/**
 * Class CreateAction
 *
 * @property CrudController $controller
 * @method getMessageOnCreate(ActiveRecord $model)
 */
class CreateAction extends Action
{
    public $view = 'create';
    public $loadDefaultValues = true;
    public $loadGetParams = true;
    public $loadGetFormName = null;
    public $skipIfSet = true;
    public $redirect;
    public $messageOnCreate = 'Model Successful Created';

    public function run()
    {
        $class = $this->modelClass
            ?? $this->controller->createClass
            ?? $this->controller->modelClass;

        /** @var ActiveRecord|Model $model */
        $model = new $class();

        $this->controller->trigger(CrudController::EVENT_BEFORE_CREATE, new Event(['model' => $model, 'action' => $this]));

        $this->loadDefaultValues($model);
        $this->loadGetParams($model);

        if ($this->handlePostRequest($model)) {
            $this->handleSuccessfulSave($model);
            return $this->redirect($model);
        }

        return $this->renderView($model);
    }

    public function renderView($model)
    {
        if ($this->view) {
            return $this->render($this->view, compact('model'));
        }
        return $this->redirect($model);
    }

    /**
     * Загружает значения по умолчанию для модели
     */
    protected function loadDefaultValues($model): void
    {
        if ($this->loadDefaultValues && method_exists($model, 'loadDefaultValues')) {
            $model->loadDefaultValues($this->skipIfSet);
        }
    }

    /**
     * Загружает параметры GET запроса в модель
     */
    protected function loadGetParams($model): void
    {
        if ($this->loadGetParams && method_exists($model, 'load')) {
            /** @var Request $request */
            $request = Yii::$app->request;
            $model->load($request->get(), $this->loadGetFormName);
        }
    }

    /**
     * Обрабатывает POST запрос и сохраняет модель
     */
    protected function handlePostRequest($model): bool
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }

        if (!$model->load(Yii::$app->request->post())) {
            return false;
        }

        if ($model->save()) {
            return true;
        }

        $this->controller->trigger(CrudController::EVENT_ERROR_CREATE, new Event(['model' => $model, 'action' => $this]));
        Yii::$app->session->setFlash('error', Html::errorSummary($model));

        return false;
    }

    /**
     * Обрабатывает успешное сохранение модели
     */
    protected function handleSuccessfulSave($model)
    {
        Yii::$app->session->setFlash('success', $this->getMessageOnCreate($model));
        $this->controller->trigger(CrudController::EVENT_AFTER_CREATE, new Event(['model' => $model, 'action' => $this]));
    }
}