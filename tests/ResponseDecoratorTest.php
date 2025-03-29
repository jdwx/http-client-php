<?php


declare( strict_types = 1 );


use JDWX\HttpClient\ResponseDecorator;
use JDWX\HttpClient\Simple\SimpleResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( ResponseDecorator::class )]
final class ResponseDecoratorTest extends TestCase {


    public function testGetProtocolVersion() : void {
        $rsp = new ResponseDecorator( new SimpleResponse() );
        self::assertSame( '1.1', $rsp->getProtocolVersion() );
    }


    public function testGetReasonPhrase() : void {
        $srq = new SimpleResponse( uStatusCode: 404, stReasonPhrase: 'Not Found' );
        $rsp = new ResponseDecorator( $srq );
        self::assertSame( 'Not Found', $rsp->getReasonPhrase() );
    }


    public function testGetStatusCode() : void {
        $srq = new SimpleResponse( uStatusCode: 206 );
        $rsp = new ResponseDecorator( $srq );
        self::assertSame( 206, $rsp->getStatusCode() );
    }


    public function testWithProtocolVersion() : void {
        $srq = new SimpleResponse();
        $rsp = new ResponseDecorator( $srq );
        $rsp2 = $rsp->withProtocolVersion( '2.0' );
        self::assertSame( '2.0', $rsp2->getProtocolVersion() );

        # Check that the original response is unchanged
        self::assertSame( '1.1', $rsp->getProtocolVersion() );
    }


    public function testWithStatus() : void {
        $srq = new SimpleResponse();
        $rsp = new ResponseDecorator( $srq );
        $rsp = $rsp->withStatus( 404, 'Not Found' );
        self::assertSame( 404, $rsp->getStatusCode() );
        self::assertSame( 'Not Found', $rsp->getReasonPhrase() );
    }


}
