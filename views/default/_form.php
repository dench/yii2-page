<?php

use dench\image\helpers\ImageHelper;
use dench\image\widgets\ImageUpload;
use dench\language\models\Language;
use dench\page\helpers\CategoryHelper;
use dench\page\models\Page;
use dosamigos\ckeditor\CKEditor;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model dench\page\models\Page */
/* @var $form yii\widgets\ActiveForm */
/* @var $images dench\image\models\Image */

$js = '';

foreach (Language::suffixList() as $suffix => $name) {

$js .= "
var name" . $suffix . " = '';
$('#page-name" . $suffix . "').focus(function(){
    name" . $suffix . " = $(this).val();
}).blur(function(){
    var h1 = $('#page-h1" . $suffix . "');
    if (h1.val() == name" . $suffix . ") {
        h1.val($(this).val());
    }
    var title = $('#page-title" . $suffix . "');
    if (title.val() == name" . $suffix . ") {
        title.val($(this).val());
    }
});";

}

$path = ImageHelper::generatePath('fill');

$sizes = [];
foreach (Yii::$app->params['image']['size'] as $key => $size) {
    $sizes[] = "['" . $size['width'] . " x " . $size['height'] . " / " . $size['method'] . "', '" . $key . "']";
}
$size_items = implode($sizes, ', ');

$js .= <<<JS
CKEDITOR.on('dialogDefinition', function(ev) {

    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;
    
    dialogDefinition.resizable = CKEDITOR.DIALOG_RESIZE_NONE;

    if (dialogName == 'image') {
        dialogDefinition.addContents({
            id: 'Insert',
            label: 'Insert Image',
            elements: [
                {
                    type: 'select',
                    label: 'Size',
                    items: [{$size_items}],
                    labelStyle: 'display: none'
                },
                {
                    type: 'html',
                    html: '<div class="images-data"></div>'
                }
            ]
        });
        var oldOnShow = dialogDefinition.onShow;
        var newOnShow = function () {
            var html = $('<div>').addClass('images-data');
            $('#tab-images').find('img').each(function(){
                var img = $(this);
                var imgId = img.next('input').val();
                var imgAlt = img.next('input').next('.input-group').find('input').val();
                html.append($(this).clone().click(function(){
                    var size = $('div[name="Insert"]').find('select').val();
                    var imgSrc = $(this).attr('src').replace('/fill/', '/' + size + '/');
                    ev.editor.insertHtml('<img src="' + imgSrc + '" alt="' + imgAlt + '" data-id="' + imgId + '">');
                    CKEDITOR.dialog.getCurrent().hide();
                }));
            });
            html.append('<style>.images-data { max-height: 377px; white-space: normal; } .images-data img { border: 1px solid #CCC; padding: 2px; margin: 3px; width: 113px; cursor: pointer; } .images-data img:hover { border: 1px solid #666; }</style>');
            $('.images-data').html(html);
        }
        dialogDefinition.onShow = function() {
            oldOnShow.call(this, arguments);
            newOnShow.call(this, arguments);
        };
    }
});
JS;

$js .= <<<JS
    $('#pageform').submit(function(event){
        if (event.originalEvent) {
            $('[id^="pagetext"]').each(function(){
                var iD;
                var start;
                var end;
                var img;
                var str = $(this).val();
                var str2;
                var dataId;
                var alt;
                var name;
                $(document).find('.file-preview img').each(function(){
                    iD = $(this).next().val();
                    dataId = str.indexOf('data-id="' + iD + '"');
                    if (dataId > 0) {
                        alt = $(this).next().next().find('input').val();
                        name = $(this).next().next().next().find('input').val();
                        
                        img = str.lastIndexOf('<', dataId);
                        
                        start = str.indexOf('alt="', img)+5;
                        end = str.indexOf('"', start);
                        str2 = str.slice(0, start) + alt + str.slice(end);
                        str = str2;
                        
                        start = str.indexOf('src="', img)+5;
                        end = str.indexOf('"', start);
                        start = str.lastIndexOf('/', end)+1;
                        end = str.lastIndexOf('.', end);
                        str2 = str.slice(0, start) + name + str.slice(end);
                        str = str2;
                    }
                });
                eval('CKEDITOR.instances.' + $(this).attr('id') + '.setData(str);');
            });
        }
        return true;
    });
JS;

$this->registerJs($js);
?>

<div class="page-form">

    <?php $form = ActiveForm::begin(['id' => 'pageform']); ?>

    <ul class="nav nav-tabs">
        <?php foreach (Language::suffixList() as $suffix => $name) : ?>
            <li class="nav-item<?= empty($suffix) ? ' active': '' ?>"><a href="#lang<?= $suffix ?>" class="nav-link" data-toggle="tab"><?= $name ?></a></li>
        <?php endforeach; ?>
        <li class="nav-item"><a href="#tab-main" class="nav-link" data-toggle="tab"><?= Yii::t('page', 'Main') ?></a></li>
        <li class="nav-item"><a href="#tab-images" class="nav-link" data-toggle="tab"><?= Yii::t('page', 'Images') ?></a></li>
    </ul>

    <div class="tab-content">
        <?php foreach (Language::suffixList() as $suffix => $name) : ?>
            <div class="tab-pane fade<?php if (empty($suffix)) echo ' in active'; ?>" id="lang<?= $suffix ?>">
                <?= $form->field($model, 'name' . $suffix)->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'h1' . $suffix)->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'title' . $suffix)->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'keywords' . $suffix)->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'description' . $suffix)->textarea() ?>
                <?= $form->field($model, 'text' . $suffix)->widget(CKEditor::className(), [
                    'preset' => 'full',
                    'options' => [
                        'id' => 'pagetext' . $suffix,
                    ],
                    'clientOptions' => [
                        'customConfig' => '/js/ckeditor.js?' . time(),
                        'language' => Yii::$app->language,
                        'allowedContent' => true,
                    ]
                ]) ?>
            </div>
        <?php endforeach; ?>

        <div class="tab-pane fade" id="tab-main">
            <?= $form->field($model, 'parent_ids')->widget(Select2::classname(), [
                'data' => CategoryHelper::getTree(true),
                'options' => [
                    'placeholder' => Yii::t('page', 'Select...'),
                    'multiple' => true,
                    'options' => [
                        $model->id => ['disabled' => true],
                    ]
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'showToggleAll' => false,
            ]); ?>
            <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'type')->dropDownList([
                Page::TYPE_PAGE => Yii::t('page', 'Page'),
                Page::TYPE_CATEGORY => Yii::t('page', 'Category'),
            ]) ?>
            <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'enabled')->checkbox() ?>
        </div>

        <div class="tab-pane fade" id="tab-images">
            <?= ImageUpload::widget([
                'images' => $images,
                'image_id' => $model->image_id,
                'col' => 'col-sm-4 col-md-3',
                'size' => 'fill',
                'imageEnabled' => $model->imageEnabled,
                'label' => null,
            ]) ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('page', 'Create') : Yii::t('page', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
