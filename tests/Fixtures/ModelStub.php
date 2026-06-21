<?php

namespace Libelula\ErrorHandler\Tests\Fixtures;

use yii\base\Model;

/**
 * Minimal model fixture used to produce real validation errors for the
 * exception tests.
 */
class ModelStub extends Model
{
    /** @var string|null */
    public $name;

    public function rules(): array
    {
        return [
            [['name'], 'required'],
        ];
    }
}
