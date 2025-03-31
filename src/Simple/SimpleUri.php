<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Simple;


use Psr\Http\Message\UriInterface;


readonly class SimpleUri implements UriInterface, \Stringable {


    public string $stScheme;

    public string $stPath;


    final public function __construct( string        $stScheme = '', public string $stUser = '',
                                       public string $stPassword = '', public string $stHost = '',
                                       public ?int   $nuPort = null, string $stPath = '',
                                       public string $stQuery = '', public string $stFragment = '' ) {
        # This catches you if you forget and pass the full URI as the only
        # argument to the constructor. This is a common mistake.
        if ( str_contains( $stScheme, ':' ) || str_contains( $stScheme, '/' ) ) {
            // Scheme must not contain a colon
            throw new \InvalidArgumentException( 'Invalid scheme: ' . $stScheme );
        }
        if ( '' === $stPath && $stHost ) {
            # If the path is empty and the host is set, set the path to '/'
            $stPath = '/';
        }
        $this->stScheme = strtolower( $stScheme );
        $this->stPath = $stPath;
    }


    public static function from( array|string|UriInterface $i_uri ) : static {
        if ( is_string( $i_uri ) ) {
            return static::fromString( $i_uri );
        }
        if ( is_array( $i_uri ) ) {
            return static::fromArray( $i_uri );
        }
        return static::fromUri( $i_uri );
    }


    public static function fromArray( array $i_rUri ) : static {
        $nuPort = $i_rUri[ 'port' ] ?? null;
        if ( is_string( $nuPort ) ) {
            $nuPort = intval( $nuPort );
        }
        return new static(
            $i_rUri[ 'scheme' ] ?? '',
            $i_rUri[ 'user' ] ?? '',
            $i_rUri[ 'pass' ] ?? '',
            $i_rUri[ 'host' ] ?? '',
            $nuPort,
            $i_rUri[ 'path' ] ?? '',
            $i_rUri[ 'query' ] ?? '',
            $i_rUri[ 'fragment' ] ?? ''
        );
    }


    public static function fromString( string $i_stUri ) : static {
        $r = parse_url( $i_stUri );
        if ( ! is_array( $r ) ) {
            throw new \InvalidArgumentException( 'Invalid URI: ' . $i_stUri );
        }
        return static::fromArray( $r );
    }


    public static function fromUri( UriInterface $i_uri ) : static {
        $stUserInfo = $i_uri->getUserInfo();
        [ $stUser, $stPassword ] = $stUserInfo
            ? array_merge( explode( ':', $stUserInfo, 2 ), [ '' ] )
            : [ '', '' ];
        return new static(
            $i_uri->getScheme(),
            $stUser,
            $stPassword,
            $i_uri->getHost(),
            $i_uri->getPort(),
            $i_uri->getPath(),
            $i_uri->getQuery(),
            $i_uri->getFragment()
        );
    }


    public function __toString() : string {
        $st = $this->stScheme ? $this->stScheme . ':' : '';
        $stAuthority = $this->getAuthority();
        if ( $stAuthority ) {
            $st .= '//' . $stAuthority;
        }
        $st .= $this->stPath;
        if ( $this->stQuery ) {
            $st .= '?' . $this->stQuery;
        }
        if ( $this->stFragment ) {
            $st .= '#' . $this->stFragment;
        }
        return $st;
    }


    public function getAuthority() : string {
        $st = $this->getUserInfo();
        if ( $st ) {
            $st .= '@';
        }
        if ( ! $this->stHost ) {
            return $st;
        }
        $st .= $this->stHost;
        if ( ! is_int( $this->nuPort ) ) {
            return $st;
        }
        if ( $this->stScheme === 'http' && $this->nuPort === 80 ) {
            return $st;
        }
        if ( $this->stScheme === 'https' && $this->nuPort === 443 ) {
            return $st;
        }
        return $st . ':' . $this->nuPort;
    }


    public function getFragment() : string {
        return $this->stFragment;
    }


    public function getHost() : string {
        return $this->stHost;
    }


    public function getPath() : string {
        return $this->stPath;
    }


    public function getPort() : ?int {
        return $this->nuPort;
    }


    public function getQuery() : string {
        return $this->stQuery;
    }


    public function getScheme() : string {
        return $this->stScheme;
    }


    public function getUserInfo() : string {
        $st = '';
        if ( $this->stUser ) {
            $st .= $this->stUser;
            if ( $this->stPassword ) {
                $st .= ':' . $this->stPassword;
            }
        }
        return $st;
    }


    public function withFragment( string $fragment ) : static {
        return $this->clone( nstFragment: $fragment );
    }


    public function withHost( string $host ) : static {
        return $this->clone( nstHost: $host );
    }


    public function withPath( string $path ) : static {
        return $this->clone( nstPath: $path );
    }


    public function withPort( ?int $port ) : static {
        return $this->clone( nuPort: $port, i_bForcePort: true );
    }


    public function withQuery( string $query ) : static {
        return $this->clone( nstQuery: $query );
    }


    public function withScheme( string $scheme ) : static {
        return $this->clone( nstScheme: $scheme );
    }


    public function withUserInfo( string $user, ?string $password = null ) : static {
        return $this->clone( nstUser: $user, nstPassword: $password );
    }


    private function clone( ?string $nstScheme = null, ?string $nstUser = null,
                            ?string $nstPassword = null, ?string $nstHost = null,
                            ?int    $nuPort = null, ?string $nstPath = null,
                            ?string $nstQuery = null, ?string $nstFragment = null,
                            bool    $i_bForcePort = false ) : static {
        return new static(
            $nstScheme ?? $this->stScheme,
            $nstUser ?? $this->stUser,
            $nstPassword ?? $this->stPassword,
            $nstHost ?? $this->stHost,
            $i_bForcePort
                ? $nuPort
                : $nuPort ?? $this->nuPort,
            $nstPath ?? $this->stPath,
            $nstQuery ?? $this->stQuery,
            $nstFragment ?? $this->stFragment
        );
    }


}
