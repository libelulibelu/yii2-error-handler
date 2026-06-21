<?php

namespace Libelula\ErrorHandler\exceptions;

/**
 * Implemented by exceptions that carry structured error data (e.g. model
 * validation errors) to be merged into the error response.
 */
interface DataException
{

  /**
   * @return array Structured error data describing the failure.
   */
  public function getDataError(): array;
}
