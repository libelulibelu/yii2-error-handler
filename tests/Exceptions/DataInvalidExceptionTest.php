<?php

namespace Libelula\ErrorHandler\Tests\Exceptions;

use Libelula\ErrorHandler\exceptions\DataInvalidException;
use Libelula\ErrorHandler\Tests\Fixtures\ModelStub;
use Libelula\ErrorHandler\Tests\TestCase;
use Yii;

class DataInvalidExceptionTest extends TestCase
{
    public function testItCarriesUnprocessableStatusAndCode(): void
    {
        $this->mockWebApplication();

        $exception = new DataInvalidException('Validation failed', $this->invalidModel());

        $this->assertSame(422, $exception->statusCode);
        $this->assertSame(1003, $exception->getCode());
        $this->assertSame('Validation failed', $exception->getMessage());
    }

    public function testItExposesModelErrorsAsData(): void
    {
        $this->mockWebApplication();

        $exception = new DataInvalidException('Validation failed', $this->invalidModel());
        $data = $exception->getDataError();

        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('errorSummary', $data);
        $this->assertArrayHasKey('name', $data['errors']);
    }

    public function testItExposesSubmittedDataAsMetadata(): void
    {
        $this->mockWebApplication();
        Yii::$app->request->setBodyParams(['name' => '', 'extra' => 'value']);

        $exception = new DataInvalidException('Validation failed', $this->invalidModel());

        $this->assertSame(
            ['data' => ['name' => '', 'extra' => 'value']],
            $exception->getMetadataError()
        );
    }

    private function invalidModel(): ModelStub
    {
        $model = new ModelStub();
        $model->validate();

        return $model;
    }
}
