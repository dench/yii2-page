<?php

use yii\db\Migration;

/**
 * Handles adding position to table `page`.
 */
class m170325_172854_add_position_column_to_page_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('page', 'position', 'integer not null default 0');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('page', 'position');
    }
}
