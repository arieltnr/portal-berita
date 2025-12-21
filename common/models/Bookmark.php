<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Bookmark extends ActiveRecord
{
    public static function tableName()
    {
        return 'bookmarks';
    }

    public function rules()
    {
        return [
            [['user_id', 'article_url'], 'required'],
            ['user_id', 'integer'],
            [['article_url', 'url_to_image'], 'string', 'max' => 500],
            [['article_title', 'article_description', 'article_content'], 'string'],
            [['article_author', 'article_source'], 'string', 'max' => 255],
            ['published_at', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'article_url' => 'Article URL',
            'article_title' => 'Title',
            'article_author' => 'Author',
            'article_description' => 'Description',
            'article_content' => 'Content',
            'article_source' => 'Source',
            'published_at' => 'Published At',
            'url_to_image' => 'Image URL',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function isBookmarked($userId, $articleUrl)
    {
        return self::find()
            ->where(['user_id' => $userId, 'article_url' => $articleUrl])
            ->exists();
    }

    public static function toggleBookmark($userId, $articleData)
    {
        $bookmark = self::findOne(['user_id' => $userId, 'article_url' => $articleData['url']]);

        if ($bookmark) {
            $bookmark->delete();
            return false; // Removed
        } else {
            $bookmark = new self();
            $bookmark->user_id = $userId;
            $bookmark->article_url = $articleData['url'];
            $bookmark->article_title = $articleData['title'] ?? null;
            $bookmark->article_author = $articleData['author'] ?? null;
            $bookmark->article_description = $articleData['description'] ?? null;
            $bookmark->article_content = $articleData['content'] ?? null;
            $bookmark->article_source = isset($articleData['source']['name']) ? $articleData['source']['name'] : null;
            $bookmark->published_at = $articleData['publishedAt'] ?? null;
            $bookmark->url_to_image = $articleData['urlToImage'] ?? null;
            $bookmark->save();
            return true; // Added
        }
    }
}
