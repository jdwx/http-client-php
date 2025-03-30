<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\ResponseInterface;


class ResponseDecorator implements ResponseInterface {


    use ResponseTrait;


    public function __construct( private ResponseInterface $response ) { }


    public function getResponse() : ResponseInterface {
        return $this->response;
    }


    protected function cloneResponse( ResponseInterface $response ) : static {
        $x = clone $this;
        $x->response = $response;
        return $x;
    }


}
