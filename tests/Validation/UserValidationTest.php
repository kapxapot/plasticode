<?php

namespace Plasticode\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Plasticode\Config\Config;
use Plasticode\Config\LocalizationConfig;
use Plasticode\Core\Factories\TranslatorFactory;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Testing\Mocks\Repositories\RoleRepositoryMock;
use Plasticode\Testing\Mocks\Repositories\UserRepositoryMock;
use Plasticode\Testing\Seeders\RoleSeeder;
use Plasticode\Testing\Seeders\UserSeeder;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;
use Respect\Validation\Validator as RespectValidator;

final class UserValidationTest extends TestCase
{
    private Validator $validator;
    private UserValidation $userValidation;

    public function setUp(): void
    {
        parent::setUp();

        RespectValidator::with('Plasticode\\Validation\\Rules\\');

        $settingsProvider = new SettingsProvider();

        $config = new Config($settingsProvider);
        $localizationConfig = new LocalizationConfig();
        $translator = (new TranslatorFactory())($config, $localizationConfig);

        $this->validator = new Validator($translator);

        $roleRepository = new RoleRepositoryMock(new RoleSeeder());

        $this->userValidation = new UserValidation(
            new ValidationRules($settingsProvider),
            new UserRepositoryMock(new UserSeeder($roleRepository))
        );
    }

    public function tearDown(): void
    {
        unset($this->userValidation);
        unset($this->validator);

        parent::tearDown();
    }

    public function testOptionalFields()
    {
        $this->userValidation
            ->withOptionalLogin()
            ->withOptionalEmail()
            ->withOptionalPassword();

        $data = [
        ];

        $rules = $this->userValidation->getRules($data);

        $result = $this->validator->validateArray($data, $rules);

        $this->assertTrue($result->isSuccess());
    }

    public function testRequiredFields()
    {
        $data = [
        ];

        $rules = $this->userValidation->getRules($data);

        $result = $this->validator->validateArray($data, $rules);

        $this->assertFalse($result->isSuccess());

        $this->assertArrayHasKey('login', $result->errors());
        $this->assertArrayHasKey('email', $result->errors());
        $this->assertArrayHasKey('password', $result->errors());
    }
}
