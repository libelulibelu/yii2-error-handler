<?php

namespace Libelula\ErrorHandler\models;

use Libelula\CommonHelpers\Utils;
use Libelula\ErrorHandler\ErrorHandler;
use Yii;
use yii\mongodb\ActiveRecord;

/**
 *
 * @property \MongoDB\BSON\UTCDateTime $createdAt
 * @property string $date
 * @property string $exception
 * @property array $response
 * @property string $empCodigo
 * @property string $currentUrl
 * @property <string, string> $user
 * @property string $method
 */
class Exceptions extends ActiveRecord
{

  public static function getDb()
  {
    /** @var ErrorHandler */
    $errorHandler = Yii::$app->errorHandler;

    if ($errorHandler->bdConnection) {
      $bd = Yii::$app->get($errorHandler->bdConnection, false);

      if ($bd !== null) {
        return $bd;
      }
    }

    return parent::getDb();
  }

  /**
   * @inheritdoc
   */
  public static function collectionName()
  {
    return 'exceptions';
  }

  /**
   * @inheritdoc
   */
  public function attributes()
  {
    return [
      '_id',
      'empCodigo',
      'createdAt',
      'date',
      'exception',
      'response',
      'currentUrl',
      'user',
      'method',
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [$this->attributes(), 'safe']
    ];
  }

  /**
   * @param (array|mixed)[] $response
   */
  public static function store(
    string $empCodigo,
    string $exception,
    array $response
  ): Exceptions {
    $exc = new Exceptions();
    $exc->empCodigo = $empCodigo;
    $exc->createdAt = Utils::getNowMongo();
    $exc->date = Utils::getNow();
    $exc->exception = $exception;
    $exc->response = $response;

    // Save the request information
    $request = Yii::$app->request;

    $exc->currentUrl = $request->absoluteUrl;
    $userData = [
      '_id' => Yii::$app->user->identity->_id ?? null,
      'ip' => $request->userIP,
      'host' => $request->userHost,
      'agent' => $request->userAgent,
    ];

    /** @var ErrorHandler */
    $errorHandler = Yii::$app->errorHandler;

    if ($errorHandler->saveBody) {
      $userData['body'] = $request->post();
    }

    $exc->user = $userData;
    $exc->method = $request->getMethod();

    $exc->save();

    return $exc;
  }
}
