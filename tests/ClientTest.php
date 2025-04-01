<?php


declare( strict_types = 1 );


use JDWX\HttpClient\Client;
use JDWX\HttpClient\Exceptions\ClientException;
use JDWX\HttpClient\Exceptions\HttpStatusException;
use JDWX\HttpClient\Exceptions\NetworkException;
use JDWX\HttpClient\Exceptions\RequestException;
use JDWX\HttpClient\Response;
use JDWX\PsrHttp\Factory as PsrFactory;
use JDWX\PsrHttp\Request as PsrRequest;
use JDWX\PsrHttp\Response as PsrResponse;
use JDWX\PsrHttp\StringStream;
use JDWX\PsrHttp\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Support\MyTestClient;
use Support\MyTestLogger;


require_once __DIR__ . '/Support/MyTestClient.php';
require_once __DIR__ . '/Support/MyTestLogger.php';


#[CoversClass( Client::class )]
final class ClientTest extends TestCase {


    public function testConstructForMissingClient() : void {
        $this->expectException( InvalidArgumentException::class );
        $client = new Client();
        unset( $client );
    }


    public function testGetForError500Acceptable() : void {
        $logger = new MyTestLogger();
        $backend = new MyTestClient( [ '/' => 500 ] );
        $client = new Client( $backend, $logger );
        $client->setErrorIsAcceptable();
        $rsp = $client->get( '/' );
        self::assertSame( 500, $rsp->getStatusCode() );

        self::assertStringContainsString( 'HTTP Status', $logger->message ?? '' );
        self::assertSame( 500, $logger->context[ 'status' ] ?? null );
        self::assertSame( 'GET', $logger->context[ 'method' ] ?? null );
        self::assertSame( '/', $logger->context[ 'uri' ] ?? null );
        self::assertSame( LOG_INFO, $logger->level );
    }


    public function testGetForError500AcceptableNoLogErrors() : void {
        $logger = new MyTestLogger();
        $backend = new MyTestClient( [ '/' => 500 ] );
        $client = new Client( $backend, $logger );
        $client->setErrorIsAcceptable();
        $client->setLogErrors( false );
        $rsp = $client->get( '/' );
        self::assertSame( 500, $rsp->getStatusCode() );
        self::assertSame( LOG_DEBUG, $logger->level );
    }


    public function testGetForError500Exception() : void {
        $backend = new MyTestClient( [ '/' => 500 ] );
        $client = new Client( $backend );
        $this->expectException( HttpStatusException::class );
        $client->get( '/' );
    }


    public function testGetForError500ExceptionLog() : void {
        $logger = new MyTestLogger();
        $backend = new MyTestClient( [ '/' => 500 ] );
        $client = new Client( $backend, $logger );
        try {
            $client->get( '/' );
        } catch ( HttpStatusException ) {
        }

        self::assertStringContainsString( 'HTTP Status', $logger->message ?? '' );
        self::assertSame( 500, $logger->context[ 'status' ] ?? null );
        self::assertSame( LOG_ERR, $logger->level );

    }


    public function testGetForHeader() : void {
        $backend = new MyTestClient( [ '/' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $client->get( '/', i_itRequestHeaders: [
            'X-Test-Header' => 'TestValue',
        ] );
        $req = array_shift( $backend->rRequests );
        self::assertSame( 'TestValue', $req->getHeaderLine( 'X-Test-Header' ) );
    }


    public function testGetForProvidedFactory() : void {
        $backend = new MyTestClient( [ '/' => 'TEST_CONTENT' ] );
        $client = new Client( $backend, new PsrFactory() );
        $client->get( '/' );
        $req = array_shift( $backend->rRequests );
        self::assertInstanceOf( RequestInterface::class, $req );
        self::assertSame( '/', strval( $req->getUri() ) );

    }


    public function testGetForQuery() : void {
        $logger = new MyTestLogger();
        $backend = new MyTestClient( [ '/foo' => 'TEST_CONTENT' ] );
        $client = new Client( $backend, $logger );
        $client->get( '/foo', i_itQueryParams: [
            'bar' => '1',
            'baz' => '2',
        ] );
        $req = array_shift( $backend->rRequests );
        $uri = $req->getUri();
        self::assertSame( 'bar=1&baz=2', $uri->getQuery() );

        self::assertStringContainsString( 'HTTP Status', $logger->message ?? '' );
        self::assertSame( 200, $logger->context[ 'status' ] ?? null );
        self::assertSame( LOG_DEBUG, $logger->level );
    }


    public function testGetForQueryMerge() : void {
        $backend = new MyTestClient( [ '/foo' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $client->get( '/foo?bar=1&baz=2', i_itQueryParams: [
            'baz' => '3',
            'qux' => '4',
        ] );
        $req = array_shift( $backend->rRequests );
        $uri = $req->getUri();
        self::assertSame( 'bar=1&baz=3&qux=4', $uri->getQuery() );
    }


    public function testGetForString() : void {
        $backend = new MyTestClient( [ '/' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $rsp = $client->get( '/' );
        self::assertInstanceOf( Response::class, $rsp );
        self::assertSame( 'TEST_CONTENT', $rsp->getBody()->getContents() );
    }


    public function testGetForUri() : void {
        $srp = new PsrResponse( 'TEST_CONTENT' );
        $backend = new MyTestClient( [ '/foo' => $srp ] );
        $client = new Client( $backend );
        $uri = Uri::from( 'https://example.com/foo?bar=1&baz=2' );
        $rsp = $client->get( $uri );
        self::assertSame( 'TEST_CONTENT', $rsp->getBody()->getContents() );
    }


    public function testPostForBodyQuery() : void {
        $backend = new MyTestClient( [ '/foo' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $client->post( '/foo', i_body: [
            'bar' => '1',
            'baz' => '2',
        ] );
        $req = array_shift( $backend->rRequests );
        self::assertSame( 'bar=1&baz=2', $req->getBody()->getContents() );
        self::assertSame( 'application/x-www-form-urlencoded', $req->getHeaderLine( 'Content-Type' ) );
    }


    public function testPostForBodyStream() : void {
        $backend = new MyTestClient( [ '/' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $stream = new StringStream( 'TEST_BODY' );
        $client->post( '/', $stream );
        $req = array_shift( $backend->rRequests );
        self::assertSame( 'TEST_BODY', $req->getBody()->getContents() );
    }


    public function testPostForBodyString() : void {
        $backend = new MyTestClient( [ '/' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $rsp = $client->post( '/', 'TEST_BODY' );
        self::assertSame( 'TEST_CONTENT', $rsp->getBody()->getContents() );
        $req = array_shift( $backend->rRequests );
        self::assertSame( 'POST', $req->getMethod() );
        self::assertSame( 'TEST_BODY', $req->getBody()->getContents() );
    }


    public function testPostForString() : void {
        $backend = new MyTestClient( [ '/' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $rsp = $client->post( 'https://www.example.com/' );
        self::assertSame( 'TEST_CONTENT', $rsp->getBody()->getContents() );
        $req = array_shift( $backend->rRequests );
        self::assertSame( 'POST', $req->getMethod() );
        self::assertSame( '/', $req->getUri()->getPath() );
    }


    public function testPostForUri() : void {
        $backend = new MyTestClient( [ '/foo' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $uri = Uri::from( 'https://www.example.com/foo' );
        $rsp = $client->post( $uri );
        self::assertSame( 'TEST_CONTENT', $rsp->getBody()->getContents() );
        $req = array_shift( $backend->rRequests );
        self::assertSame( 'POST', $req->getMethod() );
        self::assertSame( '/foo', $req->getRequestTarget() );
    }


    public function testSendRequestForClientException() : void {
        $req = new PsrRequest( i_uri: '/' );
        $ex = new ClientException();
        $backend = new MyTestClient( [ '/' => $ex ] );
        $client = new Client( $backend );
        self::expectException( ClientException::class );
        $client->sendRequest( $req );
    }


    public function testSendRequestForNetworkException() : void {
        $req = new PsrRequest( i_uri: '/' );
        $ex = new NetworkException( $req );
        $backend = new MyTestClient( [ '/' => $ex ] );
        $client = new Client( $backend );
        self::expectException( NetworkException::class );
        $client->sendRequest( $req );
    }


    public function testSendRequestForRequestException() : void {
        $req = new PsrRequest( i_uri: '/' );
        $ex = new RequestException( $req );
        $backend = new MyTestClient( [ '/' => $ex ] );
        $client = new Client( $backend );
        self::expectException( RequestException::class );
        $client->sendRequest( $req );
    }


    public function testSendRequestForRuntimeError() : void {
        $req = new PsrRequest( i_uri: '/' );
        $ex = new RuntimeException( 'TEST_ERROR' );
        $backend = new MyTestClient( [ '/' => $ex ] );
        $client = new Client( $backend );
        self::expectException( ClientException::class );
        $client->sendRequest( $req );
    }


}
