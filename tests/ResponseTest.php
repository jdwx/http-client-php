<?php


declare( strict_types = 1 );


use JDWX\HttpClient\Exceptions\HttpHeaderException;
use JDWX\HttpClient\Exceptions\NoBodyException;
use JDWX\HttpClient\Response;
use JDWX\PsrHttp\Request as PsrRequest;
use JDWX\PsrHttp\Response as PsrResponse;
use JDWX\PsrHttp\StringStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Support\MyTestLogger;


require_once __DIR__ . '/Support/MyTestLogger.php';


#[CoversClass( Response::class )]
final class ResponseTest extends TestCase {


    public function testBody() : void {
        $base = $this->newResponse( 'TEST_BODY' );
        self::assertSame( 'TEST_BODY', $base->body() );
    }


    public function testBodyForReadEOFNotSeekable() : void {
        $body = new StringStream( 'TEST_BODY' );
        $body->bSeekable = false;
        $base = $this->newResponse( $body );

        $body->uOffset = strlen( $body->stContents );
        self::assertTrue( $body->eof() );
        self::expectException( NoBodyException::class );
        $base->body();
    }


    public function testBodyForReadEOFSeekable() : void {
        $body = new StringStream( 'TEST_BODY' );
        $base = $this->newResponse( $body );

        $body->seek( 0, SEEK_END );
        self::assertSame( 'TEST_BODY', $base->body() );
    }


    public function testBodyForReadLateNotSeekable() : void {
        $log = new MyTestLogger();
        $body = new StringStream( 'TEST_BODY' );
        $body->bSeekable = false;
        $base = $this->newResponse( $body, i_log: $log );

        $body->read( 5 );
        self::assertSame( 'BODY', $base->body() );
        self::assertIsString( $log->message );
    }


    public function testBodyForReadLateSeekable() : void {
        $body = new StringStream( 'TEST_BODY' );
        $body->read( 5 );
        $base = $this->newResponse( $body );
        self::assertSame( 'TEST_BODY', $base->body() );
    }


    public function testBodyForReadTwiceNotSeekable() : void {
        $body = new StringStream( 'TEST_BODY' );
        $body->bSeekable = false;
        $base = $this->newResponse( $body );

        self::assertSame( 'TEST_BODY', $base->body() );
        self::assertSame( 'TEST_BODY', $base->body() );
    }


    public function testBodyForReadTwiceSeekable() : void {
        $body = new StringStream( 'TEST_BODY' );
        $body->bSeekable = true;
        $base = $this->newResponse( $body );

        self::assertSame( 'TEST_BODY', $base->body() );
        self::assertSame( 'TEST_BODY', $base->body() );
    }


    public function testGetBareContentTypeForNone() : void {
        $base = $this->newResponse();
        self::assertNull( $base->getBareContentType() );
    }


    public function testGetBareContentTypeForOne() : void {
        $rsp = new PsrResponse();
        $rsp = $rsp->withHeader( 'Content-Type', 'multipart/form-data; boundary=ExampleBoundaryString' );
        $base = $this->newResponse( $rsp );
        self::assertSame( 'multipart/form-data', $base->getBareContentType() );
    }


    public function testGetBareContentTypeForTwo() : void {
        $rsp = new PsrResponse();
        $rsp = $rsp->withHeader( 'Content-Type', [
            'application/json',
            'text/html',
        ] );
        $base = $this->newResponse( $rsp );
        self::assertNull( $base->getBareContentType() );
    }


    public function testGetHeaderOneExForNone() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::expectException( HttpHeaderException::class );
        $base->getHeaderOneEx( 'content-type' );
    }


    public function testGetHeaderOneExForOne() : void {

        $rsp = new PsrResponse();
        $rsp = $rsp->withHeader( 'Content-Type', 'application/json' );
        $base = $this->newResponse( $rsp );
        self::assertSame( 'application/json', $base->getHeaderOneEx( 'content-type' ) );
    }


    public function testGetHeaderOneExForTwo() : void {

        $rsp = new PsrResponse();
        $rsp = $rsp->withHeader( 'Content-Type', [
            'application/json',
            'text/html',
        ] );
        $base = $this->newResponse( $rsp );
        self::expectException( HttpHeaderException::class );
        $base->getHeaderOneEx( 'content-type' );
    }


    public function testGetHeaderOneForNone() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertNull( $base->getHeaderOne( 'content-type' ) );
    }


    public function testGetHeaderOneForOne() : void {

        $rsp = new PsrResponse();
        $rsp = $rsp->withHeader( 'Content-Type', 'application/json' );
        $base = $this->newResponse( $rsp );
        self::assertSame( 'application/json', $base->getHeaderOne( 'content-type' ) );
    }


    public function testGetHeaderOneForTwo() : void {

        $rsp = new PsrResponse();
        $rsp = $rsp->withHeader( 'Content-Type', [
            'application/json',
            'text/html',
        ] );
        $base = $this->newResponse( $rsp );
        self::assertNull( $base->getHeaderOne( 'content-type' ) );
    }


    public function testGetRequest() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertInstanceOf( RequestInterface::class, $base->getRequest() );
    }


    public function testGetStatusCode() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertSame( 200, $base->getStatusCode() );
    }


    public function testIsContentType() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isContentType( 'text', 'plain' ) );

        $rsp = $rsp->withHeader( 'conTEnt-TyPe', 'text/plain' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentType( 'text', 'plain' ) );
        self::assertTrue( $base->isContentType( 'text/plain' ) );
        self::assertFalse( $base->isContentType( 'text', 'html' ) );
        self::assertFalse( $base->isContentType( 'text/plainx' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain; charset=utf-8' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentType( 'text', 'plain' ) );
        self::assertTrue( $base->isContentType( 'text/plain' ) );
        self::assertFalse( $base->isContentType( 'text', 'html' ) );
        self::assertFalse( $base->isContentType( 'text/plainx' ) );
    }


    public function testIsContentTypeLoose() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isContentTypeLoose( 'text', 'plain' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeLoose( 'text', 'plain' ) );
        self::assertFalse( $base->isContentTypeLoose( 'text', 'plainx' ) );
        self::assertFalse( $base->isContentTypeLoose( 'text', 'html' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain; charset=utf-8' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeLoose( 'text', 'plain' ) );
        self::assertFalse( $base->isContentTypeLoose( 'text', 'plainx' ) );
        self::assertFalse( $base->isContentTypeLoose( 'text', 'html' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain+json' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeLoose( 'text', 'plain' ) );
        self::assertTrue( $base->isContentTypeLoose( 'text', 'json' ) );
        self::assertFalse( $base->isContentTypeLoose( 'text', 'jsonx' ) );
    }


    public function testIsContentTypeSubtype() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isContentTypeSubtype( 'plain' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeSubtype( 'plain' ) );
        self::assertFalse( $base->isContentTypeSubtype( 'html' ) );
        self::assertFalse( $base->isContentTypeSubtype( 'plainx' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain; charset=utf-8' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeSubtype( 'plain' ) );
        self::assertFalse( $base->isContentTypeSubtype( 'html' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain+json' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeSubtype( 'plain' ) );
        self::assertTrue( $base->isContentTypeSubtype( 'json' ) );
        self::assertFalse( $base->isContentTypeSubtype( 'html' ) );
        self::assertFalse( $base->isContentTypeSubtype( 'jsonx' ) );

        $rsp = $rsp->withHeader( 'content-type', 'plain/text' );
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isContentTypeSubtype( 'plain' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain/nope' );
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isContentTypeSubtype( 'plain' ) );
    }


    public function testIsContentTypeType() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isContentTypeType( 'text' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeType( 'text' ) );
        self::assertFalse( $base->isContentTypeType( 'text/plain' ) );
        self::assertFalse( $base->isContentTypeType( 'text/html' ) );
        self::assertFalse( $base->isContentTypeType( 'text/plainx' ) );

        $rsp = $rsp->withHeader( 'content-type', 'text/plain; charset=utf-8' );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isContentTypeType( 'text' ) );
        self::assertFalse( $base->isContentTypeType( 'text/plain' ) );
    }


    public function testIsError() : void {
        $srp = new PsrResponse();
        $rsp = $this->newResponse();
        self::assertFalse( $rsp->isError() );

        $srp = $srp->withStatus( 100 );
        $rsp = $this->newResponse( $srp );
        self::assertFalse( $rsp->isError() );

        $srp = $srp->withStatus( 301 );
        $rsp = $this->newResponse( $srp );
        self::assertFalse( $rsp->isError() );

        $srp = $srp->withStatus( 404 );
        $rsp = $this->newResponse( $srp );
        self::assertTrue( $rsp->isError() );

        $srp = $srp->withStatus( 500 );
        $rsp = $this->newResponse( $srp );
        self::assertTrue( $rsp->isError() );

    }


    public function testIsRedirect() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isRedirect() );

        $rsp = $rsp->withStatus( 301 );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isRedirect() );

        $rsp = $rsp->withStatus( 304 );
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isRedirect() );

        $rsp = $rsp->withStatus( 307 );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isRedirect() );
    }


    public function testIsSuccess() : void {

        $rsp = new PsrResponse();
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isSuccess() );

        $rsp = $rsp->withStatus( 100 );
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isSuccess() );

        $rsp = $rsp->withStatus( 301 );
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isSuccess() );

        $rsp = $rsp->withStatus( 304 );
        $base = $this->newResponse( $rsp );
        self::assertTrue( $base->isSuccess() );

        $rsp = $rsp->withStatus( 404 );
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isSuccess() );

        $rsp = $rsp->withStatus( 500 );
        $base = $this->newResponse( $rsp );
        self::assertFalse( $base->isSuccess() );
    }


    public function testToStringForFullBody() : void {
        $rsp = new PsrResponse( 'TEST_BODY' );
        $rsp = $rsp->withHeader( 'Content-Type', 'text/plain' );
        $base = $this->newResponse( $rsp );
        $st = strval( $base );
        self::assertStringContainsStringIgnoringCase( 'http/1.1 200', $st );
        self::assertStringContainsStringIgnoringCase( 'content-type: text/plain', $st );
        self::assertStringContainsString( 'TEST_BODY', $st );
    }


    public function testToStringForNoBody() : void {
        $body = new StringStream( 'TEST_BODY' );
        $body->seek( 0, SEEK_END );
        $body->bSeekable = false;
        self::assertTrue( $body->eof() );

        $rsp = new PsrResponse( $body );
        $base = $this->newResponse( $rsp );
        $st = strval( $base );
        self::assertStringContainsString( '[Body not available.]', $st );
    }


    public function testToStringForShortBody() : void {
        $body = new StringStream( 'TEST_BODY' );
        $body->seek( 5 );
        $body->bSeekable = false;

        $rsp = new PsrResponse( $body );
        $base = $this->newResponse( $rsp );
        $st = strval( $base );
        self::assertStringContainsString( '[...]', $st );
        self::assertStringContainsString( 'BODY', $st );
        self::assertStringNotContainsString( 'TEST_BODY', $st );
    }


    private function newResponse( ResponseInterface|StreamInterface|string|null $i_rsp = null,
                                  ?LoggerInterface                              $i_log = null ) : Response {
        if ( is_string( $i_rsp ) ) {
            $i_rsp = new StringStream( $i_rsp );
        }
        if ( $i_rsp instanceof StreamInterface ) {
            $i_rsp = new PsrResponse( $i_rsp );
        }
        if ( ! $i_rsp instanceof ResponseInterface ) {
            $i_rsp = new PsrResponse();
        }
        return new Response( new PsrRequest(), $i_rsp, $i_log );
    }


}
