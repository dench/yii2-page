<?php

use yii\db\Migration;

/**
 * Handles the creation of table `page_image`.
 */
class m170311_110757_create_page_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('page_image', [
            'page_id' => $this->integer()->notNull(),
            'image_id' => $this->integer()->notNull(),
            'position' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->addPrimaryKey('pk-page_image', 'page_image', ['page_id', 'image_id']);
        
        $this->addForeignKey('fk-page_image-page_id', 'page_image', 'page_id', 'page', 'id', 'CASCADE');
        
        $this->addForeignKey('fk-page_image-image_id', 'page_image', 'image_id', 'image', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk-page_image-image_id', 'page_image');
        
        $this->dropForeignKey('fk-page_image-page_id', 'page_image');
        
        $this->dropPrimaryKey('pk-page_image', 'page_image');
        
        $this->dropTable('page_image');
    }
}
