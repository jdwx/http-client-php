<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Simple;


use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;


class SimpleFactory implements RequestFactoryInterface, ResponseFactoryInterface, UriFactoryInterface {


    public function createRequest( string $method, $uri ) : RequestInterface {
        return new SimpleRequest( stMethod: $method, i_uri: $uri );
    }


    public function createResponse( int $code = 200, string $reasonPhrase = '' ) : ResponseInterface {
        return new SimpleResponse( uStatusCode: $code, stReasonPhrase: $reasonPhrase );
    }


    public function createUri( string $uri = '' ) : UriInterface {
        return SimpleUri::fromString( $uri );
    }


}
