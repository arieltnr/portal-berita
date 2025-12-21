<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class ApiLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'api_logs';
    }

    public function rules()
    {
        return [
            [['endpoint', 'method'], 'required'],
            ['endpoint', 'string', 'max' => 500],
            ['method', 'string', 'max' => 10],
            [['request_params', 'response_body', 'error_message'], 'string'],
            ['response_status', 'integer'],
            ['ip_address', 'string', 'max' => 45],
            ['user_id', 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'endpoint' => 'Endpoint',
            'method' => 'Method',
            'request_params' => 'Request Params',
            'response_status' => 'Response Status',
            'response_body' => 'Response Body',
            'error_message' => 'Error Message',
            'ip_address' => 'IP Address',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function log($endpoint, $method, $params, $status, $response, $error = null)
    {
        $log = new self();
        $log->endpoint = $endpoint;
        $log->method = $method;
        $log->request_params = json_encode($params);
        $log->response_status = $status;
        $log->response_body = is_string($response) ? $response : json_encode($response);
        $log->error_message = $error;
        $log->ip_address = Yii::$app->request->userIP ?? '0.0.0.0';
        $log->user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $log->save();
    }
}
