<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Disukai - Portal Berita';

$placeholder = 'https://via.placeholder.com/200x150?text=No+Image';
$imageUrl = Html::encode($article['urlToImage'] ?? $placeholder);

?>

<h2 class="mb-4"><i class="fas fa-thumbs-up"></i> Articles I Liked</h2>

<?php if (empty($articles)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You haven't liked any articles yet.
    </div>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
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
                    <span><i class="fas fa-building"></i> <?= Html::encode($article['source']['name'] ?? 'Unknown') ?></span>
                    <?php if (!empty($article['author'])): ?>
                        <span class="ms-3"><i class="fas fa-user"></i> <?= Html::encode($article['author']) ?></span>
                    <?php endif; ?>
                    <span class="ms-3"><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($article['publishedAt'])) ?></span>
                </div>

                <?php if (!empty($article['description'])): ?>
                    <p class="article-description">
                        <?= Html::encode($article['description']) ?>
                    </p>
                <?php endif; ?>

                <div class="rating-section">
                    <?php
                    $ratingData = $ratings[$article['url']];
                    ?>
                    <button class="rating-btn rating-up active" data-type="up">
                        <span class="count"><?= $ratingData['counts']['thumbs_up'] ?></span>
                        <i class="fas fa-thumbs-up"></i>
                    </button>

                    <button class="rating-btn rating-down" data-type="down">
                        <i class="fas fa-thumbs-down"></i>
                        <span class="count"><?= $ratingData['counts']['thumbs_down'] ?></span>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php

$rating = Url::to(['/api/set-rating']);

$script = <<< JS
    $(document).ready(function() {
        $('.rating-btn').click(function() {
            const btn = $(this);
            const card = btn.closest('.article-card');
            const articleUrl = card.data('url');
            const type = btn.data('type');

            $.ajax({
                url: '$rating',
                method: 'POST',
                data: {
                    url: articleUrl,
                    type: type
                },
                success: function(response) {
                    if (response.success) {
                        if (response.userRating !== 'up') {
                            // Removed like, remove from list
                            card.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            // Update counts
                            const upBtn = card.find('.rating-up');
                            const downBtn = card.find('.rating-down');
                            upBtn.find('.count').text(response.counts.thumbs_up);
                            downBtn.find('.count').text(response.counts.thumbs_down);
                        }
                    }
                }
            });
        });
    });
JS;
$this->registerJs($script);
?>