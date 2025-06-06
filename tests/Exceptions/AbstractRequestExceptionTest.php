<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\HttpClient\Exceptions\AbstractRequestException;
use JDWX\HttpClient\Exceptions\ClientException;
use JDWX\HttpClient\Exceptions\NetworkException;
use JDWX\HttpClient\Exceptions\RequestException;
use JDWX\PsrHttp\Request as PsrRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( AbstractRequestException::class )]
final class AbstractRequestExceptionTest extends TestCase {


    public function testFromWithBoth() : void {
        $srq = new PsrRequest();
        $pex = new NetworkException( $srq );
        $srq2 = new PsrRequest();
        $ex = RequestException::from( $pex, $srq2 );
        self::assertSame( $srq2, $ex->getRequest() );
    }


    public function testFromWithIntegratedRequest() : void {
        $srq = new PsrRequest();
        $pex = new NetworkException( $srq );
        $ex = RequestException::from( $pex );
        self::assertSame( $srq, $ex->getRequest() );
    }


    public function testFromWithNeither() : void {
        $pex = new ClientException( 'foo' );
        self::expectException( ClientException::class );
        RequestException::from( $pex );
    }


    public function testFromWithSameType() : void {
        $srq = new PsrRequest();
        $pex = new RequestException( $srq );
        $ex = RequestException::from( $pex );
        self::assertSame( $pex, $ex );
    }


    public function testFromWithSeparateRequest() : void {
        $pex = new ClientException( 'foo' );
        $srq = new PsrRequest();
        $ex = RequestException::from( $pex, $srq );
        self::assertSame( $srq, $ex->getRequest() );
    }


    public function testGetRequest() : void {
        $srq = new PsrRequest();
        $ex = new class( $srq ) extends AbstractRequestException {


        };
        self::assertSame( $srq, $ex->getRequest() );
    }


}
