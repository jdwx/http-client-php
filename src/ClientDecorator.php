<?php /** @noinspection PhpClassCanBeReadonlyInspection */


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class ClientDecorator implements ClientInterface {


    public function __construct( private readonly ClientInterface $client ) {}


    public function sendRequest( RequestInterface $request ) : ResponseInterface {
        return $this->client->sendRequest( $request );
    }


}
