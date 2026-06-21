<?php

namespace Libelula\ErrorHandler;

use Libelula\ErrorHandler\exceptions\MessageException;
use Libelula\ErrorHandler\interfaces\ConfigRecord;
use Libelula\ErrorHandler\utils\Handler;
use Yii;
use yii\web\ErrorHandler as WebErrorHandler;

/**
 * Application error handler that converts exceptions into a normalized array
 * response, optionally persists them to MongoDB and notifies by email.
 *
 * Configure it as the `errorHandler` component of the Yii application. See the
 * public properties below for the available configuration options.
 */
class ErrorHandler extends WebErrorHandler
{

  /** @var string Name of the MongoDB connection component used to store exceptions. */
  public $bdConnection = 'mongodb';

  /** @var string Name of the logger component implementing {@see \Libelula\ErrorHandler\interfaces\LoggerHandler}; empty to disable. */
  public $loggerComponent = '';

  /** @var string Configuration code used to fetch the email settings from the config class. */
  public $emailConfig = 'EMAIL_ERROR_NOTIFICATION';

  /** @var string|null Fully qualified config class implementing {@see \Libelula\ErrorHandler\interfaces\ConfigRecord}. */
  public $configClass = null;

  /** @var \Libelula\ErrorHandler\utils\Handler Utility that builds the response and handles persistence/notification. */
  public $handler;

  /** @var string Company identifier stored with each persisted exception. */
  public $company;

  /** @var string[] Exception class names that must not be saved nor notified. */
  public $exceptionsNotSave = [
    MessageException::class,
  ];

  /** @var bool Whether exceptions should be persisted to the database. */
  public $saveError = false;

  /** @var bool Whether an email notification should be sent for the exception. */
  public $notify = false;

  /** @var bool Whether to include the exception trace/meta in the response. */
  public $showTrace = YII_DEBUG;

  /** @var bool Whether to store the POST body alongside the persisted exception. */
  public $saveBody = YII_DEBUG;

  public function init()
  {
    parent::init();
    if ($this->handler === null) {
      $this->handler = new Handler($this->company);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function convertExceptionToArray($exception)
  {
    if ($exception === null) {
      return [
        'transaccion' => false,
        'errorDescripcion' => Yii::t('app', 'Error not found.'),
      ];
    }

    $saveError = $this->saveError;
    if (in_array(get_class($exception), $this->exceptionsNotSave)) {
      $saveError = false;
      $this->notify = false;
    }

    $finalResponse = $this->handler->get(
      $exception,
      $saveError,
      $this->showTrace
    );

    $notified = $this->notify();
    Yii::debug($notified, __METHOD__);

    return $finalResponse;
  }

  /**
   * Sends the email notification for the current exception when enabled and a
   * valid config class is available.
   *
   * @return bool Whether a notification was actually sent.
   */
  private function notify(): bool
  {
    if (
      $this->notify
      && class_exists($this->configClass)
    ) {
      $className = $this->configClass;
      /** @var ConfigRecord */
      $classConfig = new $className();

      $config = $classConfig->getConfig(
        $this->emailConfig
      );

      if ($config) {
        return $this->handler->notify(
          $config
        );
      }
    }

    return false;
  }
}
