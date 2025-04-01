<?php


declare( strict_types = 1 );


use JDWX\HttpClient\RequestDecorator;
use JDWX\PsrHttp\Request as PsrRequest;
use JDWX\PsrHttp\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( RequestDecorator::class )]
final class RequestDecoratorTest extends TestCase {


    public function testGetMethod() : void {
        $req = new PsrRequest();
        $req = new RequestDecorator( $req );
        self::assertSame( 'GET', $req->getMethod() );
    }


    public function testGetProtocolVersion() : void {
        $req = new PsrRequest();
        $req = new RequestDecorator( $req );
        self::assertSame( '1.1', $req->getProtocolVersion() );
    }


    public function testGetRequestTarget() : void {
        $req = new PsrRequest();
        $req = new RequestDecorator( $req );
        self::assertSame( '/', $req->getRequestTarget() );
    }


    public function testGetUri() : void {
        $stUri = 'https://example.com/foo/bar?baz=1&qux=2#quux';
        $srq = new PsrRequest( i_uri: $stUri );
        $req = new RequestDecorator( $srq );
        self::assertSame( $stUri, strval( $req->getUri() ) );
    }


    public function testWithMethod() : void {
        $req = new PsrRequest();
        $req = new RequestDecorator( $req );
        $req = $req->withMethod( 'POST' );
        self::assertSame( 'POST', $req->getMethod() );
    }


    public function testWithProtocolVersion() : void {
        $req = new PsrRequest();
        $req = new RequestDecorator( $req );
        $req = $req->withProtocolVersion( '2.0' );
        self::assertSame( '2.0', $req->getProtocolVersion() );
    }


    public function testWithRequestTarget() : void {
        $req = new PsrRequest();
        $req = new RequestDecorator( $req );
        $req = $req->withRequestTarget( '/foo' );
        self::assertSame( '/foo', $req->getRequestTarget() );
    }


    public function testWithUri() : void {
        $req = new RequestDecorator( new PsrRequest() );
        $stUri = 'https://example.com/foo/bar?baz=1&qux=2#quux';
        $uri = Uri::fromString( $stUri );
        $req = $req->withUri( $uri );
        self::assertSame( $stUri, strval( $uri ) );
        self::assertSame( $stUri, strval( $req->getUri() ) );
    }


}
