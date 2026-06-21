<?php

namespace Libelula\ErrorHandler\exceptions;

use yii\db\BaseActiveRecord;
use yii\web\HttpException;

class DataNotSaveException extends HttpException implements DataException
{

  /** @var array */
  private $_dataError;

  public function __construct(String $message, BaseActiveRecord $model)
  {
    $this->_dataError = [
      'errors' => $model->getErrors(),
      'errorSummary' => $model->getErrorSummary(true),
    ];

    parent::__construct(422, $message, 1002, null);
  }

  public function getDataError(): array
  {
    return $this->_dataError;
  }
}
