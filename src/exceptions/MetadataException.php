<?php

namespace Libelula\ErrorHandler\exceptions;

/**
 * Implemented by exceptions that carry additional metadata (e.g. the request
 * filter or submitted data) to be merged into the response meta section.
 */
interface MetadataException
{

  /**
   * @return array Metadata describing the context of the failure.
   */
  public function getMetadataError(): array;
}
