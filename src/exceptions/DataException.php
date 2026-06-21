<?php

namespace Libelula\ErrorHandler\exceptions;

interface DataException
{

  public function getDataError(): array;
}
