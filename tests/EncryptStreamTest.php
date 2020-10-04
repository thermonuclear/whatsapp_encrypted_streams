<?php


namespace Thermonuclear\WhatsappCipher;

use \PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Utils;
use ReflectionClass;

class EncryptStreamTest extends TestCase
{
    public function testConstruct()
    {
        $stream = Utils::streamFor('some test data for stream');
        $mediaKey = random_bytes(32);
        $encStream = new EncryptStream($stream, 'AUDIO', $mediaKey);

        $this->assertSame($mediaKey, $encStream->getMediaKey());

        $class = new ReflectionClass('Thermonuclear\WhatsappCipher\EncryptStream');

        $prop = $class->getProperty('stream');
        $prop->setAccessible(true);
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $prop->getValue($encStream));
    }

    public function testCreateEncryptedFile()
    {
        $path = 'enc';
        $stream = Utils::streamFor('some test data for stream');
        $encStream = new EncryptStream($stream, 'AUDIO');
        $encStream->createEncryptedFile($path);

        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));

        unlink($path);
    }
}
