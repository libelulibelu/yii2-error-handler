<?php

namespace Libelula\ErrorHandler;

use Libelula\ErrorHandler\exceptions\MessageException;
use Libelula\ErrorHandler\interfaces\ConfigRecord;
use Libelula\ErrorHandler\utils\Handler;
use Yii;
use yii\web\ErrorHandler as WebErrorHandler;

/**
 *
 */
class ErrorHandler extends WebErrorHandler
{

  /** @var string - Database connection name */
  public $bdConnection = 'mongodb';

  /** @var string */
  public $loggerComponent = '';

  /** @var string */
  public $emailConfig = 'EMAIL_ERROR_NOTIFICATION';

  /** @var string|null */
  public $configClass = null;

  /** @var \Libelula\ErrorHandler\utils\Handler */
  public $handler;

  /** @var string */
  public $empresa;

  /** @var string[] Classname for exception to not save */
  public $exceptionsNotSave = [
    MessageException::class,
  ];

  /** @var bool */
  public $saveError = false;

  /** @var bool */
  public $notificate = false;

  /** @var bool */
  public $showTrace = YII_DEBUG;

  /** @var bool */
  public $saveBody = YII_DEBUG;

  public function init()
  {
    parent::init();
    if ($this->handler === null) {
      $this->handler = new Handler($this->empresa);
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
      $this->notificate = false;
    }

    $finalResponse = $this->handler->get(
      $exception,
      $saveError,
      $this->showTrace
    );

    $notificated = $this->notificate();
    Yii::debug($notificated, __METHOD__);

    return $finalResponse;
  }

  private function notificate(): bool
  {
    if (
      $this->notificate
      && class_exists($this->configClass)
    ) {
      $className = $this->configClass;
      /** @var ConfigRecord */
      $classConfig = new $className();

      $config = $classConfig->getConfig(
        $this->emailConfig
      );

      if ($config) {
        return $this->handler->notificate(
          $config
        );
      }
    }

    return false;
  }
}
