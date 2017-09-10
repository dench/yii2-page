<?php

use dench\sortable\grid\SortableColumn;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel dench\page\models\Page */
/* @var $dataProvider yii\data\ActiveDataProvider */

if (isset($dataProvider->models[0]->parent)) {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('page', 'Pages'), 'url' => ['index']];
    if (isset($dataProvider->models[0]->parent->parent)) {
        $this->params['breadcrumbs'][] = ['label' => $dataProvider->models[0]->parent->parent->name, 'url' => ['index', 'PageSearch[parent_id]' => $dataProvider->models[0]->parent->parent->id]];
    }
    $this->title = $dataProvider->models[0]->parent->name;
} else {
    $this->title = Yii::t('page', 'Pages');
}
$this->params['breadcrumbs'][] = $this->title;

if (!Yii::$app->request->get('all') && $dataProvider->totalCount > $dataProvider->count) {
    $showAll = Html::a(Yii::t('app', 'Show all'), Url::current(['all' => 1]));
} else {
    $showAll = '';
}
?>
<div class="page-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('page', 'Create Page'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model, $key, $index, $grid) {
            return [
                'data-position' => $model->position,
            ];
        },
        'layout' => "{summary}\n{$showAll}\n{items}\n{pager}",
        'columns' => [
            [
                'class' => SortableColumn::className(),
            ],
            [
                'attribute' => 'name',
                'content' => function($data) {
                    if ($data->type) {
                        return Html::a('<i class="glyphicon glyphicon-folder-open"></i> ' . $data->name, ['index', 'PageSearch[parent_id]' => $data->id]);
                    } else {
                        return $data->name;
                    }
                }
            ],
            'slug',
            'created_at:date',
            [
                'attribute' => 'enabled',
                'filter' => [
                    Yii::t('app', 'Disabled'),
                    Yii::t('app', 'Enabled'),
                ],
                'content' => function($model, $key, $index, $column){
                    if ($model->enabled) {
                        $class = 'glyphicon glyphicon-ok';
                    } else {
                        $class = '';
                    }
                    return Html::tag('i', '', ['class' => $class]);
                },
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
        'options' => [
            'data' => [
                'sortable' => 1,
                'sortable-url' => Url::to(['sorting']),
            ]
        ],
    ]); ?>
</div>
