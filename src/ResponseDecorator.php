<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\ResponseInterface;


class ResponseDecorator implements ResponseInterface {


    use ResponseTrait;


    public function __construct( private ResponseInterface $response ) {}


    protected function cloneResponse( ResponseInterface $response ) : static {
        $x = clone $this;
        $x->response = $response;
        return $x;
    }


    protected function fromResponse() : ResponseInterface {
        return $this->response;
    }


}
