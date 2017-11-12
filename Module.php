<?php

namespace dench\page;

use Yii;

/**
 * Class Module
 *
 * @package dench\page
 */
class Module extends \yii\base\Module
{
    /**
     * @var string the namespace that controller classes are in
     */
    public $controllerNamespace = 'dench\page\controllers';

    public function init()
    {
        parent::init();

        Yii::$app->i18n->translations['page'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath' => '@vendor/dench/yii2-page/messages',
        ];
    }
}