<?php

namespace Libelula\ErrorHandler\Tests\Exceptions;

use Libelula\ErrorHandler\exceptions\DataNotSaveException;
use Libelula\ErrorHandler\Tests\TestCase;
use yii\db\BaseActiveRecord;

class DataNotSaveExceptionTest extends TestCase
{
    public function testItCarriesUnprocessableStatusAndCode(): void
    {
        $exception = new DataNotSaveException('Could not save', $this->modelMock());

        $this->assertSame(422, $exception->statusCode);
        $this->assertSame(1002, $exception->getCode());
        $this->assertSame('Could not save', $exception->getMessage());
    }

    public function testItExposesModelErrorsAsData(): void
    {
        $exception = new DataNotSaveException('Could not save', $this->modelMock());

        $this->assertSame(
            [
                'errors' => ['name' => ['Name cannot be blank.']],
                'errorSummary' => ['Name cannot be blank.'],
            ],
            $exception->getDataError()
        );
    }

    private function modelMock(): BaseActiveRecord
    {
        $model = $this->createMock(BaseActiveRecord::class);
        $model->method('getErrors')->willReturn(['name' => ['Name cannot be blank.']]);
        $model->method('getErrorSummary')->with(true)->willReturn(['Name cannot be blank.']);

        return $model;
    }
}
