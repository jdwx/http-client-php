<?php


declare( strict_types = 1 );


use JDWX\HttpClient\MessageDecorator;
use JDWX\PsrHttp\Response as PsrResponse;
use JDWX\PsrHttp\StringStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( MessageDecorator::class )]
final class MessageDecoratorTest extends TestCase {


    public function testGetBody() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT' );
        $msg = new MessageDecorator( $rsp );
        self::assertSame( 'TEST_CONTENT', $msg->getBody()->getContents() );
    }


    public function testGetHeader() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT', i_rHeaders: [ 'foo' => [ 'bar' ] ] );
        $msg = new MessageDecorator( $rsp );
        self::assertSame( 'bar', $msg->getHeader( 'foo' )[ 0 ] );
    }


    public function testGetHeaderLine() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT', i_rHeaders: [ 'foo' => [ 'bar', 'baz' ] ] );
        $msg = new MessageDecorator( $rsp );
        self::assertSame( 'bar, baz', $msg->getHeaderLine( 'foo' ) );
    }


    public function testGetHeaders() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT', i_rHeaders: [ 'foo' => [ 'bar' ] ] );
        $msg = new MessageDecorator( $rsp );
        self::assertSame( [ 'foo' => [ 'bar' ] ], $msg->getHeaders() );
    }


    public function testGetProtocolVersion() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT' );
        $msg = new MessageDecorator( $rsp );
        self::assertSame( '1.1', $msg->getProtocolVersion() );
    }


    public function testHasHeader() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT', i_rHeaders: [ 'foo' => [ 'bar' ] ] );
        $msg = new MessageDecorator( $rsp );
        self::assertTrue( $msg->hasHeader( 'foo' ) );
        self::assertFalse( $msg->hasHeader( 'baz' ) );
    }


    public function testWithAddedHeader() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT', i_rHeaders: [ 'foo' => [ 'bar' ] ] );
        $msg = new MessageDecorator( $rsp );
        $msg = $msg->withAddedHeader( 'foo', 'baz' );
        self::assertSame( 'bar, baz', $msg->getHeaderLine( 'foo' ) );
    }


    public function testWithBody() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT' );
        $msg = new MessageDecorator( $rsp );
        $msg = $msg->withBody( new StringStream( 'NEW_CONTENT' ) );
        self::assertSame( 'NEW_CONTENT', $msg->getBody()->getContents() );
    }


    public function testWithHeader() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT', i_rHeaders: [ 'foo' => [ 'bar' ] ] );
        $msg = new MessageDecorator( $rsp );
        $msg = $msg->withHeader( 'foo', 'baz' );
        self::assertSame( 'baz', $msg->getHeaderLine( 'foo' ) );
    }


    public function testWithProtocolVersion() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT' );
        $msg = new MessageDecorator( $rsp );
        $msg = $msg->withProtocolVersion( '2.0' );
        self::assertSame( '2.0', $msg->getProtocolVersion() );
    }


    public function testWithoutHeader() : void {
        $rsp = new PsrResponse( 'TEST_CONTENT', i_rHeaders: [
            'foo' => [ 'bar' ],
            'baz' => [ 'qux' ],
        ] );
        $msg = new MessageDecorator( $rsp );
        $msg = $msg->withoutHeader( 'foo' );
        self::assertFalse( $msg->hasHeader( 'foo' ) );
        self::assertTrue( $msg->hasHeader( 'baz' ) );
    }


}
