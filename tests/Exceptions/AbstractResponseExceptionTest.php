<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\HttpClient\Exceptions\AbstractResponseException;
use JDWX\HttpClient\Exceptions\ClientException;
use JDWX\HttpClient\Exceptions\HttpHeaderException;
use JDWX\HttpClient\Exceptions\HttpStatusException;
use JDWX\HttpClient\Exceptions\NoBodyException;
use JDWX\HttpClient\Response;
use JDWX\PsrHttp\Request as PsrRequest;
use JDWX\PsrHttp\Response as PsrResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( AbstractResponseException::class )]
final class AbstractResponseExceptionTest extends TestCase {


    public function testFromForCompatibleType() : void {
        $srq = new PsrRequest();
        $srp = new PsrResponse();
        $pex = new HttpHeaderException( $srp, $srq );
        $ex = HttpStatusException::from( $pex );
        self::assertSame( $srq, $ex->getRequest() );
        self::assertSame( $srp, $ex->getResponse() );
    }


    public function testFromForNoRequest() : void {
        $srp = new PsrResponse();
        $pex = new ClientException( 'No request provided' );
        self::expectException( ClientException::class );
        NoBodyException::from( $pex, i_response: $srp );
    }


    public function testFromForNoResponse() : void {
        $srq = new PsrRequest();
        $pex = new ClientException( 'No response provided' );
        self::expectException( ClientException::class );
        NoBodyException::from( $pex, i_request: $srq );
    }


    public function testFromForSameType() : void {
        $srq = new PsrRequest();
        $srp = new PsrResponse();
        $ex = new NoBodyException( $srp, $srq );
        $ex2 = NoBodyException::from( $ex );
        self::assertSame( $ex, $ex2 );
    }


    public function testFromForSeparateRequestAndResponse() : void {
        $srq = new PsrRequest();
        $srp = new PsrResponse();
        $pex = new ClientException();
        $ex = NoBodyException::from( $pex, $srq, $srp );
        self::assertSame( $srq, $ex->getRequest() );
        self::assertSame( $srp, $ex->getResponse() );
    }


    public function testFromForSeparateResponseOnly() : void {
        $srq = new PsrRequest();
        $srp = new PsrResponse();
        $rsp = new Response( $srq, $srp );
        $pex = new ClientException();
        $ex = NoBodyException::from( $pex, i_response: $rsp );
        self::assertSame( $srq, $ex->getRequest() );
        self::assertSame( $rsp, $ex->getResponse() );
    }


    public function testGetResponse() : void {
        $srq = new PsrRequest();
        $srp = new PsrResponse();
        $ex = new NoBodyException( $srp, $srq );
        self::assertSame( $srq, $ex->getRequest() );
        self::assertSame( $srp, $ex->getResponse() );
    }


}
