<?php

use yii\db\Migration;

/**
 * Initial migration for News Portal database
 * Alternative to manual SQL execution
 * 
 * Run with: php yii migrate
 */
class m130524_201442_init extends Migration
{
    public function safeUp()
    {
        // Users table
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull()->unique(),
            'birth_year' => $this->integer()->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'failed_login_attempts' => $this->integer()->defaultValue(0),
            'last_failed_login' => $this->timestamp(),
            'is_locked' => $this->boolean()->defaultValue(false),
            'status' => $this->string(10),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Create index on email
        $this->createIndex('idx-users-email', '{{%users}}', 'email');

        // Bookmarks table
        $this->createTable('{{%bookmarks}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'article_url' => $this->string(500)->notNull(),
            'article_title' => $this->text(),
            'article_author' => $this->string(255),
            'article_description' => $this->text(),
            'article_content' => $this->text(),
            'article_source' => $this->string(255),
            'published_at' => $this->timestamp(),
            'url_to_image' => $this->string(500),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key
        $this->addForeignKey(
            'fk-bookmarks-user_id',
            '{{%bookmarks}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        // Create unique constraint
        $this->createIndex(
            'idx-bookmarks-user-article',
            '{{%bookmarks}}',
            ['user_id', 'article_url'],
            true
        );

        // Create index for faster queries
        $this->createIndex('idx-bookmarks-user_id', '{{%bookmarks}}', 'user_id');

        // Ratings table
        $this->createTable('{{%ratings}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'article_url' => $this->string(500)->notNull(),
            'article_title' => $this->text(),
            'article_author' => $this->string(255),
            'article_description' => $this->text(),
            'article_content' => $this->text(),
            'article_source' => $this->string(255),
            'published_at' => $this->timestamp(),
            'url_to_image' => $this->string(500),
            'rating_type' => $this->string(10)->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key
        $this->addForeignKey(
            'fk-ratings-user_id',
            '{{%ratings}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        // Create unique constraint
        $this->createIndex(
            'idx-ratings-user-article',
            '{{%ratings}}',
            ['user_id', 'article_url'],
            true
        );

        // Create indexes
        $this->createIndex('idx-ratings-user_id', '{{%ratings}}', 'user_id');
        $this->createIndex('idx-ratings-article_url', '{{%ratings}}', 'article_url');

        // Add check constraint for rating_type
        $this->execute("ALTER TABLE ratings ADD CONSTRAINT chk_rating_type CHECK (rating_type IN ('up', 'down'))");

        // API Logs table
        $this->createTable('{{%api_logs}}', [
            'id' => $this->primaryKey(),
            'endpoint' => $this->string(500)->notNull(),
            'method' => $this->string(10)->notNull(),
            'request_params' => $this->text(),
            'response_status' => $this->integer(),
            'response_body' => $this->text(),
            'error_message' => $this->text(),
            'ip_address' => $this->string(45),
            'user_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key (nullable)
        $this->addForeignKey(
            'fk-api_logs-user_id',
            '{{%api_logs}}',
            'user_id',
            '{{%users}}',
            'id',
            'SET NULL'
        );

        // Create index for date queries
        $this->createIndex('idx-api_logs-created_at', '{{%api_logs}}', 'created_at');

        // Articles cache table (optional)
        $this->createTable('{{%articles_cache}}', [
            'id' => $this->primaryKey(),
            'article_url' => $this->string(500)->notNull()->unique(),
            'title' => $this->text(),
            'author' => $this->string(255),
            'description' => $this->text(),
            'content' => $this->text(),
            'source' => $this->string(255),
            'published_at' => $this->timestamp(),
            'url_to_image' => $this->string(500),
            'category' => $this->string(50),
            'keywords' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Create indexes
        $this->createIndex('idx-articles_cache-url', '{{%articles_cache}}', 'article_url');
        $this->createIndex('idx-articles_cache-category', '{{%articles_cache}}', 'category');

        echo "Database tables created successfully!\n";
    }

    public function safeDown()
    {
        // Drop tables in reverse order
        $this->dropTable('{{%articles_cache}}');
        $this->dropTable('{{%api_logs}}');
        $this->dropTable('{{%ratings}}');
        $this->dropTable('{{%bookmarks}}');
        $this->dropTable('{{%users}}');

        echo "Database tables dropped successfully!\n";
    }
}