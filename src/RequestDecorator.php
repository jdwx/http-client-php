<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\RequestInterface;


class RequestDecorator implements RequestInterface {


    use RequestTrait;


    public function __construct( private RequestInterface $request ) {}


    public function cloneRequest( RequestInterface $request ) : static {
        $x = clone $this;
        $x->request = $request;
        return $x;
    }


    protected function fromRequest() : RequestInterface {
        return $this->request;
    }


}
