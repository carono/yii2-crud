<?php


namespace carono\yii2crud;


use yii\helpers\Inflector;

class Breadcrumbs
{
    public static $crumbsNamespace = 'app\breadcrumbs';

    /**
     * @param $action
     * @return string
     */
    protected static function actionToBreadcrumbClass($action)
    {
        $arr = explode('/', Inflector::camel2id($action->controller->getUniqueId()));
        $arr[\count($arr) - 1] = Inflector::camelize(ucfirst($arr[\count($arr) - 1]));
        return static::$crumbsNamespace . '\\' . implode('\\', $arr) . 'Crumbs';
    }

    protected static function actionToBreadcrumbMethod($action)
    {
        return 'crumb' . Inflector::camelize($action->id);
    }

    /**
     * @param \yii\base\Action $action
     * @param array $params
     * @return array|mixed
     */
    public static function formCrumbs($action, $params)
    {
        $class = static::actionToBreadcrumbClass($action);
        $method = static::actionToBreadcrumbMethod($action);

        if (class_exists($class) && method_exists($class, $method)) {
            $reflectionMethod = new \ReflectionMethod($class, $method);
            $data = [];
            foreach ($reflectionMethod->getParameters() as $p) {
                $data[] = $params[$p->getName()] ?? null;
            }
            return array_filter(\call_user_func_array([$class, $method], $data));
        }

        return [];
    }
}