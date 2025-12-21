<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\Bookmark;
use common\models\Rating;
use common\models\LoginForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'bookmarks', 'my-likes'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        try {
            $newsApi = Yii::$app->newsApi;
            $result = $newsApi->getTopHeadlines('us', null, 1, 10);

            if ($result['status'] === 'error') {
                // Tampilkan error dari API
                return $this->render('error-api', ['error' => $result]);
            }

            $articles = $result['articles'] ?? [];

            // Jika user login, ambil bookmark dan rating info
            $bookmarkedUrls = [];
            $ratings = [];

            if (!Yii::$app->user->isGuest) {
                $bookmarkedUrls = Bookmark::find()
                    ->select('article_url')
                    ->where(['user_id' => Yii::$app->user->id])
                    ->column();

                foreach ($articles as $article) {
                    $ratings[$article['url']] = [
                        'counts' => Rating::getRatingCounts($article['url']),
                        'userRating' => Rating::getUserRating(Yii::$app->user->id, $article['url'])
                    ];
                }
            } else {
                foreach ($articles as $article) {
                    $ratings[$article['url']] = [
                        'counts' => Rating::getRatingCounts($article['url']),
                        'userRating' => null
                    ];
                }
            }

            return $this->render('index', [
                'articles' => $articles,
                'bookmarkedUrls' => $bookmarkedUrls,
                'ratings' => $ratings,
                'category' => null
            ]);
        } catch (\Exception $e) {
            return $this->render('error-exception', [
                'message' => $e->getMessage(),
                'url' => Yii::$app->request->url
            ]);
        }
    }

    public function actionCategory($cat)
    {
        $validCategories = ['business', 'entertainment', 'sports', 'general', 'health', 'science', 'technology'];

        if (!in_array($cat, $validCategories)) {
            throw new \yii\web\NotFoundHttpException('Category not found');
        }

        try {
            $newsApi = Yii::$app->newsApi;
            $result = $newsApi->getTodayNewsByCategory($cat, 'us', 10);

            if ($result['status'] === 'error') {
                return $this->render('error-api', ['error' => $result]);
            }

            $articles = $result['articles'] ?? [];

            $bookmarkedUrls = [];
            $ratings = [];

            if (!Yii::$app->user->isGuest) {
                $bookmarkedUrls = Bookmark::find()
                    ->select('article_url')
                    ->where(['user_id' => Yii::$app->user->id])
                    ->column();

                foreach ($articles as $article) {
                    $ratings[$article['url']] = [
                        'counts' => Rating::getRatingCounts($article['url']),
                        'userRating' => Rating::getUserRating(Yii::$app->user->id, $article['url'])
                    ];
                }
            } else {
                foreach ($articles as $article) {
                    $ratings[$article['url']] = [
                        'counts' => Rating::getRatingCounts($article['url']),
                        'userRating' => null
                    ];
                }
            }

            return $this->render('index', [
                'articles' => $articles,
                'bookmarkedUrls' => $bookmarkedUrls,
                'ratings' => $ratings,
                'category' => $cat
            ]);
        } catch (\Exception $e) {
            return $this->render('error-exception', [
                'message' => $e->getMessage(),
                'url' => Yii::$app->request->url
            ]);
        }
    }

    public function actionSearch($q)
    {
        if (empty($q)) {
            return $this->redirect(['index']);
        }

        try {
            $newsApi = Yii::$app->newsApi;
            $result = $newsApi->searchNews($q, null, 'publishedAt', 1, 20);

            if ($result['status'] === 'error') {
                return $this->render('error-api', ['error' => $result]);
            }

            $articles = $result['articles'] ?? [];

            $bookmarkedUrls = [];
            $ratings = [];

            if (!Yii::$app->user->isGuest) {
                $bookmarkedUrls = Bookmark::find()
                    ->select('article_url')
                    ->where(['user_id' => Yii::$app->user->id])
                    ->column();

                foreach ($articles as $article) {
                    $ratings[$article['url']] = [
                        'counts' => Rating::getRatingCounts($article['url']),
                        'userRating' => Rating::getUserRating(Yii::$app->user->id, $article['url'])
                    ];
                }
            } else {
                foreach ($articles as $article) {
                    $ratings[$article['url']] = [
                        'counts' => Rating::getRatingCounts($article['url']),
                        'userRating' => null
                    ];
                }
            }

            return $this->render('search', [
                'articles' => $articles,
                'query' => $q,
                'bookmarkedUrls' => $bookmarkedUrls,
                'ratings' => $ratings
            ]);
        } catch (\Exception $e) {
            return $this->render('error-exception', [
                'message' => $e->getMessage(),
                'url' => Yii::$app->request->url
            ]);
        }
    }

    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new User(['scenario' => 'register']);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save(false)) {
                return $this->render('register-success', ['email' => $model->email]);
            }
        }

        return $this->render('register', ['model' => $model]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        $model = new User();
        $email = Yii::$app->request->post('email');
        $password = Yii::$app->request->post('password');

        if ($email && $password) {
            $model = User::findByEmail($email);

            if (!$model) {
                Yii::$app->session->setFlash('error', 'Email tidak ditemukan');
                return $this->render('login');
            }

            if ($model->checkLocked()) {
                $remainingTime = 5 - floor((time() - strtotime($model->last_failed_login)) / 60);
                Yii::$app->session->setFlash('error', "Akun Anda terkunci. Silakan coba lagi dalam {$remainingTime} menit.");
                return $this->render('login');
            }

            if ($model->validatePasswordLogin($password)) {
                $model->resetFailedLogin();
                Yii::$app->user->login($model, 3600 * 24 * 30);
                return $this->goHome();
            } else {
                $model->incrementFailedLogin();

                if ($model->is_locked) {
                    Yii::$app->session->setFlash('error', 'Password salah 3 kali. Akun Anda dikunci selama 5 menit.');
                } else {
                    $remaining = User::MAX_FAILED_ATTEMPTS - $model->failed_login_attempts;
                    Yii::$app->session->setFlash('error', "Password salah. Sisa percobaan: {$remaining}");
                }

                return $this->render('login', [
                    'model' => $model,
                    'email' => $email,
                ]);
            }
        }

        return $this->render('login', [
            'model' => $model,
            'email' => $email,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionBookmarks()
    {
        $bookmarks = Bookmark::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $ratings = [];
        foreach ($bookmarks as $bookmark) {
            $ratings[$bookmark->article_url] = [
                'counts' => Rating::getRatingCounts($bookmark->article_url),
                'userRating' => Rating::getUserRating(Yii::$app->user->id, $bookmark->article_url)
            ];
        }

        return $this->render('bookmarks', [
            'bookmarks' => $bookmarks,
            'ratings' => $ratings
        ]);
    }

    public function actionMyLikes()
    {
        $likes = Rating::find()
            ->where(['user_id' => Yii::$app->user->id, 'rating_type' => 'up'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $articles = [];
        foreach ($likes as $like) {
            $bookmark = Bookmark::findOne(['article_url' => $like->article_url]);
            if ($bookmark) {
                $articles[] = [
                    'url' => $bookmark->article_url,
                    'title' => $bookmark->article_title,
                    'author' => $bookmark->article_author,
                    'description' => $bookmark->article_description,
                    'content' => $bookmark->article_content,
                    'source' => ['name' => $bookmark->article_source],
                    'publishedAt' => $bookmark->published_at,
                    'urlToImage' => $bookmark->url_to_image
                ];
            }
        }

        $ratings = [];
        foreach ($articles as $article) {
            $ratings[$article['url']] = [
                'counts' => Rating::getRatingCounts($article['url']),
                'userRating' => Rating::getUserRating(Yii::$app->user->id, $article['url'])
            ];
        }

        return $this->render('my-likes', [
            'articles' => $articles,
            'ratings' => $ratings
        ]);
    }
}
