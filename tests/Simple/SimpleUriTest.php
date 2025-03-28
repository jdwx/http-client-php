<?php


declare( strict_types = 1 );


namespace Simple;


use InvalidArgumentException;
use JDWX\HttpClient\Simple\SimpleUri;
use PHPUnit\Framework\TestCase;


class SimpleUriTest extends TestCase {


    public function testConstructForFullUri() : void {
        self::expectException( InvalidArgumentException::class );
        $x = new SimpleUri( 'https://example.com/' );
        unset( $x );
    }


    public function testFromArray() : void {
        $uri = SimpleUri::fromArray( [
            'port' => '12345',
        ] );
        self::assertSame( 12345, $uri->nuPort );
    }


    public function testFromString() : void {
        $uri = SimpleUri::fromString( 'https://foo:bar@example.com:12345/baz/qux/?quux=1&corge=2#grault' );
        self::assertSame( 'https', $uri->stScheme );
        self::assertSame( 'example.com', $uri->stHost );
        self::assertSame( 12345, $uri->nuPort );
        self::assertSame( 'foo', $uri->stUser );
        self::assertSame( 'bar', $uri->stPassword );
        self::assertSame( '/baz/qux/', $uri->stPath );
        self::assertSame( 'quux=1&corge=2', $uri->stQuery );
        self::assertSame( 'grault', $uri->stFragment );

        $uri = SimpleUri::fromString( 'https://example.com/foo/bar?baz=1&qux=2#quux' );
        self::assertSame( 'https', $uri->stScheme );
        self::assertSame( '', $uri->stUser ); // No user info specified
        self::assertSame( '', $uri->stPassword ); // No password specified
        self::assertSame( 'example.com', $uri->stHost );
        self::assertNull( $uri->nuPort ); // No port specified
        self::assertSame( '/foo/bar', $uri->stPath ); // Path should be '/foo/bar'
        self::assertSame( 'baz=1&qux=2', $uri->stQuery );
        self::assertSame( 'quux', $uri->stFragment );

        self::expectException( InvalidArgumentException::class );
        SimpleUri::fromString( 'https:////example.com' );

    }


    public function testGetAuthority() : void {
        $uri = new SimpleUri();
        self::assertSame( '', $uri->getAuthority() );

        $uri = new SimpleUri( stUser: 'foo' );
        self::assertSame( 'foo@', $uri->getAuthority() );

        $uri = new SimpleUri( stPassword: 'bar' );
        self::assertSame( '', $uri->getAuthority() );

        $uri = new SimpleUri( stHost: 'baz' );
        self::assertSame( 'baz', $uri->getAuthority() );

        $uri = new SimpleUri( nuPort: 8080 );
        self::assertSame( '', $uri->getAuthority() );

        $uri = new SimpleUri( stHost: 'baz', nuPort: 8080 );
        self::assertSame( 'baz:8080', $uri->getAuthority() );

        $uri = new SimpleUri( stUser: 'foo', stPassword: 'bar' );
        self::assertSame( 'foo:bar@', $uri->getAuthority() );

        $uri = new SimpleUri( stUser: 'foo', stPassword: 'bar', stHost: 'baz', nuPort: 8080 );
        self::assertSame( 'foo:bar@baz:8080', $uri->getAuthority() );

        $uri = new SimpleUri( stScheme: 'HTTP', stUser: 'foo', stHost: 'baz', nuPort: 80 );
        self::assertSame( 'foo@baz', $uri->getAuthority() );

        $uri = new SimpleUri( stScheme: 'HtTpS', stUser: 'foo', stHost: 'baz', nuPort: 443 );
        self::assertSame( 'foo@baz', $uri->getAuthority() );

    }


    public function testGetFragment() : void {
        $uri = new SimpleUri();
        self::assertSame( '', $uri->getFragment() );

        $uri = new SimpleUri( stFragment: 'foo' );
        self::assertSame( 'foo', $uri->getFragment() );
    }


    public function testGetHost() : void {
        $uri = new SimpleUri();
        self::assertSame( '', $uri->getHost() );

        $uri = new SimpleUri( stHost: 'example.com' );
        self::assertSame( 'example.com', $uri->getHost() );
    }


    public function testGetPath() : void {
        $uri = new SimpleUri();
        self::assertSame( '', $uri->getPath() );

        $uri = new SimpleUri( stPath: '/a/b' );
        self::assertSame( '/a/b', $uri->getPath() );
    }


    public function testGetPort() : void {
        $uri = new SimpleUri();
        self::assertNull( $uri->getPort() );

        $uri = new SimpleUri( nuPort: 8080 );
        self::assertSame( 8080, $uri->getPort() );
    }


    public function testGetQuery() : void {
        $uri = new SimpleUri( stQuery: 'foo=1&bar=baz' );
        self::assertSame( 'foo=1&bar=baz', $uri->getQuery() );
    }


    public function testGetScheme() : void {
        $uri = new SimpleUri();
        self::assertSame( '', $uri->getScheme() );

        $uri = new SimpleUri( stScheme: 'https' );
        self::assertSame( 'https', $uri->getScheme() );

        $uri = new SimpleUri( stScheme: 'HTTP' );
        self::assertSame( 'http', $uri->getScheme() );
    }


    public function testGetUserInfo() : void {
        $uri = new SimpleUri();
        self::assertSame( '', $uri->getUserInfo() );

        $uri = new SimpleUri( stUser: 'user' );
        self::assertSame( 'user', $uri->getUserInfo() );

        $uri = new SimpleUri( stPassword: 'password' );
        self::assertSame( '', $uri->getUserInfo() );

        $uri = new SimpleUri( stUser: 'user', stPassword: 'password' );
        self::assertSame( 'user:password', $uri->getUserInfo() );

    }


    public function testToString() : void {
        $uri = new SimpleUri( stPath: '/foo/bar', stQuery: 'baz=qux' );
        self::assertSame( '/foo/bar?baz=qux', (string) $uri );

        $uri = new SimpleUri(
            stScheme: 'https', stHost: 'example.com', nuPort: 8080,
            stPath: '/foo/bar', stQuery: 'baz=qux', stFragment: 'quux'
        );
        self::assertSame( 'https://example.com:8080/foo/bar?baz=qux#quux', strval( $uri ) );

        $uri = new SimpleUri(
            stScheme: 'https', stHost: 'example.com',
            stPath: '/foo/bar', stQuery: 'baz=1&qux=2', stFragment: 'quux'
        );
        self::assertSame( 'https://example.com/foo/bar?baz=1&qux=2#quux', strval( $uri ) );
    }


    public function testWithFragment() : void {
        $uri = new SimpleUri( stFragment: 'foo' );
        $uri = $uri->withFragment( 'bar' );
        self::assertSame( 'bar', $uri->stFragment );
    }


    public function testWithHost() : void {
        $uri = new SimpleUri( stHost: 'foo' );
        $uri = $uri->withHost( 'bar' );
        self::assertSame( 'bar', $uri->stHost );
    }


    public function testWithPath() : void {
        $uri = new SimpleUri( stPath: 'foo' );
        $uri = $uri->withPath( 'bar' );
        self::assertSame( 'bar', $uri->stPath );
    }


    public function testWithPort() : void {
        $uri = new SimpleUri( nuPort: 8080 );
        $uri = $uri->withPort( 80 );
        self::assertSame( 80, $uri->nuPort );

        $uri = $uri->withPort( null );
        self::assertNull( $uri->nuPort );
    }


    public function testWithQuery() : void {
        $uri = new SimpleUri( stQuery: 'foo=1' );
        $uri = $uri->withQuery( 'bar=2' );
        self::assertSame( 'bar=2', $uri->stQuery );
    }


    public function testWithScheme() : void {
        $uri = new SimpleUri( stScheme: 'http' );
        $uri = $uri->withScheme( 'https' );
        self::assertSame( 'https', $uri->stScheme );
    }


    public function testWithUserInfo() : void {
        $uri = new SimpleUri();
        $uri = $uri->withUserInfo( 'user', 'password' );
        self::assertSame( 'user', $uri->stUser );
        self::assertSame( 'password', $uri->stPassword );
    }


}
