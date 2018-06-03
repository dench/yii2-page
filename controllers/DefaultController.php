<?php

namespace dench\page\controllers;

use dench\image\models\File;
use dench\page\models\PageSearch;
use dench\sortable\actions\SortingAction;
use Yii;
use dench\image\models\Image;
use dench\page\models\Page;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DefaultController implements the CRUD actions for Page model.
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'sorting' => [
                'class' => SortingAction::className(),
                'query' => Page::find(),
            ],
        ];
    }

    /**
     * Lists all Page models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PageSearch(['all' => Yii::$app->request->get('all')]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Page model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Page model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Page();

        $model->loadDefaultValues();

        if ($parent_id = Yii::$app->request->get('parent_id')) {
            $model->parent_ids = [$parent_id];
        }

        $images = [];
        $files = [];

        if ($post = Yii::$app->request->post()) {
            /** @var Image[] $images */
            $images = [];
            $image_ids = isset($post['Image']) ? $post['Image'] : [];
            foreach ($image_ids as $key => $image) {
                $images[$key] = Image::findOne($key);
            }
            if ($images) {
                Model::loadMultiple($images, $post);
            } else {
                $model->image_ids = [];
            }

            /** @var File[] $files */
            $files = [];
            $file_ids = isset($post['File']) ? $post['File'] : [];
            foreach ($file_ids as $key => $file) {
                $files[$key] = File::findOne($key);
            }
            if ($files) {
                Model::loadMultiple($files, $post);
            } else {
                $model->file_ids = [];
            }

            $model->load($post);

            $error = [];
            if (!$model->validate()) $error['model'] = $model->errors;
            foreach ($images as $key => $image) {
                if (!$image->validate()) $error['image'][$key] = $image->errors;
            }
            if (empty($error)) {
                foreach ($images as $key => $image) {
                    $image->save(false);
                }
                foreach ($files as $key => $file) {
                    $file->save(false);
                }
                if (!$model->image_id && $images) {
                    $image = current($images);
                    $model->image_id = $image->id;
                }
                $model->save(false);
                Yii::$app->session->setFlash('success', Yii::t('page', 'Information added successfully.'));
                if (isset($model->parent)) {
                    return $this->redirect(['index', 'PageSearch[parent_id]' => $model->parent->id]);
                } else {
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'images' => $images,
            'files' => $files,
        ]);
    }

    /**
     * Updates an existing Page model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModelMulti($id);

        $images = $model->imagesAll;
        $files = $model->filesAll;

        if ($post = Yii::$app->request->post()) {
            $model->load($post);
            $old_ids = ArrayHelper::map($images, 'id', 'id');
            /** @var Image[] $images */
            $images = [];
            $image_ids = isset($post['Image']) ? $post['Image'] : [];
            $new_ids = [];
            foreach ($image_ids as $key => $image) {
                $images[$key] = Image::findOne($key);
                $new_ids[$key] = $key;
            }
            if ($images) {
                Model::loadMultiple($images, $post);
            } else {
                $model->image_ids = [];
            }
            $deleted_ids = array_diff($old_ids, $new_ids);

            //$f_old_ids = ArrayHelper::map($files, 'id', 'id');
            /** @var File[] $files */
            $files = [];
            $file_ids = isset($post['File']) ? $post['File'] : [];
            print_r($file_ids);
            $f_new_ids = [];
            foreach ($file_ids as $key => $file) {
                $files[$key] = File::findOne($key);
                $f_new_ids[$key] = $key;
            }
            if ($files) {
                Model::loadMultiple($files, $post);
            } else {
                $model->file_ids = [];
            }
            //$f_deleted_ids = array_diff($f_old_ids, $f_new_ids);

            $error = [];
            if (!$model->validate()) $error['model'] = $model->errors;
            foreach ($images as $key => $image) {
                if (!$image->validate()) $error['image'][$key] = $image->errors;
            }
            if (empty($error)) {
                foreach ($images as $key => $image) {
                    $image->save(false);
                }
                foreach ($deleted_ids as $d_id) {
                    if ($deleted_image = Image::findOne($d_id)) {
                        $deleted_image->delete();
                    }
                }
                if (!$model->image_id && $images) {
                    $image = current($images);
                    $model->image_id = $image->id;
                }
                foreach ($files as $key => $file) {
                    $file->save(false);
                }
                $model->save(false);
                Yii::$app->session->setFlash('success', Yii::t('page', 'Information has been saved successfully.'));
                if (isset($model->parent)) {
                    return $this->redirect(['index', 'PageSearch[parent_id]' => $model->parent->id]);
                } else {
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'images' => $images,
            'files' => $files,
        ]);
    }

    /**
     * Deletes an existing Page model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Page model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Page the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Page::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('page', 'The requested page does not exist.'));
        }
    }

    /**
     * Finds the Page model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Page|\yii\db\ActiveRecord
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModelMulti($id)
    {
        if (($model = Page::find()->where(['id' => $id])->multilingual()->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('page', 'The requested page does not exist.'));
        }
    }
}
