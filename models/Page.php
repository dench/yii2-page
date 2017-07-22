<?php

namespace dench\page\models;

use Yii;
use dench\image\models\Image;
use dench\sortable\behaviors\SortableBehavior;
use dench\language\behaviors\LanguageBehavior;
use omgdef\multilingual\MultilingualQuery;
use voskobovich\linker\LinkerBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "page".
 *
 * @property integer $id
 * @property string $slug
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $position
 * @property integer $enabled
 * @property integer $image_id
 *
 * Language
 *
 * @property string $name
 * @property string $title
 * @property string $h1
 * @property string $keywords
 * @property string $description
 * @property string $text
 *
 * Relations
 *
 * @property Page $parent
 * @property Page[] $parents
 * @property Page[] $childs
 * @property Image[] $images
 * @property Image[] $imagesAll
 * @property Image $image
 * @property array $imageEnabled
 */
class Page extends ActiveRecord
{
    const DISABLED = 0;
    const ENABLED = 1;

    const TYPE_PAGE = 0;
    const TYPE_CATEGORY = 1;

    private $_imageEnabled = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            LanguageBehavior::className(),
            TimestampBehavior::className(),
            SortableBehavior::className(),
            'slug' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
                'ensureUnique' => true
            ],
            [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'parent_ids' => ['parents'],
                    'image_ids' => [
                        'images',
                        'updater' => [
                            'viaTableAttributesValue' => [
                                'position' => function($updater, $relatedPk, $rowCondition) {
                                    $primaryModel = $updater->getBehavior()->owner;
                                    $image_ids = array_values($primaryModel->image_ids);
                                    return array_search($relatedPk, $image_ids);
                                },
                                'enabled' => function($updater, $relatedPk, $rowCondition) {
                                    $primaryModel = $updater->getBehavior()->owner;
                                    return !empty($primaryModel->imageEnabled[$relatedPk]) ? 1 : 0;
                                },
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'h1', 'title'], 'required'],
            [['slug', 'name', 'h1', 'title', 'keywords'], 'string', 'max' => 255],
            [['description', 'text'], 'string'],
            [['slug', 'name', 'h1', 'title', 'keywords', 'description', 'text'], 'trim'],
            [['position', 'image_id'], 'integer'],
            [['enabled', 'type'], 'boolean'],
            [['enabled'], 'default', 'value' => self::ENABLED],
            [['enabled'], 'in', 'range' => [self::ENABLED, self::DISABLED]],
            [['type'], 'in', 'range' => [self::TYPE_PAGE, self::TYPE_CATEGORY]],
            [['image_ids', 'parent_ids', 'imageEnabled'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slug' => Yii::t('page', 'Slug'),
            'created_at' => Yii::t('page', 'Created'),
            'updated_at' => Yii::t('page', 'Updated'),
            'enabled' => Yii::t('page', 'Enabled'),
            'name' => Yii::t('page', 'Name'),
            'h1' => Yii::t('page', 'H1'),
            'title' => Yii::t('page', 'Title'),
            'keywords' => Yii::t('page', 'Keywords'),
            'description' => Yii::t('page', 'Description'),
            'text' => Yii::t('page', 'Text'),
            'position' => Yii::t('page', 'Position'),
            'type' => Yii::t('page', 'Type'),
            'parent_ids' => Yii::t('page', 'Parent category'),
        ];
    }

    public static function viewPage($id)
    {
        if (is_numeric($id)) {
            $page = self::findOne($id);
        } else {
            $page = self::findOne(['slug' => $id]);
        }
        if ($page === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        Yii::$app->view->params['page'] = $page;
        Yii::$app->view->title = $page->title;
        if ($page->description) {
            Yii::$app->view->registerMetaTag([
                'name' => 'description',
                'content' => $page->description
            ]);
        }
        if ($page->keywords) {
            Yii::$app->view->registerMetaTag([
                'name' => 'keywords',
                'content' => $page->keywords
            ]);
        }
        return $page;
    }

    public static function getList($enabled)
    {
        return ArrayHelper::map(self::find()->andFilterWhere(['enabled' => $enabled])->orderBy('position')->all(), 'id', 'name');
    }

    /**
     * @return MultilingualQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new MultilingualQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if ($this->id == 1) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id'])->viaTable('page_parent', ['page_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(self::className(), ['id' => 'parent_id'])->viaTable('page_parent', ['page_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
        return $this->hasMany(self::className(), ['id' => 'page_id'])->viaTable('page_parent', ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        $name = $this->tableName();

        return $this->hasMany(Image::className(), ['id' => 'image_id'])
            ->viaTable($name . '_image', [$name . '_id' => 'id'])
            ->leftJoin($name . '_image', 'id=image_id')
            ->where([$name . '_image.' . $name . '_id' => $this->id])
            ->andFilterWhere([$name . '_image.enabled' => true])
            ->orderBy([$name . '_image.position' => SORT_ASC])
            ->indexBy('id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImagesAll()
    {
        $name = $this->tableName();

        return $this->hasMany(Image::className(), ['id' => 'image_id'])
            ->viaTable($name . '_image', [$name . '_id' => 'id'])
            ->leftJoin($name . '_image', 'id=image_id')
            ->where([$name . '_image.' . $name . '_id' => $this->id])
            ->orderBy([$name . '_image.position' => SORT_ASC])
            ->indexBy('id');
    }

    public function getImageEnabled()
    {
        if ($this->_imageEnabled != null) {
            return $this->_imageEnabled;
        }

        $name = $this->tableName();

        return $this->_imageEnabled = (new \yii\db\Query())
            ->select(['enabled'])
            ->from($name . '_image')
            ->where([$name . '_id' => $this->id])
            ->indexBy('image_id')
            ->column();
    }

    public function setImageEnabled($value)
    {
        $this->_imageEnabled = $value;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::className(), ['id' => 'image_id']);
    }
}