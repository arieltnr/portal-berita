<?php

use yii\helpers\Html;

?>

<div class="alert alert-danger">
    <h4><i class="fas fa-exclamation-circle"></i> API Error</h4>
    <p><strong>Status:</strong> <?= Html::encode($error['status'] ?? 'error') ?></p>
    <p><strong>Code:</strong> <?= Html::encode($error['code'] ?? 'unknown') ?></p>
    <p><strong>Message:</strong> <?= Html::encode($error['message'] ?? 'Unknown error') ?></p>
    <?php if (isset($error['httpCode'])): ?>
        <p><strong>HTTP Code:</strong> <?= Html::encode($error['httpCode']) ?></p>
    <?php endif; ?>
</div>