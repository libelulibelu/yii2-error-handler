<?php

namespace Libelula\ErrorHandler\exceptions;

use Libelula\CommonHelpers\RequestHelpers;
use yii\base\Model;
use yii\web\HttpException;

class DataInvalidException extends HttpException implements DataException, MetadataException
{

  /** @var array */
  private $_dataError;

  private $_metaDataError;

  public function __construct(String $message, Model $model)
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

  public function getDataError(): array
  {
    return $this->_dataError;
  }

  public function getMetadataError(): array
  {
    return $this->_metaDataError;
  }
}
