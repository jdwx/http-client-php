<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use InvalidArgumentException;
use JDWX\HttpClient\Exceptions\HttpStatusException;
use JDWX\HttpClient\Simple\SimpleFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Stringable;


class Client extends ClientDecorator implements ClientInterface {


    private RequestFactoryInterface $requestFactory;

    private UriFactoryInterface $uriFactory;

    private StreamFactoryInterface $streamFactory;

    private ?LoggerInterface $logger;

    private bool $bErrorIsAcceptable = false;

    private bool $bLogErrors = true;


    public function __construct( object ...$i_rSources ) {
        $client = self::pickInterfaceEx( $i_rSources, \Psr\Http\Client\ClientInterface::class );
        assert( $client instanceof ClientInterface );
        parent::__construct( $client );

        $this->requestFactory = self::pickInterfaceEx( $i_rSources, RequestFactoryInterface::class );
        $this->uriFactory = self::pickInterfaceEx( $i_rSources, UriFactoryInterface::class );
        $this->streamFactory = self::pickInterfaceEx( $i_rSources, StreamFactoryInterface::class );
        $this->logger = self::pickInterface( $i_rSources, LoggerInterface::class );

    }


    /**
     * @param array<object> $i_rSources
     * @param string $i_stInterface
     * @return ?object
     */
    protected static function pickInterface( array $i_rSources, string $i_stInterface ) : ?object {
        static $facDefault = null;
        foreach ( $i_rSources as $factory ) {
            if ( $factory instanceof $i_stInterface ) {
                return $factory;
            }
        }
        if ( null === $facDefault ) {
            $facDefault = new SimpleFactory();
        }
        if ( $facDefault instanceof $i_stInterface ) {
            return $facDefault;
        }
        return null;
    }


    protected static function pickInterfaceEx( array $i_rFactories, string $i_stInterface ) : object {
        $fac = self::pickInterface( $i_rFactories, $i_stInterface );
        if ( $fac instanceof $i_stInterface ) {
            return $fac;
        }
        throw new InvalidArgumentException( "No source found for interface {$i_stInterface}" );
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
     * @param iterable<string, string|int|float>|string|StreamInterface|Stringable $i_body
     * @param iterable<string, string|list<string>> $i_rHeaders
     *
     * Note that if you don't provide an iterable body, you're responsible for
     * setting Content-Type correctly in the provided headers.
     */
    public function post( UriInterface|string $i_uri, iterable|string|StreamInterface|Stringable $i_body = '',
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
        $response = $this->upgradeResponse( $request, parent::sendRequest( $request ) );
        $this->handleFailure( $request, $response );
        return $response;
    }


    public function setErrorIsAcceptable( bool $i_bErrorIsAcceptable = true ) : static {
        $this->bErrorIsAcceptable = $i_bErrorIsAcceptable;
        return $this;
    }


    public function setLogErrors( bool $i_bLogErrors = true ) : static {
        $this->bLogErrors = $i_bLogErrors;
        return $this;
    }


    protected function handleFailure( RequestInterface $request, ResponseInterface $response ) : void {

        $uStatus = $response->getStatusCode();
        $stReason = $response->getReasonPhrase() ?: 'Unknown Error';
        $stMethod = $request->getMethod();
        $stUri = strval( $request->getUri() );
        $stMessage = "HTTP Status {$uStatus} {$stReason} for: {$stMethod} {$stUri}";
        $bError = $response->isError();

        $uFacility = ( $bError && $this->bLogErrors )
            ? ( $this->bErrorIsAcceptable ? LOG_INFO : LOG_ERR )
            : LOG_DEBUG;
        $this->logger?->log( $uFacility, $stMessage, [
            'status' => $uStatus,
            'reason' => $stReason,
            'method' => $stMethod,
            'uri' => $stUri,
        ] );

        if ( $bError && ! $this->bErrorIsAcceptable ) {
            throw new HttpStatusException( $response, $request, $stMessage, $uStatus );
        }
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
            ->withHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
        ;
    }


    protected function upgradeResponse( RequestInterface                    $i_request,
                                        \Psr\Http\Message\ResponseInterface $i_response ) : ResponseInterface {
        return new Response( $i_request, $i_response );
    }


}
