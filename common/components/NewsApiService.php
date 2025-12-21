<?php

namespace common\components;

use Yii;
use yii\base\Component;
use common\models\ApiLog;
use common\models\User;
use common\models\Rating;
use common\models\Bookmark;

class NewsApiService extends Component
{
    public $apiKey;
    public $baseUrl = 'https://newsapi.org/v2';

    public function init()
    {
        parent::init();
        if (!$this->apiKey) {
            $this->apiKey = Yii::$app->params['newsApiKey'] ?? '';
        }
    }

    public function getTopHeadlines($country = 'us', $category = null, $page = 1, $pageSize = 10)
    {
        $params = [
            'country' => $country,
            'pageSize' => $pageSize,
            'page' => $page,
        ];

        if ($category) {
            $params['category'] = $category;
        }

        return $this->request('/top-headlines', $params);
    }

    public function searchNews($query, $from = null, $sortBy = 'publishedAt', $page = 1, $pageSize = 10)
    {
        $params = [
            'q' => $query,
            'sortBy' => $sortBy,
            'pageSize' => $pageSize,
            'page' => $page,
        ];

        if ($from) {
            $params['from'] = $from;
        }

        return $this->request('/everything', $params);
    }

    public function getTodayNewsByCategory($category, $country = 'us', $pageSize = 10)
    {
        $today = date('Y-m-d');

        $params = [
            'country' => $country,
            'category' => $category,
            'from' => $today,
            'pageSize' => $pageSize,
        ];

        return $this->request('/top-headlines', $params);
    }

    private function request($endpoint, $params = [])
    {
        $params['apiKey'] = $this->apiKey;
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: NewsPortal/1.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                ApiLog::log($endpoint, 'GET', $params, 0, $response, $error);
                throw new \Exception('Curl Error: ' . $error);
            }

            $data = json_decode($response, true);
            ApiLog::log($endpoint, 'GET', $params, $httpCode, $response, null);

            if ($httpCode != 200) {
                return [
                    'status' => 'error',
                    'code' => $data['code'] ?? 'unknown',
                    'message' => $data['message'] ?? 'Unknown error from API',
                    'httpCode' => $httpCode
                ];
            }

            return $data;
        } catch (\Exception $e) {
            ApiLog::log($endpoint, 'GET', $params, 0, '', $e->getMessage());
            throw $e;
        }
    }

    public function getRecommendations($userEmail, $limit = 10)
    {
        $user = User::findByEmail($userEmail);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found'
            ];
        }

        $likedArticles = Rating::find()
            ->where(['user_id' => $user->id, 'rating_type' => 'up'])
            ->limit(50)
            ->all();

        if (empty($likedArticles)) {
            return $this->getTopHeadlines('us', null, 1, $limit);
        }

        $keywords = $this->extractKeywords($likedArticles);

        if (!empty($keywords)) {
            $searchQuery = implode(' OR ', array_slice($keywords, 0, 3));
            $results = $this->searchNews($searchQuery, null, 'publishedAt', 1, $limit);

            if ($results['status'] === 'ok' && !empty($results['articles'])) {
                $ratedUrls = Rating::find()
                    ->select('article_url')
                    ->where(['user_id' => $user->id])
                    ->column();

                $filtered = array_filter($results['articles'], function ($article) use ($ratedUrls) {
                    return !in_array($article['url'], $ratedUrls);
                });

                $results['articles'] = array_values(array_slice($filtered, 0, $limit));
            }

            return $results;
        }

        return $this->getTopHeadlines('us', null, 1, $limit);
    }

    private function extractKeywords($articles)
    {
        $keywords = [];
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'in', 'a', 'an', 'and', 'or', 'but', 'to', 'for', 'of', 'as', 'by'];

        foreach ($articles as $rating) {
            $bookmark = Bookmark::findOne(['article_url' => $rating->article_url]);

            if ($bookmark) {
                $text = strtolower($bookmark->article_title . ' ' . $bookmark->article_description);
                $words = preg_split('/\s+/', $text);

                foreach ($words as $word) {
                    $word = preg_replace('/[^a-z0-9]/', '', $word);
                    if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                        $keywords[$word] = ($keywords[$word] ?? 0) + 1;
                    }
                }
            }
        }

        arsort($keywords);
        return array_keys($keywords);
    }
}
