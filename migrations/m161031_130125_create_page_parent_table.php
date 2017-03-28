<?php

use yii\db\Migration;

/**
 * Handles the creation of table `page_parent`.
 */
class m161031_130125_create_page_parent_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('page_parent', [
            'page_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk-page_parent', 'page_parent', ['page_id', 'parent_id']);

        $this->addForeignKey('fk-page_id', 'page_parent', 'page_id', 'page', 'id', 'CASCADE');

        $this->addForeignKey('fk-parent_id', 'page_parent', 'parent_id', 'page', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk-parent_id', 'page_parent');

        $this->dropForeignKey('fk-page_id', 'page_parent');

        $this->dropPrimaryKey('pk-page_parent', 'page_parent');

        $this->dropTable('page_parent');
    }
}
