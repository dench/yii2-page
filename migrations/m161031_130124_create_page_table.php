<?php

use yii\db\Migration;

/**
 * Handles the creation of table `page`.
 */
class m161031_130124_create_page_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('page', [
            'id' => $this->primaryKey(),
            'slug' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)
        ]);

        $this->createTable('page_lang', [
            'page_id' => $this->integer()->notNull(),
            'lang_id' => $this->string(3)->notNull(),
            'name' => $this->string()->notNull(),
            'h1' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'keywords' => $this->string(),
            'description' => $this->text(),
            'text' => $this->text()
        ]);

        $this->addPrimaryKey('pk-page_lang', 'page_lang', ['page_id', 'lang_id']);

        $this->addForeignKey('fk-page_lang-page_id', 'page_lang', 'page_id', 'page', 'id', 'CASCADE');

        $this->addForeignKey('fk-page_lang-lang_id', 'page_lang', 'lang_id', 'language', 'id', 'CASCADE', 'CASCADE');

        $this->insert('page', [
            'slug' => 'index',
            'created_at' => time(),
            'updated_at' => time()
        ]);

        $this->batchInsert('page_lang', ['page_id', 'lang_id', 'name', 'h1', 'title'], [
            [1, 'uk', 'Головна', 'Головна', 'Головна'],
            [1, 'ru', 'Главная', 'Главная', 'Главная'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk-page_lang-lang_id', 'page_lang');

        $this->dropForeignKey('fk-page_lang-page_id', 'page_lang');

        $this->dropPrimaryKey('pk-page_lang', 'page_lang');

        $this->dropTable('page_lang');

        $this->dropTable('page');
    }
}
