<?php

use yii\helpers\Html;

$this->title = 'Error';
?>

<!-- Modal Error -->
<div class="modal fade show" id="errorModal" style="display: block; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Terjadi Kesalahan
                </h5>
                <!-- Tombol close di header (opsional tapi umum) -->
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Pesan Error:</strong></p>
                <p><?= Html::encode($message) ?></p>
            </div>
            <div class="modal-footer">
                <!-- Tombol Close di footer -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="window.location.href='<?= Html::encode($url) ?>'">
                    <i class="fas fa-sync"></i> Muat Ulang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Pastikan Bootstrap JavaScript dimuat (jika belum) -->
<?php
$js = <<<JS
// Opsional: tutup modal saat tekan Escape
document.getElementById('errorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});
JS;
$this->registerJs($js);
?>