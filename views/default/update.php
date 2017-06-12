<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model dench\page\models\Page */
/* @var $images dench\image\models\Image[] */

$this->title = Yii::t('page', 'Update');
$this->params['breadcrumbs'][] = ['label' => Yii::t('page', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="page-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'images' => $images,
    ]) ?>

</div>
