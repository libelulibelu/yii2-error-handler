<?php

namespace Libelula\ErrorHandler\Tests\Exceptions;

use Libelula\ErrorHandler\exceptions\MessageException;
use Libelula\ErrorHandler\Tests\TestCase;

class MessageExceptionTest extends TestCase
{
    public function testItCarriesBadRequestStatusAndCode(): void
    {
        $exception = new MessageException('Invalid input');

        $this->assertSame(400, $exception->statusCode);
        $this->assertSame(1001, $exception->getCode());
        $this->assertSame('Invalid input', $exception->getMessage());
    }
}
