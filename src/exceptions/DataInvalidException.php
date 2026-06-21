<?php

namespace Libelula\ErrorHandler\exceptions;

use Libelula\CommonHelpers\RequestHelpers;
use yii\base\Model;
use yii\web\HttpException;

/**
 * Thrown when a model fails validation (HTTP 422, code 1003). Exposes both the
 * validation errors and the submitted request data.
 */
class DataInvalidException extends HttpException implements DataException, MetadataException
{

  /** @var array Validation errors and summary for the model. */
  private $_dataError;

  /** @var array Submitted request data that failed validation. */
  private $_metaDataError;

  /**
   * @param string $message Human-readable error message.
   * @param Model $model The model whose validation failed.
   */
  public function __construct(string $message, Model $model)
  {
    $this->_dataError = [
      'errors' => $model->getErrors(),
      'errorSummary' => $model->getErrorSummary(true),
    ];

    $this->_metaDataError = [
      'data' => RequestHelpers::getPostData(),
    ];

    parent::__construct(422, $message, 1003, null);
  }

  /**
   * @return array Validation errors and summary for the model.
   */
  public function getDataError(): array
  {
    return $this->_dataError;
  }

  /**
   * @return array Submitted request data that failed validation.
   */
  public function getMetadataError(): array
  {
    return $this->_metaDataError;
  }
}
