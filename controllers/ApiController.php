<?php
/**
 * Project: fanli
 * User: liyifei
 * Date: 16/2/11
 * Time: 23:38
 */

namespace liyifei\base\controllers;

use liyifei\base\definitions\Api;
use liyifei\pluploader\PlUploaderAction;
use liyifei\pluploader\uploader\LocalUploader;
use yii;
use yii\rest\Controller;

class ApiController extends Controller
{
    public function init()
    {
        parent::init();

        Yii::$app->setComponents([
            'response' => [
                'class' => 'yii\web\Response',
                'on beforeSend' => function ($event) {
                    /**
                     * @var yii\web\Response $response
                     */
                    $response = $event->sender;
                    $response->format = yii\web\Response::FORMAT_JSON;
                    if (!$response->isSuccessful) {
                        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
                            // action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
                            $exception = new yii\web\HttpException(404, Yii::t('yii', 'Page not found.'));
                        }

                        if ($exception instanceof yii\web\HttpException) {
                            $status = $exception->statusCode;
                            $error = $exception->getMessage() ?: $exception->getName();
                        } elseif ($exception instanceof yii\base\UserException) {
                            $status = $exception->getCode() ?: Api::REQUEST_FAIL;
                            $error = $exception->getMessage();

                            $response->setStatusCode(200);
                        } else {
                            $status = $exception->getCode() ?: Api::REQUEST_FAIL;
                            $error = $exception->getMessage();
                        }
                        $response->data = [
                            'status' => false,
                            'code' => $status,
                            'msg' => $error,
                            'data' => null
                        ];
                    }
                }
            ]
        ]);
    }

    public function actions()
    {
        return yii\helpers\ArrayHelper::merge(parent::actions(), [
            'upload' => [
                'class' => PlUploaderAction::className(),
                'fileExtLimit' => 'jpg,jpeg,png',
                'fileSizeLimit' => 2 * 1024 * 1024,
                'uploader' => new LocalUploader(Yii::getAlias('@webroot/attachment/')),
                'uploadUrl' => Yii::getAlias('@web/attachment/'),
//                'prefix' => '',
                'allowAnony' => false,
                'renameFile' => true,
            ]
        ]);
    }

    public function success($data = null)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        return [
            'status' => Api::REQUEST_SUCCESS,
            'msg' => '',
            'data' => $data
        ];
    }

    public function fail($errorMessage, $errorCode = Api::REQUEST_FAIL)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        return [
            'status' => $errorCode,
            'msg' => $errorMessage,
            'data' => null
        ];
    }
}
