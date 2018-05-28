<?php

use yii\db\Migration;

/**
 * Handles the creation of table `page_file`.
 */
class m180528_130711_create_page_file_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('page_file', [
            'page_id' => $this->integer()->notNull(),
            'file_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'position' => $this->integer()->notNull()->defaultValue(0),
            'enabled' => $this->boolean()->notNull()->defaultValue(1),
        ], $tableOptions);

        $this->addPrimaryKey('pk-page_file', 'page_file', ['page_id', 'file_id']);

        $this->addForeignKey('fk-page_file-page_id', 'page_file', 'page_id', 'page', 'id', 'CASCADE');

        $this->addForeignKey('fk-page_file-file_id', 'page_file', 'file_id', 'file', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk-page_file-file_id', 'page_file');

        $this->dropForeignKey('fk-page_file-page_id', 'page_file');

        $this->dropPrimaryKey('pk-page_file', 'page_file');

        $this->dropTable('page_file');
    }
}
