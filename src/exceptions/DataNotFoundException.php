<?php

namespace Libelula\ErrorHandler\exceptions;

use yii\web\HttpException;

/**
 * Thrown when a requested record cannot be found (HTTP 404, code 1004). Exposes
 * the filter used in the lookup.
 */
class DataNotFoundException extends HttpException implements MetadataException
{

  /** @var array Filter used in the failed lookup. */
  private $_metaDataError;

  /**
   * @param string $message Human-readable error message.
   * @param array $filter Filter used in the lookup that returned no records.
   */
  public function __construct(string $message, array $filter)
  {
    $this->_metaDataError = [
      'filter' => $filter,
    ];

    parent::__construct(404, $message, 1004, null);
  }

  /**
   * @return array Filter used in the failed lookup.
   */
  public function getMetadataError(): array
  {
    return $this->_metaDataError;
  }
}
