<?php

namespace Libelula\ErrorHandler\models;

use Libelula\CommonHelpers\Utils;
use Libelula\ErrorHandler\ErrorHandler;
use Yii;
use yii\mongodb\ActiveRecord;

/**
 * MongoDB ActiveRecord that persists handled exceptions in the `exceptions`
 * collection.
 *
 * @property \MongoDB\BSON\UTCDateTime $createdAt
 * @property string $date
 * @property string $exception
 * @property array $response
 * @property string $empCodigo Company identifier.
 * @property string $currentUrl
 * @property array{_id: ?string, ip: string, host: string, agent: string, body?: array} $user
 * @property string $method
 */
class Exceptions extends ActiveRecord
{

  /**
   * @inheritdoc
   */
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
  public static function collectionName(): string
  {
    return 'exceptions';
  }

  /**
   * @inheritdoc
   */
  public function attributes(): array
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
  public function rules(): array
  {
    return [
      [$this->attributes(), 'safe']
    ];
  }

  /**
   * Persists a handled exception together with the current request context.
   *
   * @param string $company Company identifier stored in the `empCodigo` field.
   * @param string $exception Fully qualified class name of the exception.
   * @param (array|mixed)[] $response Normalized response data to store.
   * @return Exceptions The saved record.
   */
  public static function store(
    string $company,
    string $exception,
    array $response
  ): Exceptions {
    $exc = new Exceptions();
    $exc->empCodigo = $company;
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
