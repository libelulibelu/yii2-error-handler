<?php

namespace Libelula\ErrorHandler\exceptions;

use yii\web\HttpException;

/**
 * Thrown to return a plain message to the client (HTTP 400, code 1001). Its
 * message is exposed directly and it is excluded from persistence/notification
 * by default.
 */
class MessageException extends HttpException
{

  /**
   * @param string $message Human-readable message returned to the client.
   */
  public function __construct(string $message)
  {
    parent::__construct(400, $message, 1001, null);
  }
}
