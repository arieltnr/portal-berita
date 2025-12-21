<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use common\models\Bookmark;
use common\models\Rating;
use common\models\User;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'toggle-bookmark' => ['post'],
                    'set-rating' => ['post'],
                    'recommendations' => ['post'],
                ],
            ],
        ];
    }

    public function actionToggleBookmark()
    {
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Please login first'];
        }

        $articleData = Yii::$app->request->post();

        if (empty($articleData['url'])) {
            return ['success' => false, 'message' => 'Invalid article data'];
        }

        try {
            $isAdded = Bookmark::toggleBookmark(Yii::$app->user->id, $articleData);

            return [
                'success' => true,
                'isBookmarked' => $isAdded,
                'message' => $isAdded ? 'Bookmark added' : 'Bookmark removed'
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionSetRating()
    {
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Please login first'];
        }

        $url = Yii::$app->request->post('url');
        $type = Yii::$app->request->post('type'); // 'up' or 'down'

        if (empty($url) || !in_array($type, ['up', 'down'])) {
            return ['success' => false, 'message' => 'Invalid data'];
        }

        try {
            $newRating = Rating::setRating(Yii::$app->user->id, $url, $type);
            $counts = Rating::getRatingCounts($url);

            return [
                'success' => true,
                'userRating' => $newRating,
                'counts' => $counts
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionCheckEmail()
    {
        $email = Yii::$app->request->post('email');

        if (empty($email)) {
            return ['available' => false];
        }

        $exists = User::find()->where(['email' => $email])->exists();

        return ['available' => !$exists];
    }

    // FASE 3: Recommendations API
    public function actionRecommendations()
    {
        // Validasi header
        $signature = Yii::$app->request->headers->get('x-signature');

        if ($signature !== 'DHEALTHSKILLTEST2025') {
            Yii::$app->response->statusCode = 403;
            return [
                'status' => 'error',
                'message' => 'Invalid signature'
            ];
        }

        // Get request body
        $rawBody = Yii::$app->request->rawBody;
        $data = json_decode($rawBody, true);

        if (!isset($data['user_email'])) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'message' => 'user_email is required'
            ];
        }

        try {
            $newsApi = Yii::$app->newsApi;
            $result = $newsApi->getRecommendations($data['user_email'], 10);

            if ($result['status'] === 'error') {
                Yii::$app->response->statusCode = 404;
                return $result;
            }

            // Format response sesuai spec
            $articles = [];
            foreach ($result['articles'] as $article) {
                $articles[] = [
                    'author' => $article['author'] ?? '',
                    'title' => $article['title'] ?? '',
                    'description' => $article['description'] ?? '',
                    'content' => $article['content'] ?? '',
                    'source' => $article['source']['name'] ?? '',
                    'publishedAt' => $article['publishedAt'] ?? '',
                    'url' => $article['url'] ?? '',
                    'urlToImage' => $article['urlToImage'] ?? ''
                ];
            }

            return [
                'status' => 'ok',
                'article' => $articles
            ];
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 500;
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
