<?php


declare( strict_types = 1 );


namespace Support;


use JDWX\HttpClient\Simple\SimpleResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;


class MyTestClient implements ClientInterface {


    /** @var list<RequestInterface> Copy of all requests received. */
    public array $rRequests = [];


    /**
     * @param array<string, ResponseInterface|int|string|Throwable> $rResponses Map of path => response.
     *
     * If the response is an int it will return an error response with the
     * given status code.
     *
     * If the response is a string, it will be the body content of an otherwise
     * default response.
     *
     * If the response is a ResponseInterface, it will be returned as-is.
     *
     * If the response is a Throwable, it will be thrown.
     */
    public function __construct( public array $rResponses = [] ) { }


    public function error404() : ResponseInterface {
        return ( new SimpleResponse( '404 Not Found', uStatusCode: 404, stReasonPhrase: 'Not Found' ) )
            ->withHeader( 'Content-Type', 'text/plain' )
        ;
    }


    public function sendRequest( RequestInterface $request ) : ResponseInterface {
        $this->rRequests[] = $request;
        $stPath = $request->getRequestTarget();

        # Remove query string, if present.
        $stPath = explode( '?', $stPath, 2 )[ 0 ];

        if ( ! isset( $this->rResponses[ $stPath ] ) ) {
            return $this->error404();
        }

        $rsp = $this->rResponses[ $stPath ];
        if ( is_int( $rsp ) ) {
            return new SimpleResponse( 'Oh no error!', uStatusCode: $rsp, stReasonPhrase: 'Simulated error' );
        }
        if ( is_string( $rsp ) ) {
            return new SimpleResponse( $rsp );
        }
        if ( $rsp instanceof ResponseInterface ) {
            return $rsp;
        }
        throw $rsp;
    }


}
