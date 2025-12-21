<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model untuk News Portal
 *
 * @property integer $id
 * @property string $full_name
 * @property string $email
 * @property integer $birth_year
 * @property string $password_hash
 * @property integer $failed_login_attempts
 * @property string $last_failed_login
 * @property boolean $is_locked
 * @property string $created_at
 * @property string $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public $password;
    public $repeat_password;
    
    const LOCK_DURATION = 300; // 5 menit
    const MAX_FAILED_ATTEMPTS = 3;

    public static function tableName()
    {
        return '{{%users}}';
    }

    public function rules()
    {
        return [
            [['full_name', 'email', 'birth_year'], 'required'],
            [['password', 'repeat_password'], 'required', 'on' => 'register'],
            ['email', 'email'],
            ['email', 'unique', 'message' => 'Email ini sudah terdaftar'],
            ['birth_year', 'integer', 'min' => 1900, 'max' => date('Y')],
            ['password', 'string', 'min' => 12, 'on' => 'register'],
            ['password', 'validatePassword', 'on' => 'register'],
            ['repeat_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Password tidak cocok'],
            ['full_name', 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'full_name' => 'Nama Lengkap',
            'email' => 'Email',
            'birth_year' => 'Tahun Lahir',
            'password' => 'Password',
            'repeat_password' => 'Ulangi Password',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!preg_match('/[A-Z]/', $this->password)) {
            $this->addError($attribute, 'Password harus mengandung minimal 1 huruf kapital');
        }
        if (!preg_match('/[0-9]/', $this->password)) {
            $this->addError($attribute, 'Password harus mengandung minimal 1 angka');
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $this->password)) {
            $this->addError($attribute, 'Password harus mengandung minimal 1 simbol');
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord && $this->password) {
                $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
            }
            $this->updated_at = new \yii\db\Expression('NOW()');
            return true;
        }
        return false;
    }

    public function validatePasswordLogin($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function checkLocked()
    {
        if ($this->is_locked && $this->last_failed_login) {
            $lockTime = strtotime($this->last_failed_login);
            $now = time();
            
            if (($now - $lockTime) < self::LOCK_DURATION) {
                return true;
            } else {
                $this->is_locked = false;
                $this->failed_login_attempts = 0;
                $this->save(false);
                return false;
            }
        }
        return false;
    }

    public function incrementFailedLogin()
    {
        $this->failed_login_attempts++;
        $this->last_failed_login = new \yii\db\Expression('NOW()');
        
        if ($this->failed_login_attempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->is_locked = true;
        }
        
        $this->save(false);
    }

    public function resetFailedLogin()
    {
        $this->failed_login_attempts = 0;
        $this->is_locked = false;
        $this->last_failed_login = null;
        $this->save(false);
    }

    // IdentityInterface methods
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }

    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    // Relations
    public function getBookmarks()
    {
        return $this->hasMany(Bookmark::class, ['user_id' => 'id']);
    }

    public function getRatings()
    {
        return $this->hasMany(Rating::class, ['user_id' => 'id']);
    }
}