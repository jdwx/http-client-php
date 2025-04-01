<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\HttpClient\Exceptions\ClientException;
use JDWX\HttpClient\Exceptions\RequestException;
use JDWX\PsrHttp\Request as PsrRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( ClientException::class )]
final class ClientExceptionTest extends TestCase {


    public function testFromForCompatibleType() : void {
        $srq = new PsrRequest();
        $pex = new RequestException( $srq );
        $ex = ClientException::from( $pex );
        self::assertSame( $pex, $ex );
    }


    public function testFromForSameType() : void {
        $pex = new ClientException( 'Test exception' );
        $ex = ClientException::from( $pex );
        self::assertSame( $pex, $ex );
    }


    public function testFromIncompatibleType() : void {
        $pex = new \InvalidArgumentException( 'Test exception' );
        $ex = ClientException::from( $pex );
        self::assertInstanceOf( ClientException::class, $ex );
        self::assertNotSame( $pex, $ex );
        self::assertSame( $pex, $ex->getPrevious() );
    }


}
