<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow text-center p-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            <h2 class="mb-3">Pendaftaran Berhasil!</h2>
            <p class="lead">Akun Anda dengan email <strong><?= Html::encode($email) ?></strong> telah berhasil dibuat.</p>
            <p>Silakan login untuk mulai menggunakan News Portal.</p>
            <div class="mt-4">
                <a href="<?= Url::to(['/site/login']) ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Login Sekarang
                </a>
            </div>
        </div>
    </div>
</div>