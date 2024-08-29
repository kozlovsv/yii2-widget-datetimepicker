<?php

namespace kozlovsv\datepicker;

use Exception;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\MaskedInput;

class DateTimePicker extends MaskedInput
{
    /**
     * @var array the event handlers for the underlying Bootstrap Switch 3 input JS plugin.
     */
    public $clientEvents = [];

    /**
     * @var string the size of the input ('lg', 'md', 'sm', 'xs')
     */
    public $size;

    /**
     * @var array HTML attributes to render on the container if its used as a component.
     */
    public $containerOptions = [];

    /**
     * @var string the template to render the input. By default, renders as a component, you can render a simple
     * input field without pickup and/or reset buttons by modifying the template to `{input}`. `{button}` must exist for
     * a component type of datepicker. The following template is invalid `{input}{reset}` and will be treated as `{input}`
     */
    public $template = "{input}";

    /**
     * @var string the icon to use on the reset button
     */
    public $resetButtonIcon = 'glyphicon glyphicon-remove';

    /**
     * @var string the icon to use on the pickup button. Defaults to `glyphicon-th`. Other uses are `glyphicon-time` and
     * `glyphicon-calendar`.
     */
    public $pickButtonIcon = 'glyphicon glyphicon-th';

    /**
     * @var bool whether to render the input as an inline calendar
     */
    public $inline = false;

    public $mask = '99.99.9999';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset($this->options['autocomplete'])) $this->options['autocomplete'] = 'off';
        Html::addCssClass($this->containerOptions, 'input-group date');
        Html::addCssClass($this->options, 'form-control');
        if ($this->size !== null) {
            $size = 'input-' . $this->size;
            Html::addCssClass($this->options, $size);
            Html::addCssClass($this->containerOptions, $size);
        }
        if ($this->inline) {
            $this->clientOptions['linkField'] = $this->options['id'];
            Html::removeCssClass($this->containerOptions, 'date');
            Html::removeCssClass($this->containerOptions, 'input-group');
            Html::addCssClass($this->options, 'text-center');
        }

        $value = $this->model->{$this->attribute};
        if ($this->model->{$this->attribute} != null) {
            $format = $this->getDateFormat();
            try {
                $this->model->{$this->attribute} = Yii::$app->formatter->format($value, $format);
            } catch (Exception $e) {
                $this->model->{$this->attribute} = null;
            }
        }
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function run()
    {
        $input = MaskedInput::widget([
            'mask' => $this->mask,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'options' => $this->options,
            'clientOptions' => $this->clientOptions,
        ]);

        if (!$this->inline) {
            $resetIcon = Html::tag('span', '', ['class' => $this->resetButtonIcon]);
            $pickIcon = Html::tag('span', '', ['class' => $this->pickButtonIcon]);
            $resetAddon = Html::tag('span', $resetIcon, ['class' => 'input-group-addon']);
            $pickerAddon = Html::tag('span', $pickIcon, ['class' => 'input-group-addon']);
        } else {
            $resetAddon = $pickerAddon = '';
        }

        if (strpos($this->template, '{button}') !== false || $this->inline) {
            $input = Html::tag(
                'div', strtr($this->template, ['{input}' => $input, '{reset}' => $resetAddon, '{button}' => $pickerAddon]), $this->containerOptions
            );
        }
        echo $input;
        $this->registerClientScript();
    }

    /**
     * Registers required script for the plugin to work as a DateTimePicker
     */
    public function registerClientScript()
    {
        $js = [];
        $view = $this->getView();
        DateTimePickerAsset::register($view);
        $id = $this->options['id'];
        $selector = "jQuery('#$id')";

        if (strpos($this->template, '{button}') !== false || $this->inline) {
            $selector .= ".parent()";
        }

        $options = !empty($this->clientOptions) ? Json::encode($this->clientOptions) : '';

        $js[] = "$selector.datetimepicker($options);";
        $js[] = "{$selector}.on('keydown', function(event) {{$selector}.data('DateTimePicker').hide(); } );";

        if ($this->inline) {
            $js[] = "$selector.find('.datetimepicker-inline').addClass('center-block');";
            $js[] = "$selector.find('table.table-condensed').attr('align','center').css('margin','auto');";
        }
        if (!empty($this->clientEvents)) {
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "$selector.on('$event', $handler);";
            }
        }
        $view->registerJs(implode("\n", $js));
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        if (isset($this->clientOptions['pickDate']) && $this->clientOptions['pickDate'] === false)
            return 'time';
        if (!empty($this->clientOptions['pickTime']))
            return 'datetime';
        return 'date';
    }

}
