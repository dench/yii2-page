<?php

use yii\db\Migration;

/**
 * Handles adding short to table `page`.
 */
class m180218_124809_add_short_column_to_page_lang_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('page_lang', 'short', 'text');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('page_lang', 'short');
    }
}
