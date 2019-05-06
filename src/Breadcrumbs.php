<?php


namespace carono\yii2crud;


use yii\helpers\Inflector;

class Breadcrumbs
{
    public static $crumbsNamespace = 'app\breadcrumbs';

    /**
     * @param \yii\base\Action $action
     * @param array $params
     * @return array|mixed
     */
    public static function formCrumbs($action, $params)
    {
        $arr = explode('/', Inflector::camel2id($action->controller->getUniqueId()));
        $arr[\count($arr) - 1] = Inflector::camelize(ucfirst($arr[\count($arr) - 1]));
        $class = static::$crumbsNamespace . '\\' . implode('\\', $arr) . 'Crumbs';
        $name = 'crumb' . Inflector::camelize($action->id);
        if (method_exists($class, $name)) {
            $reflectionMethod = new \ReflectionMethod($class, $name);
            $data = [];
            foreach ($reflectionMethod->getParameters() as $p) {
                $data[] = $params[$p->getName()] ?? null;
            }
            return array_filter(\call_user_func_array([$class, $name], $data));
        }

        return [];
    }
}