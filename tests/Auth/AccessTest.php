<?php

namespace Plasticode\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Plasticode\Auth\Access;
use Plasticode\IO\File;
use Plasticode\Testing\Mocks\Repositories\RoleRepositoryMock;
use Plasticode\Testing\Mocks\Repositories\UserRepositoryMock;
use Plasticode\Testing\Seeders\RoleSeeder;
use Plasticode\Testing\Seeders\UserSeeder;
use Symfony\Component\Yaml\Yaml;

final class AccessTest extends TestCase
{
    public function testAccess() : void
    {
        $path = File::combine(__DIR__, 'access.yml');
        $data = File::load($path);
        $settings = Yaml::parse($data);

        $access = new Access(
            $settings
        );

        $roleRepository = new RoleRepositoryMock(
            new RoleSeeder()
        );

        $userRepository = new UserRepositoryMock(
            new UserSeeder($roleRepository)
        );

        $admin = $userRepository->get(1);
        $editor = $userRepository->get(2);
        $author = $userRepository->get(3);

        $this->assertTrue(
            $access->checkActionRights('menus', 'full', $admin)
        );

        $this->assertFalse(
            $access->checkActionRights('menus', 'read', $editor)
        );

        $this->assertFalse(
            $access->checkActionRights('menus', 'read', $author)
        );

        $this->assertTrue(
            $access->checkActionRights('roles', 'full', $admin)
        );

        $this->assertTrue(
            $access->checkActionRights('roles', 'api_read', $editor)
        );

        $this->assertTrue(
            $access->checkActionRights('roles', 'api_read', $author)
        );

        $this->assertFalse(
            $access->checkActionRights('roles', 'read', $editor)
        );

        $this->assertFalse(
            $access->checkActionRights('roles', 'read', $author)
        );
    }
}
