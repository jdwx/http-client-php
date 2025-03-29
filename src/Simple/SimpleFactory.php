<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Simple;


use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;


class SimpleFactory implements RequestFactoryInterface, ResponseFactoryInterface,
    UriFactoryInterface, StreamFactoryInterface {


    public function createRequest( string $method, $uri ) : RequestInterface {
        return new SimpleRequest( stMethod: $method, i_uri: $uri );
    }


    public function createResponse( int $code = 200, string $reasonPhrase = '' ) : ResponseInterface {
        return new SimpleResponse( uStatusCode: $code, stReasonPhrase: $reasonPhrase );
    }


    public function createStream( string $content = '' ) : StreamInterface {
        return new SimpleStringStream( $content );
    }


    public function createStreamFromFile( string $filename, string $mode = 'r' ) : StreamInterface {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $bst = @file_get_contents( $filename );
        if ( false === $bst ) {
            throw new \RuntimeException( "Failed to read file: $filename" );
        }
        return $this->createStream( $bst );
    }


    /** @param resource $resource */
    public function createStreamFromResource( $resource ) : StreamInterface {
        if ( ! is_resource( $resource ) ) {
            throw new \InvalidArgumentException( 'Invalid resource provided.' );
        }
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $bst = @stream_get_contents( $resource );
        if ( false === $bst ) {
            # I don't know how to test this.
            // @codeCoverageIgnoreStart
            throw new \RuntimeException( 'Failed to read resource.' );
            // @codeCoverageIgnoreEnd
        }
        return $this->createStream( $bst );
    }


    public function createUri( string $uri = '' ) : UriInterface {
        return SimpleUri::fromString( $uri );
    }


}
