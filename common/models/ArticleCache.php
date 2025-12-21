<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class ArticleCache extends ActiveRecord
{
    public static function tableName()
    {
        return 'articles_cache';
    }

    public function rules()
    {
        return [
            ['article_url', 'required'],
            ['article_url', 'string', 'max' => 500],
            ['article_url', 'unique'],
            [['title', 'description', 'content', 'keywords'], 'string'],
            [['author', 'source', 'category'], 'string', 'max' => 255],
            ['url_to_image', 'string', 'max' => 500],
            ['published_at', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_url' => 'Article URL',
            'title' => 'Title',
            'author' => 'Author',
            'description' => 'Description',
            'content' => 'Content',
            'source' => 'Source',
            'published_at' => 'Published At',
            'url_to_image' => 'Image URL',
            'category' => 'Category',
            'keywords' => 'Keywords',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!$insert) {
                $this->updated_at = new \yii\db\Expression('NOW()');
            }
            return true;
        }
        return false;
    }

    public static function cacheArticle($articleData)
    {
        $cache = self::findOne(['article_url' => $articleData['url']]);
        
        if (!$cache) {
            $cache = new self();
            $cache->article_url = $articleData['url'];
        }
        
        $cache->title = $articleData['title'] ?? null;
        $cache->author = $articleData['author'] ?? null;
        $cache->description = $articleData['description'] ?? null;
        $cache->content = $articleData['content'] ?? null;
        $cache->source = isset($articleData['source']['name']) ? $articleData['source']['name'] : null;
        $cache->published_at = $articleData['publishedAt'] ?? null;
        $cache->url_to_image = $articleData['urlToImage'] ?? null;
        $cache->keywords = self::extractKeywords($articleData);
        
        $cache->save();
        return $cache;
    }

    private static function extractKeywords($articleData)
    {
        $text = '';
        if (isset($articleData['title'])) {
            $text .= $articleData['title'] . ' ';
        }
        if (isset($articleData['description'])) {
            $text .= $articleData['description'];
        }
        
        // Simple keyword extraction
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        return $text;
    }
}
