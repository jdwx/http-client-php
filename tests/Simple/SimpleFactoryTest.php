<?php


declare( strict_types = 1 );


namespace Simple;


use JDWX\HttpClient\Simple\SimpleFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;


#[CoversClass( SimpleFactory::class )]
final class SimpleFactoryTest extends TestCase {


    public function testCreateRequest() : void {
        $fac = new SimpleFactory();
        $req = $fac->createRequest( 'GET', '/' );
        self::assertInstanceOf( RequestInterface::class, $req );
        self::assertSame( 'GET', $req->getMethod() );
        self::assertSame( '/', strval( $req->getUri() ) );
    }


    public function testCreateResponse() : void {
        $fac = new SimpleFactory();
        $rsp = $fac->createResponse( 404, 'Not Found' );
        self::assertInstanceOf( ResponseInterface::class, $rsp );
        self::assertSame( 404, $rsp->getStatusCode() );
        self::assertSame( 'Not Found', $rsp->getReasonPhrase() );
    }


    public function testCreateStream() : void {
        $fac = new SimpleFactory();
        $stream = $fac->createStream( 'TEST_CONTENT' );
        self::assertInstanceOf( StreamInterface::class, $stream );
        self::assertSame( 'TEST_CONTENT', $stream->getContents() );
    }


    public function testCreateStreamFile() : void {
        $fac = new SimpleFactory();
        $stFilePath = __DIR__ . '/../Support/content.txt';
        $stream = $fac->createStreamFromFile( $stFilePath );
        self::assertInstanceOf( StreamInterface::class, $stream );
        self::assertSame( 'TEST_CONTENT_FILE', $stream->getContents() );
    }


    public function testCreateStreamFileForNoFile() : void {
        $fac = new SimpleFactory();
        $stFilePath = __DIR__ . '/../Support/NO_FILE.txt';
        $this->expectException( \RuntimeException::class );
        $fac->createStreamFromFile( $stFilePath );
    }


    public function testCreateStreamResource() : void {
        $fac = new SimpleFactory();
        $stFilePath = __DIR__ . '/../Support/content.txt';
        $resource = fopen( $stFilePath, 'r' );
        self::assertIsResource( $resource );
        $stream = $fac->createStreamFromResource( $resource );
        self::assertInstanceOf( StreamInterface::class, $stream );
        self::assertSame( 'TEST_CONTENT_FILE', $stream->getContents() );
    }


    /** @suppress PhanTypeMismatchArgumentProbablyReal */
    public function testCreateStreamResourceForInvalidType() : void {
        $fac = new SimpleFactory();
        $this->expectException( \InvalidArgumentException::class );
        /**
         * @noinspection PhpParamsInspection
         * @phpstan-ignore-next-line
         */
        $fac->createStreamFromResource( 'invalid_resource' );
    }


    public function testCreateUri() : void {
        $fac = new SimpleFactory();
        $stUri = 'https://example.com/foo?bar=1&baz=2';
        $uri = $fac->createUri( $stUri );
        self::assertInstanceOf( UriInterface::class, $uri );
        self::assertSame( $stUri, strval( $uri ) );
    }


}
