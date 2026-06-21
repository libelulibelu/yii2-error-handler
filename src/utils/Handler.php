<?php

namespace Libelula\ErrorHandler\utils;

use Error;
use Exception;
use Libelula\ErrorHandler\exceptions\DataException;
use Libelula\ErrorHandler\exceptions\MetadataException;
use Libelula\ErrorHandler\interfaces\LoggerHandler;
use Libelula\ErrorHandler\models\Exceptions;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * Builds the normalized error response for an exception, optionally persisting
 * it to the database and triggering an email notification.
 */
class Handler
{

  /** @var Exception|Error The exception currently being processed. */
  private $_exception;

  /** @var Notification Helper used to dump request data and send notifications. */
  private $notification;

  /** @var int Code of the current exception. */
  private $_code;

  /** @var string Company identifier stored with each persisted exception. */
  public $company;

  public function __construct(string $company)
  {
    $this->company = $company;
    $this->notification = new Notification();
  }

  /**
   * Stores the exception being processed and caches its code.
   *
   * @param Exception|Error $exception
   * @return void
   */
  private function init($exception)
  {
    $this->_exception = $exception;
    $this->_code = $exception->getCode();
  }

  /**
   * Builds the error response, persisting it to the database when requested.
   *
   * @param Exception|Error $exception The exception to process.
   * @param bool $saveError Whether the exception must be stored in the database.
   * @param bool $showTrace Whether the response must include the meta/trace data.
   * @return array The error response to return to the client.
   */
  public function get(
    $exception,
    bool $saveError,
    bool $showTrace = YII_DEBUG
  ): array {
    $this->init($exception);
    switch ($this->_code) {
      case 401:
        $response = $this->unauthorized();
        break;

      case 1001:
        $response = $this->message();
        break;

      default:
        $response = $this->common();
        break;
    }

    if ($saveError) {
      $record = Exceptions::store(
        $this->company,
        get_class($this->_exception),
        ArrayHelper::merge($response, [
          'meta' => $this->meta()
        ])
      );

      $this->notification->writeFile(
        'exception_data.txt',
        $record->toArray()
      );
    }

    // Return with meta data error
    if ($showTrace) {
      return ArrayHelper::merge($response, [
        'meta' => $this->meta()
      ]);
    }
    return $response;
  }

  /**
   * Sends the email notification for the current exception.
   *
   * @param array $emailConfig Email settings used to deliver the notification.
   * @return bool Whether the notification was sent successfully.
   */
  public function notify(array $emailConfig): bool
  {
    $uidAction = Yii::$app->controller->action->getUniqueId();
    return $this->notification->send(
      "ERROR | Servicio ({$uidAction})",
      $emailConfig
    );
  }

  /**
   * Builds the response for unauthorized (HTTP 401) errors.
   *
   * @return array
   */
  private function unauthorized()
  {
    return [
      'transaccion' => false,
      'errorDescripcion' => Yii::t('app', 'Not authorized for this actions.'),
    ];
  }

  /**
   * Builds the response for generic errors, merging extra data when the
   * exception implements {@see DataException}.
   *
   * @return array
   */
  private function common(): array
  {
    $error = [
      'transaccion' => false,
      'errorDescripcion' => Yii::t('app', 'An error occurred while processing your request.'),
      'rawError' => $this->_exception->getMessage(),
    ];

    if ($this->_exception instanceof DataException) {
      $error = ArrayHelper::merge($error, $this->_exception->getDataError());
    }

    return $error;
  }

  /**
   * Builds the response for message exceptions (HTTP 400 / code 1001),
   * exposing the exception message directly.
   *
   * @return array
   */
  private function message(): array
  {
    return [
      'transaccion' => false,
      'errorDescripcion' => $this->_exception->getMessage(),
    ];
  }

  /**
   * Collects metadata about the exception (class, file, line, trace, logger and
   * any data provided by {@see MetadataException}).
   *
   * @return array
   */
  private function meta(): array
  {
    $exception = $this->_exception;
    $meta = [
      'exception' => $exception->getMessage(),
      'class'     => get_class($exception),
      'file'      => $exception->getFile(),
      'line'      => $exception->getLine(),
      'trace'     => explode("\n", $exception->getTraceAsString()),
      'logger'    => $this->loggerInfo(),
    ];

    if ($exception instanceof HttpException) {
      $meta['status'] = $exception->statusCode;
    }

    if ($this->_exception instanceof MetadataException) {
      $meta = ArrayHelper::merge($meta, $this->_exception->getMetadataError());
    }

    return $meta;
  }

  /**
   * Returns the request info exposed by the configured logger component, or
   * null when no logger is configured or it does not implement
   * {@see LoggerHandler}.
   *
   * @return array|null
   */
  private function loggerInfo(): ?array
  {
    /** @var \Libelula\ErrorHandler\ErrorHandler */
    $errorHandler = Yii::$app->errorHandler;

    if (empty($errorHandler->loggerComponent)) {
      return null;
    }

    $logger = Yii::$app->get($errorHandler->loggerComponent, false);

    if ($logger instanceof LoggerHandler) {
      return [
        'uid'     => $logger->getRequestUid(),
        'startAt' => $logger->getRequestStartDateTime()->format('H:i:s Y-m-d'),
      ];
    }

    return null;
  }
}
