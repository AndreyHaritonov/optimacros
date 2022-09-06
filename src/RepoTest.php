<?php declare(strict_types=1);

namespace Andrey\Optimacros;

use PHPUnit\Framework\TestCase;

final class StackTest extends TestCase
{
    public function testWithExample(): void
    {
        $repo = new Repo();
        $repo->loadFromFile(__DIR__ . '/../examples/input.csv');
        $actual = $repo->toArray();

        $data = file_get_contents(__DIR__ . '/../examples/output.json');
        $expected = json_decode($data, true);

        $this->assertEquals($expected, $actual);
    }
}