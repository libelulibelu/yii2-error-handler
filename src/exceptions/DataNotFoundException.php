<?php

namespace Libelula\ErrorHandler\exceptions;

use yii\web\HttpException;

class DataNotFoundException extends HttpException implements MetadataException
{

  /** @var array */
  private $_metaDataError;

  public function __construct(String $message, array $filter)
  {
    $this->_metaDataError = [
      'filter' => $filter,
    ];

    parent::__construct(404, $message, 1004, null);
  }

  public function getMetadataError(): array
  {
    return $this->_metaDataError;
  }
}
