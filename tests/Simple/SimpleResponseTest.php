<?php


declare( strict_types = 1 );


namespace Simple;


use JDWX\HttpClient\Simple\SimpleResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( SimpleResponse::class )]
final class SimpleResponseTest extends TestCase {


    public function testGetReasonPhrase() : void {
        $response = new SimpleResponse();
        self::assertSame( '', $response->getReasonPhrase() );

        $response->stReasonPhrase = 'Not Found';
        self::assertSame( 'Not Found', $response->getReasonPhrase() );
    }


    public function testGetStatusCode() : void {
        $response = new SimpleResponse();
        self::assertSame( 200, $response->getStatusCode() );

        $response->uStatusCode = 404;
        self::assertSame( 404, $response->getStatusCode() );
    }


    public function testWithStatus() : void {
        $response = new SimpleResponse();
        $response = $response->withStatus( 404, 'Not Found' );
        self::assertSame( 404, $response->uStatusCode );
        self::assertSame( 'Not Found', $response->stReasonPhrase );
    }


}
