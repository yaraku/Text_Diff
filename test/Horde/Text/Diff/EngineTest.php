<?php

declare(strict_types=1);

namespace Horde\Text\Diff;

use Horde\Text\HordeDiff;
use PHPUnit\Framework\TestCase;

/**
 * @author     Jan Schneider <jan@horde.org>
 * @license    http://www.horde.org/licenses/gpl GPL
 * @category   Horde
 * @package    Text_Diff
 * @subpackage UnitTests
 */
class EngineTest extends TestCase
{
    protected array $_lines = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->_lines = [
            1 => file(__DIR__ . '/fixtures/1.txt'),
            2 => file(__DIR__ . '/fixtures/2.txt')];
    }

    protected function _testDiff(HordeDiff $diff): void
    {
        $edits = $diff->getDiff();
        self::assertCount(3, $edits);
        self::assertInstanceOf(Diff\Op\Copy::class, $edits[0]);
        self::assertInstanceOf(Diff\Op\Change::class, $edits[1]);
        self::assertInstanceOf(Diff\Op\Copy::class, $edits[2]);
        self::assertEquals('This line is the same.', $edits[0]->orig[0]);
        self::assertEquals('This line is the same.', $edits[0]->final[0]);
        self::assertEquals('This line is different in 1.txt', $edits[1]->orig[0]);
        self::assertEquals('This line is different in 2.txt', $edits[1]->final[0]);
        self::assertEquals('This line is the same.', $edits[2]->orig[0]);
        self::assertEquals('This line is the same.', $edits[2]->final[0]);
    }

    public function testNativeEngine(): void
    {
        $diff = new HordeDiff('Native', [$this->_lines[1], $this->_lines[2]]);
        $this->_testDiff($diff);
    }

    public function testStringEngine(): void
    {
        $patch = file_get_contents(__DIR__ . '/fixtures/unified.patch');
        $diff = new HordeDiff('String', [$patch]);
        $this->_testDiff($diff);

        $patch = file_get_contents(__DIR__ . '/fixtures/unified2.patch');
        try {
            $diff = new HordeDiff('String', [$patch]);
            self::fail('Horde_Text_Diff_Exception expected');
        } catch (DiffException $e) {
        }
        $diff = new HordeDiff('String', [$patch, 'unified']);
        $edits = $diff->getDiff();
        self::assertCount(1, $edits);
        self::assertInstanceOf(Diff\Op\Change::class, $edits[0]);
        self::assertEquals('For the first time in U.S. history number of private contractors and troops are equal', $edits[0]->orig[0]);
        self::assertEquals('Number of private contractors and troops are equal for first time in U.S. history', $edits[0]->final[0]);

        $patch = file_get_contents(__DIR__ . '/fixtures/context.patch');
        $diff = new HordeDiff('String', [$patch]);
        $this->_testDiff($diff);
    }

    public function testXdiffEngine(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $diff = new HordeDiff('Xdiff', [$this->_lines[1], $this->_lines[2]]);
            $this->_testDiff($diff);
        } catch (DiffException $e) {
            if (extension_loaded('xdiff')) {
                throw $e;
            }
        }
    }
}
