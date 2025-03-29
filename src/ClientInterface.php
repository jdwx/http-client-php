<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;


interface ClientInterface extends \Psr\Http\Client\ClientInterface {


    public function get( UriInterface|string $i_uri, iterable $i_itQueryParams = [],
                         array               $i_itRequestHeaders = [] ) : ResponseInterface;


    public function post( UriInterface|string $i_uri, iterable|string|StreamInterface $i_body = '',
                          array               $i_rHeaders = [] ) : ResponseInterface;


    public function setErrorIsAcceptable( bool $i_bErrorIsAcceptable = true ) : static;


    public function setLogErrors( bool $i_bLogErrors = true ) : static;


}