<?php

use yii\db\Migration;

/**
 * Handles adding verification_token column to table `users` (not `user`)
 */
class m190124_110200_add_verification_token_column_to_user_table extends Migration
{
    public function up()
    {
        // Check if table exists first
        $tableSchema = $this->db->getTableSchema('{{%users}}');
        if ($tableSchema !== null) {
            $this->addColumn('{{%users}}', 'verification_token', $this->string()->defaultValue(null));
        }
    }

    public function down()
    {
        $tableSchema = $this->db->getTableSchema('{{%users}}');
        if ($tableSchema !== null) {
            $this->dropColumn('{{%users}}', 'verification_token');
        }
    }
}