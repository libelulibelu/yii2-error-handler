<?php

namespace Libelula\ErrorHandler\exceptions;

use yii\web\HttpException;

class MessageException extends HttpException
{

  public function __construct(String $message)
  {
    parent::__construct(400, $message, 1001, null);
  }
}
