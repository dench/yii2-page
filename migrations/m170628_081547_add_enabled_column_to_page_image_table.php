<?php

use yii\db\Migration;

/**
 * Handles adding enabled to table `page_image`.
 */
class m170628_081547_add_enabled_column_to_page_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('page_image', 'enabled', 'boolean not null default 1');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('page_image', 'enabled');
    }
}
