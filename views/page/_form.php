<?php

use app\helpers\CategoryHelper;
use app\widgets\ImageUpload;
use dench\language\models\Language;
use dosamigos\ckeditor\CKEditor;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model dench\page\models\Page */
/* @var $form yii\widgets\ActiveForm */
/* @var $images app\models\Image */

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

$this->registerJs($js);
?>

<div class="page-form">

    <?php $form = ActiveForm::begin(); ?>

    <ul class="nav nav-tabs">
        <?php foreach (Language::suffixList() as $suffix => $name) : ?>
            <li class="nav-item<?= empty($suffix) ? ' active': '' ?>"><a href="#lang<?= $suffix ?>" class="nav-link" data-toggle="tab"><?= $name ?></a></li>
        <?php endforeach; ?>
        <li class="nav-item"><a href="#tab-main" class="nav-link" data-toggle="tab"><?= Yii::t('app', 'Main') ?></a></li>
        <li class="nav-item"><a href="#tab-images" class="nav-link" data-toggle="tab"><?= Yii::t('app', 'Images') ?></a></li>
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
                    'clientOptions' => [
                        'customConfig' => '/js/ckeditor.js',
                        'language' => Yii::$app->language,
                        'allowedContent' => true,
                    ]
                ]) ?>
            </div>
        <?php endforeach; ?>

        <div class="tab-pane fade" id="tab-main">
            <?= $form->field($model, 'parent_ids')->dropDownList(CategoryHelper::getTree(true), ['multiple' => true, 'options' => [$model->id => ['disabled' => true]]]) ?>
            <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'enabled')->checkbox() ?>
        </div>

        <div class="tab-pane fade" id="tab-images">
            <?= ImageUpload::widget([
                'images' => $images,
                'modelInputName' => $model->formName() . '[image_ids]',
            ]) ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>