<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-body p-5">
                <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Login</h2>
                
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'options' => ['class' => 'needs-validation'],
                ]); ?>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <?= Html::submitButton('<i class="fas fa-sign-in-alt"></i> Login', [
                        'class' => 'btn btn-primary btn-lg'
                    ]) ?>
                </div>
                
                <?php ActiveForm::end(); ?>
                
                <div class="text-center mt-3">
                    <p>Belum punya akun? <a href="<?= Url::to(['/site/register']) ?>">Daftar disini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>