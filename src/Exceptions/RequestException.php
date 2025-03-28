<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Exceptions;


use Psr\Http\Client\RequestExceptionInterface;


class RequestException extends AbstractRequestException implements RequestExceptionInterface { }
