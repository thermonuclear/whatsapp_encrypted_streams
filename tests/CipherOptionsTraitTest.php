<?php


namespace Thermonuclear\WhatsappCipher;


use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CipherOptionsTraitWrap
{
    use CipherOptionsTrait;
}

class CipherOptionsTraitTest extends TestCase
{
    public function testGetMediaKey()
    {
        $class = new CipherOptionsTraitWrap();

        $ref = new ReflectionClass('Thermonuclear\WhatsappCipher\CipherOptionsTraitWrap');
        $method = $ref->getMethod('setMediaKey');
        $method->setAccessible(true);
        $mediaKeySize = $ref->getProperty('mediaKeySize');
        $mediaKeySize->setAccessible(true);
        $key = random_bytes($mediaKeySize->getValue($class));

        $method->invoke($class, $key);
        $this->assertSame($key, $class->getMediaKey());

        $method->invoke($class, '');
        $this->assertSame($mediaKeySize->getValue($class), strlen($class->getMediaKey()));
    }

    public function testGetMediaKeyExpanded()
    {
        $class = new CipherOptionsTraitWrap();

        $ref = new ReflectionClass('Thermonuclear\WhatsappCipher\CipherOptionsTraitWrap');
        $method = $ref->getMethod('setMediaKey');
        $method->setAccessible(true);
        $method->invoke($class, '');

        $method = $ref->getMethod('getMediaKeyExpanded');
        $method->setAccessible(true);
        $mediaType = $ref->getProperty('mediaType');
        $mediaType->setAccessible(true);
        $mediaType->setValue($class, 'AUDIO');
        $mediaKeyExpandedSize = $ref->getProperty('mediaKeyExpandedSize');
        $mediaKeyExpandedSize->setAccessible(true);

        $mediaKeyExpanded = $method->invoke($class);
        $this->assertSame($mediaKeyExpandedSize->getValue($class), strlen($mediaKeyExpanded));
    }

    public function testSetCipherOptions()
    {
        $class = new CipherOptionsTraitWrap();
        $ref = new ReflectionClass('Thermonuclear\WhatsappCipher\CipherOptionsTraitWrap');
        $mediaType = $ref->getProperty('mediaType');
        $mediaType->setAccessible(true);
        $mediaType->setValue($class, 'AUDIO');
        $method = $ref->getMethod('setCipherOptions');
        $method->setAccessible(true);
        $method->invoke($class, '');

        $cipherOptionsSize = $ref->getProperty('cipherOptionsSize');
        $cipherOptionsSize->setAccessible(true);

        $iv = $ref->getProperty('iv');
        $iv->setAccessible(true);
        $this->assertSame($cipherOptionsSize->getValue($class)['iv'], strlen($iv->getValue($class)));

        $cipherKey = $ref->getProperty('cipherKey');
        $cipherKey->setAccessible(true);
        $this->assertSame($cipherOptionsSize->getValue($class)['cipherKey'], strlen($cipherKey->getValue($class)));

        $macKey = $ref->getProperty('macKey');
        $macKey->setAccessible(true);
        $this->assertSame($cipherOptionsSize->getValue($class)['macKey'], strlen($macKey->getValue($class)));
    }

    public function testGetMac()
    {
        $class = new CipherOptionsTraitWrap();
        $ref = new ReflectionClass('Thermonuclear\WhatsappCipher\CipherOptionsTraitWrap');
        $mediaType = $ref->getProperty('mediaType');
        $mediaType->setAccessible(true);
        $mediaType->setValue($class, 'AUDIO');
        $method = $ref->getMethod('setCipherOptions');
        $method->setAccessible(true);
        $method->invoke($class, '');

        $method = $ref->getMethod('getMac');
        $method->setAccessible(true);
        $mac = $method->invoke($class, 'some cipher text');

        $macSizeInFile = $ref->getProperty('macSizeInFile');
        $macSizeInFile->setAccessible(true);

        $this->assertSame($macSizeInFile->getValue($class), strlen($mac));
    }
}
