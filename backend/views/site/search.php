<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Search: ' . Html::encode($query) . ' - Portal Berita';

$placeholder = Yii::getAlias('@web/images.png');

?>

<h2 class="mb-4">
    <i class="fas fa-search"></i> Search Results for "<?= Html::encode($query) ?>"
</h2>

<?php if (empty($articles)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No articles found for your search query.
    </div>
<?php else: ?>
    <p class="text-muted mb-4">Found <?= count($articles) ?> articles</p>

    <?php foreach ($articles as $article): ?>
        <?php $imageUrl = Html::encode($article['urlToImage'] ?? $placeholder); ?>
        <div class="article-card" data-url="<?= Html::encode($article['url']) ?>">
            <img src="<?= $imageUrl ?>"
                alt="Article Image"
                class="article-image"
                onerror="this.src='<?= Html::encode($placeholder) ?>'">

            <div class="article-content">
                <a href="<?= Html::encode($article['url']) ?>"
                    target="_blank"
                    class="article-title">
                    <?= Html::encode($article['title'] ?? 'Untitled') ?>
                </a>

                <div class="article-meta">
                    <span><i class="fas fa-building"></i> <?= Html::encode($article['source']['name'] ?? 'Unknown Source') ?></span>
                    <?php if (!empty($article['author'])): ?>
                        <span class="ms-3"><i class="fas fa-user"></i> <?= Html::encode($article['author']) ?></span>
                    <?php endif; ?>
                    <span class="ms-3"><i class="fas fa-calendar"></i> <?= date('M d, Y H:i', strtotime($article['publishedAt'])) ?></span>
                </div>

                <?php if (!empty($article['description'])): ?>
                    <p class="article-description">
                        <?= Html::encode($article['description']) ?>
                    </p>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="rating-section">
                        <?php
                        $ratingData = $ratings[$article['url']] ?? ['counts' => ['thumbs_up' => 0, 'thumbs_down' => 0], 'userRating' => null];
                        $thumbsUpActive = $ratingData['userRating'] === 'up' ? 'active' : '';
                        $thumbsDownActive = $ratingData['userRating'] === 'down' ? 'active' : '';
                        ?>

                        <button class="rating-btn rating-up <?= $thumbsUpActive ?>"
                            data-type="up"
                            <?= Yii::$app->user->isGuest ? 'disabled' : '' ?>>
                            <span class="count"><?= $ratingData['counts']['thumbs_up'] ?></span>
                            <i class="fas fa-thumbs-up"></i>
                        </button>

                        <button class="rating-btn rating-down <?= $thumbsDownActive ?>"
                            data-type="down"
                            <?= Yii::$app->user->isGuest ? 'disabled' : '' ?>>
                            <i class="fas fa-thumbs-down"></i>
                            <span class="count"><?= $ratingData['counts']['thumbs_down'] ?></span>
                        </button>
                    </div>

                    <?php if (!Yii::$app->user->isGuest): ?>
                        <button class="bookmark-btn <?= in_array($article['url'], $bookmarkedUrls) ? 'bookmarked' : '' ?>"
                            title="Bookmark this article">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php

$bookmark = Url::to(['/api/toggle-bookmark']);
$rating = Url::to(['/api/set-rating']);

$script = <<< JS
    $(document).ready(function() {
        $('.bookmark-btn').click(function() {
            const btn = $(this);
            const card = btn.closest('.article-card');
            const articleUrl = card.data('url');
            const articleData = {
                url: articleUrl,
                title: card.find('.article-title').text().trim(),
                author: card.find('.article-meta .fa-user').parent().text().trim(),
                description: card.find('.article-description').text().trim(),
                source: {
                    name: card.find('.article-meta .fa-building').parent().text().trim()
                },
                publishedAt: card.find('.article-meta .fa-calendar').parent().text().trim(),
                urlToImage: card.find('.article-image').attr('src')
            };

            $.ajax({
                url: '$bookmark',
                method: 'POST',
                data: articleData,
                success: function(response) {
                    if (response.success) {
                        btn.toggleClass('bookmarked');
                    }
                }
            });
        });

        $('.rating-btn').click(function() {
            const btn = $(this);
            const card = btn.closest('.article-card');
            const articleUrl = card.data('url');
            const typeData = btn.data('type');
            const articleData = {
                url: articleUrl,
                title: card.find('.article-title').text().trim(),
                author: card.find('.article-meta .fa-user').parent().text().trim(),
                description: card.find('.article-description').text().trim(),
                content: card.find('.text-muted').text().trim(),
                source: {
                    name: card.find('.article-meta .fa-building').parent().text().trim()
                },
                publishedAt: card.find('.article-meta .fa-calendar').parent().text().trim(),
                urlToImage: card.find('.article-image').attr('src'),
                type: typeData
            };

            $.ajax({
                url: '$rating',
                method: 'POST',
                data: articleData,
                success: function(response) {
                    if (response.success) {
                        const upBtn = card.find('.rating-up');
                        const downBtn = card.find('.rating-down');
                        upBtn.removeClass('active');
                        downBtn.removeClass('active');
                        if (response.userRating === 'up') upBtn.addClass('active');
                        else if (response.userRating === 'down') downBtn.addClass('active');
                        upBtn.find('.count').text(response.counts.thumbs_up);
                        downBtn.find('.count').text(response.counts.thumbs_down);
                    }
                }
            });
        });
    });
JS;
$this->registerJs($script);
?>