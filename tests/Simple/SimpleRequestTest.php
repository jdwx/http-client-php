<?php


declare( strict_types = 1 );


namespace Simple;


use JDWX\HttpClient\Simple\SimpleRequest;
use JDWX\HttpClient\Simple\SimpleUri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( SimpleRequest::class )]
final class SimpleRequestTest extends TestCase {


    public function testConstructForUri() : void {
        $st = 'https://example.com/a/b?foo=1&bar=baz#qux';
        $uri = SimpleUri::fromString( $st );
        $srq = new SimpleRequest( i_uri: $uri );
        self::assertSame( strval( $uri ), strval( $srq->uri ) );
    }


    public function testConstructForUriString() : void {
        $st = 'https://example.com/a/b?foo=1&bar=baz#qux';
        $srq = new SimpleRequest( i_uri: $st );
        self::assertSame( $st, strval( $srq->uri ) );
    }


    public function testGetMethod() : void {
        $request = new SimpleRequest();
        self::assertSame( 'GET', $request->getMethod() );

        $request->stMethod = 'POST';
        self::assertSame( 'POST', $request->getMethod() );
    }


    public function testGetRequestTarget() : void {
        $request = new SimpleRequest();
        self::assertSame( '/', $request->getRequestTarget() );

        $request->uri = SimpleUri::fromString( 'https://example.com/a/b?foo=1&bar=baz' );
        self::assertSame( '/a/b?foo=1&bar=baz', $request->getRequestTarget() );

        $request->nstRequestTarget = '/c/d';
        self::assertSame( '/c/d', $request->getRequestTarget() );
    }


    public function testGetUri() : void {
        $request = new SimpleRequest();
        self::assertSame( '/', $request->getUri()->getPath() );

        $request->uri = SimpleUri::fromString( 'https://example.com/a/b?foo=1&bar=baz' );
        self::assertSame( '/a/b', $request->getUri()->getPath() );
    }


    public function testWithMethod() : void {
        $req = new SimpleRequest();
        self::assertSame( 'GET', $req->stMethod );
        $req = $req->withMethod( 'POST' );
        self::assertSame( 'POST', $req->stMethod );
    }


    public function testWithRequestTarget() : void {
        $req = new SimpleRequest();
        self::assertNull( $req->nstRequestTarget );
        $req = $req->withRequestTarget( '/a/b' );
        self::assertSame( '/a/b', $req->nstRequestTarget );
    }


    public function testWithUri() : void {
        $req = new SimpleRequest();
        self::assertNull( $req->uri );
        $uri = SimpleUri::fromString( 'https://example.com/a/b?foo=1&bar=baz#qux' );
        $req = $req->withUri( $uri );
        self::assertSame( strval( $uri ), strval( $req->uri ) );
    }


}
