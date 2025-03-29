<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use JDWX\HttpClient\Simple\SimpleFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;


class Client extends ClientDecorator implements ClientInterface {


    private RequestFactoryInterface $requestFactory;

    private UriFactoryInterface $uriFactory;

    private StreamFactoryInterface $streamFactory;


    public function __construct( \Psr\Http\Client\ClientInterface $i_client,
                                 object                           ...$i_rFactories ) {
        parent::__construct( $i_client );

        $this->requestFactory = self::pickFactory( $i_rFactories, RequestFactoryInterface::class );
        $this->uriFactory = self::pickFactory( $i_rFactories, UriFactoryInterface::class );
        $this->streamFactory = self::pickFactory( $i_rFactories, StreamFactoryInterface::class );

    }


    /**
     * @param iterable $i_rFactories
     * @param string $i_stInterface
     * @return object
     */
    private static function pickFactory( iterable $i_rFactories, string $i_stInterface ) : object {
        static $facDefault = null;
        foreach ( $i_rFactories as $factory ) {
            if ( $factory instanceof $i_stInterface ) {
                return $factory;
            }
        }
        if ( null === $facDefault ) {
            $facDefault = new SimpleFactory();
        }
        assert( is_subclass_of( $facDefault, $i_stInterface ) );
        return $facDefault;
    }


    /**
     * @param iterable<string, string|int|float> $i_itQueryParams
     * @param iterable<string, string|list<string>> $i_itRequestHeaders
     */
    public function get( UriInterface|string $i_uri, iterable $i_itQueryParams = [],
                         iterable            $i_itRequestHeaders = [] ) : ResponseInterface {
        if ( is_string( $i_uri ) ) {
            $i_uri = $this->uriFactory->createUri( $i_uri );
        }
        if ( ! empty( $i_itQueryParams ) ) {
            $stQuery = $i_uri->getQuery();
            $rQuery = [];
            parse_str( $stQuery, $rQuery );
            $rQuery = array_merge( $rQuery, iterator_to_array( $i_itQueryParams ) );
            $stQuery = http_build_query( $rQuery );
            $i_uri = $i_uri->withQuery( $stQuery );
        }
        $req = $this->requestFactory->createRequest( 'GET', $i_uri );
        $req = $this->addHeadersToRequest( $req, $i_itRequestHeaders );
        return $this->sendRequest( $req );
    }


    /**
     * @param iterable<string, string|int|float>|string|StreamInterface $i_body
     * @param iterable<string, string|list<string>> $i_rHeaders
     *
     * Note that if you don't provide an iterable body, you're responsible for
     * setting Content-Type correctly in the provided headers.
     */
    public function post( UriInterface|string $i_uri, iterable|string|StreamInterface|\Stringable $i_body = '',
                          iterable            $i_rHeaders = [] ) : ResponseInterface {
        if ( is_string( $i_uri ) ) {
            $i_uri = $this->uriFactory->createUri( $i_uri );
        }
        $req = $this->requestFactory->createRequest( 'POST', $i_uri );
        if ( is_iterable( $i_body ) ) {
            $req = $this->addIterableToBody( $req, $i_body );
        } elseif ( $i_body instanceof StreamInterface ) {
            $req = $req->withBody( $i_body );
        } else {
            $req = $req->withBody( $this->streamFactory->createStream( $i_body ) );
        }
        $req = $this->addHeadersToRequest( $req, $i_rHeaders );
        return $this->sendRequest( $req );
    }


    public function sendRequest( RequestInterface $request ) : ResponseInterface {
        $response = parent::sendRequest( $request );
        return $this->upgradeResponse( $request, $response );

    }


    /** @param iterable<string, string|list<string>> $i_rHeaders */
    protected function addHeadersToRequest( RequestInterface $i_req, iterable $i_rHeaders ) : RequestInterface {
        foreach ( $i_rHeaders as $stHeader => $value ) {
            $i_req = $i_req->withHeader( $stHeader, $value );
        }
        return $i_req;
    }


    /** @param iterable<string, string|list<string>> $i_data */
    protected function addIterableToBody( RequestInterface $i_req, iterable $i_data ) : RequestInterface {
        # The default implementation is application/x-www-form-urlencoded.
        $st = '';
        foreach ( $i_data as $key => $value ) {
            if ( '' !== $st ) {
                $st .= '&';
            }
            # Ensure the key is a string and value is properly escaped.
            /** @noinspection PhpCastIsUnnecessaryInspection */
            $st .= urlencode( strval( $key ) ) . '=' . urlencode( (string) $value );
        }
        return $i_req
            ->withBody( $this->streamFactory->createStream( $st ) )
            ->withHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
    }


    protected function upgradeResponse( RequestInterface                    $i_request,
                                        \Psr\Http\Message\ResponseInterface $i_response ) : ResponseInterface {
        return new Response( $i_request, $i_response );
    }


}
