<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\RequestInterface;


interface ResponseInterface extends \Psr\Http\Message\ResponseInterface {


    public function body() : string;


    public function getBareContentType() : ?string;


    public function getHeaderOne( string $i_stName ) : ?string;


    public function getHeaderOneEx( string $i_stName ) : string;


    public function getRequest() : RequestInterface;


    public function isContentType( string $i_stType, ?string $i_stSubtype = null ) : bool;


    public function isContentTypeLoose( string $i_stType, string $i_stSubtype ) : bool;


    public function isContentTypeSubtype( string $i_stSubtype ) : bool;


    public function isContentTypeType( string $i_stType ) : bool;


    public function isRedirect() : bool;


    public function isSuccess() : bool;


}