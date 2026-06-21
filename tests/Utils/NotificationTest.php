<?php

namespace Libelula\ErrorHandler\Tests\Utils;

use Libelula\ErrorHandler\Tests\TestCase;
use Libelula\ErrorHandler\utils\Notification;

class NotificationTest extends TestCase
{
    /** @var string[] Temporary files created during a test, removed in teardown. */
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->createdFiles = [];

        parent::tearDown();
    }

    public function testWriteFileStoresContentAndReturnsTrue(): void
    {
        $notification = new Notification();

        $result = $notification->writeFile('data.txt', ['hello' => 'world']);

        $this->assertTrue($result);

        $files = $this->getPrivateProperty($notification, 'files');
        $this->assertArrayHasKey('data.txt', $files);

        $path = $files['data.txt'];
        $this->createdFiles[] = $path;

        $this->assertFileExists($path);
        $this->assertSame(['hello' => 'world'], json_decode(file_get_contents($path), true));
    }

    public function testGetFileNameUsesTempDirAndPreservesExtension(): void
    {
        $notification = new Notification();

        $path = $this->invokePrivate($notification, 'getFileName', ['report.JSON']);

        $this->assertStringStartsWith(sys_get_temp_dir(), $path);
        $this->assertStringEndsWith('.json', $path);
        $this->assertStringContainsString('file_', $path);
    }
}
