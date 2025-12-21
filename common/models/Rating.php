<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Rating extends ActiveRecord
{
    const TYPE_UP = 'up';
    const TYPE_DOWN = 'down';

    public static function tableName()
    {
        return 'ratings';
    }

    public function rules()
    {
        return [
            [['user_id', 'article_url', 'rating_type'], 'required'],
            ['user_id', 'integer'],
            ['article_url', 'string', 'max' => 500],
            ['rating_type', 'in', 'range' => [self::TYPE_UP, self::TYPE_DOWN]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'article_url' => 'Article URL',
            'rating_type' => 'Rating Type',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function getRatingCounts($articleUrl)
    {
        $thumbsUp = self::find()
            ->where(['article_url' => $articleUrl, 'rating_type' => self::TYPE_UP])
            ->count();

        $thumbsDown = self::find()
            ->where(['article_url' => $articleUrl, 'rating_type' => self::TYPE_DOWN])
            ->count();

        return [
            'thumbs_up' => $thumbsUp,
            'thumbs_down' => $thumbsDown,
        ];
    }

    public static function getUserRating($userId, $articleUrl)
    {
        $rating = self::findOne(['user_id' => $userId, 'article_url' => $articleUrl]);
        return $rating ? $rating->rating_type : null;
    }

    public static function setRating($userId, $articleUrl, $type)
    {
        $rating = self::findOne(['user_id' => $userId, 'article_url' => $articleUrl]);

        if ($rating) {
            if ($rating->rating_type == $type) {
                // Remove rating if clicking same button
                $rating->delete();
                return null;
            } else {
                // Change rating
                $rating->rating_type = $type;
                $rating->save();
                return $type;
            }
        } else {
            // Create new rating
            $rating = new self();
            $rating->user_id = $userId;
            $rating->article_url = $articleUrl;
            $rating->rating_type = $type;
            $rating->save();
            return $type;
        }
    }
}
