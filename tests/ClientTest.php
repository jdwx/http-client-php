<?php


declare( strict_types = 1 );


use JDWX\HttpClient\Client;
use JDWX\HttpClient\Response;
use JDWX\HttpClient\Simple\SimpleFactory;
use JDWX\HttpClient\Simple\SimpleResponse;
use JDWX\HttpClient\Simple\SimpleStringStream;
use JDWX\HttpClient\Simple\SimpleUri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Support\MyTestClient;


#[CoversClass( Client::class )]
final class ClientTest extends TestCase {


    public function testGetForError500() : void {
        $backend = new MyTestClient( [ '/' => 500 ] );
        $client = new Client( $backend );
        $rsp = $client->get( '/' );
        self::assertSame( 500, $rsp->getStatusCode() );
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
        $client = new Client( $backend, new SimpleFactory() );
        $client->get( '/' );
        $req = array_shift( $backend->rRequests );
        self::assertInstanceOf( RequestInterface::class, $req );
        self::assertSame( '/', strval( $req->getUri() ) );

    }


    public function testGetForQuery() : void {
        $backend = new MyTestClient( [ '/foo' => 'TEST_CONTENT' ] );
        $client = new Client( $backend );
        $client->get( '/foo', i_itQueryParams: [
            'bar' => '1',
            'baz' => '2',
        ] );
        $req = array_shift( $backend->rRequests );
        $uri = $req->getUri();
        self::assertSame( 'bar=1&baz=2', $uri->getQuery() );
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
        $srp = new SimpleResponse( 'TEST_CONTENT' );
        $backend = new MyTestClient( [ '/foo' => $srp ] );
        $client = new Client( $backend );
        $uri = SimpleUri::from( 'https://example.com/foo?bar=1&baz=2' );
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
        $stream = new SimpleStringStream( 'TEST_BODY' );
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
        $uri = SimpleUri::from( 'https://www.example.com/foo' );
        $rsp = $client->post( $uri );
        self::assertSame( 'TEST_CONTENT', $rsp->getBody()->getContents() );
        $req = array_shift( $backend->rRequests );
        self::assertSame( 'POST', $req->getMethod() );
        self::assertSame( '/foo', $req->getRequestTarget() );
    }


}
