<?php

namespace Libelula\ErrorHandler\Tests\Exceptions;

use Libelula\ErrorHandler\exceptions\DataNotFoundException;
use Libelula\ErrorHandler\Tests\TestCase;

class DataNotFoundExceptionTest extends TestCase
{
    public function testItCarriesNotFoundStatusAndCode(): void
    {
        $exception = new DataNotFoundException('Record not found', ['id' => 42]);

        $this->assertSame(404, $exception->statusCode);
        $this->assertSame(1004, $exception->getCode());
        $this->assertSame('Record not found', $exception->getMessage());
    }

    public function testItExposesTheLookupFilterAsMetadata(): void
    {
        $filter = ['id' => 42, 'status' => 'active'];

        $exception = new DataNotFoundException('Record not found', $filter);

        $this->assertSame(['filter' => $filter], $exception->getMetadataError());
    }
}
