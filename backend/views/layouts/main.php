<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .navbar {
            background: linear-gradient(135deg, black 0%, #494e54 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-categories {
            background-color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .category-btn {
            margin: 5px;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .category-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .article-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            display: flex;
            gap: 20px;
        }

        .article-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .article-image {
            flex-shrink: 0;
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .article-content {
            flex: 1;
        }

        .article-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .article-title:hover {
            color: black;
        }

        .article-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .article-description {
            color: #555;
            line-height: 1.6;
        }

        .rating-section {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-top: 10px;
        }

        .rating-btn {
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px 10px;
            border: none;
            background: none;
            transition: all 0.2s;
        }

        .rating-btn.active {
            color: black;
        }

        .rating-btn:hover {
            transform: scale(1.2);
        }

        .bookmark-btn {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: #ccc;
            transition: all 0.2s;
        }

        .bookmark-btn.bookmarked {
            color: black;
        }

        .search-box {
            width: 300px;
        }

        .category-btn {
            margin: 5px;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s;
            border: 2px solid black;
        }

        .category-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Active state */
        .category-btn.active,
        .category-btn.btn-primary {
            background: linear-gradient(135deg, black 0%, #494e54 100%) !important;
            color: white !important;
            border-color: black !important;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        }

        .category-btn.btn-outline-primary {
            background: white;
            color: black;
        }

        .category-btn.btn-outline-primary:hover {
            background: #f0f2ff;
            color: black;
        }
    </style>
</head>

<body>
    <?php $this->beginBody() ?>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= Url::to(['/site/index']) ?>">
                <i class="fas fa-newspaper"></i> Portal Berita
            </a>

            <div class="d-flex align-items-center gap-3">
                <?php
                $form = ActiveForm::begin([
                    'action' => ['site/search'],
                    'method' => 'get',
                    'options' => ['class' => 'd-flex']
                ]);
                ?>
                <?= Html::input('text', 'q', Yii::$app->request->get('q'), [
                    'class' => 'form-control search-box me-2',
                    'placeholder' => 'cari berita...',
                    'required' => true
                ]) ?>
                <?= Html::submitButton('<i class="fas fa-search"></i>', [
                    'class' => 'btn btn-light'
                ]) ?>
                <?php ActiveForm::end(); ?>

                <?php if (!Yii::$app->user->isGuest): ?>
                    <a href="<?= Url::to(['/site/bookmarks']) ?>" class="btn btn-light">
                        <i class="fas fa-bookmark"></i> Bookmarks
                    </a>
                    <a href="<?= Url::to(['/site/my-likes']) ?>" class="btn btn-light">
                        <i class="fas fa-thumbs-up"></i> My Likes
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= Html::encode(Yii::$app->user->identity->full_name) ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <?= Html::beginForm(['/site/logout'], 'post')
                                    . Html::submitButton('Logout', ['class' => 'dropdown-item'])
                                    . Html::endForm() ?>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= Url::to(['/site/login']) ?>" class="btn btn-light">Login</a>
                    <a href="<?= Url::to(['/site/register']) ?>" class="btn btn-warning">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="nav-categories">
        <div class="container text-center">
            <?php
            // Get current category
            $currentCategory = Yii::$app->request->get('cat');
            $controller = Yii::$app->controller->id;
            $action = Yii::$app->controller->action->id;
            $isHomepage = ($controller == 'site' && $action == 'index' && !$currentCategory);

            // Define categories
            $categories = [
                ['name' => 'Top Headlines', 'cat' => null, 'icon' => 'fa-home', 'url' => ['/site/index']],
                ['name' => 'General', 'cat' => 'general', 'icon' => 'fa-globe', 'url' => ['/site/category', 'cat' => 'general']],
                ['name' => 'Business', 'cat' => 'business', 'icon' => 'fa-briefcase', 'url' => ['/site/category', 'cat' => 'business']],
                ['name' => 'Entertainment', 'cat' => 'entertainment', 'icon' => 'fa-film', 'url' => ['/site/category', 'cat' => 'entertainment']],
                ['name' => 'Sports', 'cat' => 'sports', 'icon' => 'fa-football-ball', 'url' => ['/site/category', 'cat' => 'sports']],
                ['name' => 'Health', 'cat' => 'health', 'icon' => 'fa-heartbeat', 'url' => ['/site/category', 'cat' => 'health']],
                ['name' => 'Science', 'cat' => 'science', 'icon' => 'fa-flask', 'url' => ['/site/category', 'cat' => 'science']],
                ['name' => 'Technology', 'cat' => 'technology', 'icon' => 'fa-laptop', 'url' => ['/site/category', 'cat' => 'technology']],
            ];
            ?>
            <?php foreach ($categories as $cat): ?>
                <?php
                // Check if this category is active
                $isActive = ($cat['cat'] === null && $isHomepage) || ($cat['cat'] === $currentCategory);
                $btnClass = $isActive ? 'btn-primary active' : 'btn-outline-primary';
                ?>
                <a href="<?= Url::to($cat['url']) ?>" class="btn category-btn <?= $btnClass ?>">
                    <i class="fas <?= $cat['icon'] ?>"></i> <?= $cat['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container">
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>