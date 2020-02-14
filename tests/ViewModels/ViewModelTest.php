<?php

namespace Plasticode\Tests\ViewModels;

use PHPUnit\Framework\TestCase;
use Plasticode\ViewModels\SpoilerViewModel;

/**
 * @covers \Plasticode\ViewModels\ViewModel
 */
final class ViewModelTest extends TestCase
{
    public function testToArray() : void
    {
        $model = new SpoilerViewModel('123', 'some text', 'tittle');

        $this->assertEquals(
            [
                'id' => '123',
                'body' => 'some text',
                'title' => 'tittle',
            ],
            $model->toArray()
        );
    }
}
