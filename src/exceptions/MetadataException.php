<?php

namespace Libelula\ErrorHandler\exceptions;

interface MetadataException
{

  public function getMetadataError(): array;
}
