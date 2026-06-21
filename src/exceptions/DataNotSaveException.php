<?php

namespace Libelula\ErrorHandler\exceptions;

use yii\db\BaseActiveRecord;
use yii\web\HttpException;

/**
 * Thrown when an ActiveRecord cannot be saved (HTTP 422, code 1002). Exposes the
 * model validation errors.
 */
class DataNotSaveException extends HttpException implements DataException
{

  /** @var array Validation errors and summary for the model. */
  private $_dataError;

  /**
   * @param string $message Human-readable error message.
   * @param BaseActiveRecord $model The record that failed to save.
   */
  public function __construct(string $message, BaseActiveRecord $model)
  {
    $this->_dataError = [
      'errors' => $model->getErrors(),
      'errorSummary' => $model->getErrorSummary(true),
    ];

    parent::__construct(422, $message, 1002, null);
  }

  /**
   * @return array Validation errors and summary for the model.
   */
  public function getDataError(): array
  {
    return $this->_dataError;
  }
}
