<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Validation\ValidationResult;

final class ValidationResultTest extends TestCase
{
    public function testFail() : void
    {
        $errors = [
            'userId' => 'Cannot be empty',
            'password' => 'Must be strong',
        ];

        $result = new ValidationResult($errors);

        $this->assertEquals($errors, $result->errors());
        $this->assertTrue($result->isFail());
        $this->assertFalse($result->isSuccess());
    }

    /**
     * @dataProvider successProvider
     */
    public function testSuccess(?array $errors) : void
    {
        $result = new ValidationResult($errors);

        $this->assertEquals([], $result->errors());
        $this->assertFalse($result->isFail());
        $this->assertTrue($result->isSuccess());
    }

    public function successProvider() : array
    {
        return [
            [[]],
            [null],
        ];
    }

    public function testNullArray() : void
    {
        $result = new ValidationResult();

        $this->assertEquals([], $result->errors());
        $this->assertFalse($result->isFail());
        $this->assertTrue($result->isSuccess());
    }

    public function testThrowsException() : void
    {
        $this->expectException(ValidationException::class);

        $errors = [
            'userId' => 'Cannot be empty',
            'password' => 'Must be strong',
        ];

        $result = new ValidationResult($errors);

        $result->throwOnFail();
    }
}
