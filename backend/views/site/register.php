<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Daftar - Portal Berita';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body p-5">
                <h2 class="text-center mb-4"><i class="fas fa-user-plus"></i> Daftar Pengguna Baru</h2>

                <?php if ($model->hasErrors()) {
                    echo '<div class="alert alert-danger">';
                    foreach ($model->errors as $errors) {
                        foreach ($errors as $error) {
                            echo Html::encode($error) . '<br>';
                        }
                    }
                    echo '</div>';
                }
                $form = ActiveForm::begin([
                    'id' => 'register-form',
                    'options' => ['class' => 'needs-validation'],
                ]); ?>

                <?= $form->field($model, 'full_name')->textInput([
                    'placeholder' => 'Nama Lengkap',
                    'class' => 'form-control form-control-lg'
                ])->label('Nama Lengkap') ?>

                <?= $form->field($model, 'email')->textInput([
                    'placeholder' => 'Email Aktif',
                    'class' => 'form-control form-control-lg required',
                    'id' => 'email-input'
                ])->label('Email Aktif') ?>
                <div id="email-feedback" class="mb-3"></div>

                <?= $form->field($model, 'birth_year')->textInput([
                    'placeholder' => 'Tahun Lahir (contoh: 1990)',
                    'class' => 'form-control form-control-lg required',
                    'type' => 'number',
                    'min' => 1900,
                    'max' => date('Y')
                ])->label('Tahun Lahir')->error() ?>

                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder' => 'Password',
                    'class' => 'form-control form-control-lg required'
                ])->label('Password')->hint('Min 12 karakter, 1 huruf kapital, 1 angka, 1 simbol') ?>

                <?= $form->field($model, 'repeat_password')->passwordInput([
                    'placeholder' => 'Ulangi Password',
                    'class' => 'form-control form-control-lg required'
                ])->label('Ulangi Password') ?>

                <div class="d-grid gap-2 mt-4">
                    <?= Html::submitButton('<i class="fas fa-user-plus"></i> Daftar', [
                        'class' => 'btn btn-primary btn-lg',
                        'id' => 'submit-btn'
                    ]) ?>
                </div>

                <?php ActiveForm::end(); ?>

                <div class="text-center mt-3">
                    <p>Sudah punya akun? <a href="<?= Url::to(['/site/login']) ?>">Login disini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$checkEmailUrl = Url::to(['/api/check-email']);
$script = <<< JS
    $(document).ready(function() {
        let emailCheckTimeout;

        $('#email-input').on('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = $(this).val();
            const feedback = $('#email-feedback');

            if (email.length > 5 && email.includes('@')) {
                emailCheckTimeout = setTimeout(function() {
                    $.ajax({
                        url: '$checkEmailUrl',
                        method: 'POST',
                        data: {
                            email: email
                        },
                        success: function(response) {
                            if (response.available) {
                                feedback.html('<small class="text-success"><i class="fas fa-check"></i> Email tersedia</small>');
                                $('#submit-btn').prop('disabled', false);
                            } else {
                                feedback.html('<small class="text-danger"><i class="fas fa-times"></i> Email sudah terdaftar</small>');
                                $('#submit-btn').prop('disabled', true);
                            }
                        }
                    });
                }, 500);
            } else {
                feedback.html('');
            }
        });
    });
JS;
$this->registerJs($script);
?>