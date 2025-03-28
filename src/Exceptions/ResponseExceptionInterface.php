<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Exceptions;


use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;


interface ResponseExceptionInterface extends ClientExceptionInterface {


    public function getResponse() : ?ResponseInterface;


}