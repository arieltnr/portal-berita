<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Bookmarks - Portal Berita';
?>

<h2 class="mb-4"><i class="fas fa-bookmark"></i> My Bookmarks</h2>

<?php if (empty($bookmarks)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You don't have any bookmarked articles yet.
    </div>
<?php else: ?>
    <?php foreach ($bookmarks as $bookmark): ?>
        <div class="article-card" data-url="<?= Html::encode($bookmark->article_url) ?>">
            <img src="<?= Html::encode($bookmark->url_to_image ?? Yii::getAlias('@webroot/assets/monitor.jpg')) ?>"
                alt="Article Image"
                class="article-image"
                onerror="this.src='<?php Yii::getAlias('@webroot/assets/monitor.jpg'); ?>'">

            <div class="article-content">
                <a href="<?= Html::encode($bookmark->article_url) ?>"
                    target="_blank"
                    class="article-title">
                    <?= Html::encode($bookmark->article_title ?? 'Untitled') ?>
                </a>

                <div class="article-meta">
                    <span><i class="fas fa-building"></i> <?= Html::encode($bookmark->article_source ?? 'Unknown') ?></span>
                    <?php if ($bookmark->article_author): ?>
                        <span class="ms-3"><i class="fas fa-user"></i> <?= Html::encode($bookmark->article_author) ?></span>
                    <?php endif; ?>
                    <span class="ms-3"><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($bookmark->published_at)) ?></span>
                </div>

                <?php if ($bookmark->article_description): ?>
                    <p class="article-description">
                        <?= Html::encode($bookmark->article_description) ?>
                    </p>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="rating-section">
                        <?php
                        $ratingData = $ratings[$bookmark->article_url] ?? ['counts' => ['thumbs_up' => 0, 'thumbs_down' => 0], 'userRating' => null];
                        $thumbsUpActive = $ratingData['userRating'] === 'up' ? 'active' : '';
                        $thumbsDownActive = $ratingData['userRating'] === 'down' ? 'active' : '';
                        ?>

                        <button class="rating-btn rating-up <?= $thumbsUpActive ?>" data-type="up">
                            <span class="count"><?= $ratingData['counts']['thumbs_up'] ?></span>
                            <i class="fas fa-thumbs-up"></i>
                        </button>

                        <button class="rating-btn rating-down <?= $thumbsDownActive ?>" data-type="down">
                            <i class="fas fa-thumbs-down"></i>
                            <span class="count"><?= $ratingData['counts']['thumbs_down'] ?></span>
                        </button>
                    </div>

                    <button class="bookmark-btn bookmarked" title="Remove bookmark">
                        <i class="fas fa-bookmark"></i>
                    </button>
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

            $.ajax({
                url: '$bookmark',
                method: 'POST',
                data: {
                    url: articleUrl
                },
                success: function(response) {
                    if (response.success) {
                        card.fadeOut(300, function() {
                            $(this).remove();
                        });
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

                        if (response.userRating === 'up') {
                            upBtn.addClass('active');
                        } else if (response.userRating === 'down') {
                            downBtn.addClass('active');
                        }

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