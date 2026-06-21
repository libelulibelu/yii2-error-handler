<?php

namespace Libelula\ErrorHandler\Tests\Utils;

use Exception;
use Libelula\ErrorHandler\exceptions\DataException;
use Libelula\ErrorHandler\exceptions\MessageException;
use Libelula\ErrorHandler\Tests\Fixtures\StubLogger;
use Libelula\ErrorHandler\Tests\TestCase;
use Libelula\ErrorHandler\utils\Handler;
use PHPUnit\Framework\Attributes\DataProvider;

class HandlerTest extends TestCase
{
    #[DataProvider('branchProvider')]
    public function testGetBuildsTheResponseForEachBranch(Exception $exception, array $expected): void
    {
        $this->bootApp();
        $handler = new Handler('TEST');

        $response = $handler->get($exception, false, false);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $response);
            $this->assertSame($value, $response[$key]);
        }
    }

    public static function branchProvider(): array
    {
        return [
            'unauthorized when code is 401' => [
                new Exception('nope', 401),
                [
                    'transaccion' => false,
                    'errorDescripcion' => 'Not authorized for this actions.',
                ],
            ],
            'message exception exposes its message' => [
                new MessageException('Custom message'),
                [
                    'transaccion' => false,
                    'errorDescripcion' => 'Custom message',
                ],
            ],
            'generic exception uses the common response' => [
                new Exception('boom'),
                [
                    'transaccion' => false,
                    'errorDescripcion' => 'An error occurred while processing your request.',
                    'rawError' => 'boom',
                ],
            ],
        ];
    }

    public function testGetMergesDataFromDataExceptions(): void
    {
        $this->bootApp();
        $exception = new class('invalid') extends Exception implements DataException {
            public function getDataError(): array
            {
                return ['errors' => ['field' => 'required']];
            }
        };

        $response = (new Handler('TEST'))->get($exception, false, false);

        $this->assertSame(['field' => 'required'], $response['errors']);
    }

    public function testGetIncludesMetaWhenShowTraceIsEnabled(): void
    {
        $this->bootApp();

        $response = (new Handler('TEST'))->get(new Exception('boom'), false, true);

        $this->assertArrayHasKey('meta', $response);
        foreach (['exception', 'class', 'file', 'line', 'trace', 'logger'] as $key) {
            $this->assertArrayHasKey($key, $response['meta']);
        }
        $this->assertNull($response['meta']['logger'], 'Logger is null when no logger component is configured.');
    }

    public function testGetOmitsMetaWhenShowTraceIsDisabled(): void
    {
        $this->bootApp();

        $response = (new Handler('TEST'))->get(new Exception('boom'), false, false);

        $this->assertArrayNotHasKey('meta', $response);
    }

    public function testMetaIncludesLoggerInfoWhenLoggerComponentIsConfigured(): void
    {
        $this->mockWebApplication([
            'components' => [
                'errorHandler' => $this->errorHandlerConfig(['loggerComponent' => 'requestLogger']),
                'requestLogger' => ['class' => StubLogger::class],
            ],
        ]);

        $response = (new Handler('TEST'))->get(new Exception('boom'), false, true);

        $this->assertSame(
            [
                'uid' => StubLogger::UID,
                'startAt' => '10:20:30 2026-06-21',
            ],
            $response['meta']['logger']
        );
    }

    private function bootApp(): void
    {
        $this->mockWebApplication([
            'components' => [
                'errorHandler' => $this->errorHandlerConfig(),
            ],
        ]);
    }
}
