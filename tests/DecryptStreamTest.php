<?php


namespace Thermonuclear\WhatsappCipher;

use \PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Utils;
use ReflectionClass;

class DecryptStreamTest extends TestCase
{
    public function testConstruct()
    {
        $stream = Utils::streamFor('some test data for stream');
        $mediaKey = random_bytes(32);
        $encStream = new DecryptStream($stream, 'AUDIO', $mediaKey);

        $this->assertSame($mediaKey, $encStream->getMediaKey());

        $class = new ReflectionClass('Thermonuclear\WhatsappCipher\DecryptStream');

        $prop = $class->getProperty('stream');
        $prop->setAccessible(true);
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $prop->getValue($encStream));
    }

    public function testCreateDecryptedFile()
    {
        $pathEnc = 'enc';
        $stream = Utils::streamFor('some test data for stream');
        $encStream = new EncryptStream($stream, 'AUDIO');
        $encStream->createEncryptedFile($pathEnc);
        $mediaKey = $encStream->getMediaKey();

        $stream = Utils::streamFor(fopen($pathEnc, 'r+'));
        $encStream = new DecryptStream($stream, 'AUDIO', $mediaKey);
        $path = 'dec';
        $encStream->createDecryptedFile($path);

        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));

        unlink($pathEnc);
        unlink($path);
    }
}
