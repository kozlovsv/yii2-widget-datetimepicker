<?php

namespace kozlovsv\datepicker;

use yii\web\AssetBundle;

/**
 * @author Ilya Norkin
 */
class DateTimePickerAsset extends AssetBundle
{

    public $sourcePath = '/assets';

    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';
        if (empty(YII_DEBUG)) {
            $this->js[] = 'js/moment-with-locales.min.js';
            $this->js[] = 'js/bootstrap-datetimepicker.min.js';
            $this->css[] = 'css/bootstrap-datetimepicker.min.css';
        } else {
            $this->js[] = 'js/moment-with-locales.min.js';
            $this->js[] = 'js/bootstrap-datetimepicker.js';
            $this->css[] = 'css/bootstrap-datetimepicker.css';
        }
    }

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

}
