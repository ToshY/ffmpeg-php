<?php
namespace Char0n\FFMpegPHP\Tests\OutputProviders;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\Timer\Timer;
use Char0n\FFMpegPHP\OutputProviders\FFProbeProvider;

class FFProbeProviderTest extends TestCase
{

    protected static $moviePath;
    /**
     * @var FFProbeProvider
     */
    protected $provider;

    public static function setUpBeforeClass(): void
    {
        $path = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
        self::$moviePath = realpath($path.'test.mp4');
    }

    public static function tearDownAfterClass(): void
    {
        self::$moviePath   = null;
    }

    public function setUp(): void
    {
        $this->provider = new FFProbeProvider();
        $this->provider->setMovieFile(self::$moviePath);
    }

    public function tearDown(): void
    {
        $this->provider = null;
    }

    public function testGetOutput()
    {
        $output = $this->provider->getOutput();
        $this->assertEquals(1, preg_match('/FFprobe version/i', $output));
    }

    public function testGetOutputFileDoesntExist()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(334561);

        $provider = new FFProbeProvider();
        $provider->setMovieFile(uniqid('test', true));
        $provider->getOutput();
    }

    public function testPersistentResourceSimulation()
    {
        Timer::start();
        $provider = new FFProbeProvider('ffprobe', true);
        $provider->setMovieFile(self::$moviePath);
        $provider->getOutput();
        $provider = clone $provider;
        $provider->getOutput();
        $provider = clone $provider;
        $provider->getOutput();
        $elapsed = Timer::stop();

        Timer::start();
        $provider = new FFProbeProvider('ffprobe', false);
        $provider->setMovieFile(self::$moviePath);
        $provider->getOutput();
        $provider = clone $provider;
        $provider->getOutput();
        $provider = clone $provider;
        $provider->getOutput();
        $elapsed1 = Timer::stop();
        $this->assertGreaterThan($elapsed, $elapsed1, 'Persistent resource simulation should be faster');
    }

    public function testSerializeUnserialize()
    {
        $output = $this->provider->getOutput();
        $serialized  = serialize($this->provider);
        $this->provider = null;
        $this->provider = unserialize($serialized);
        $this->assertEquals(
            $output,
            $this->provider->getOutput(),
            'Output from original and unserialized provider should be equal'
        );
    }
}
