<?php

namespace InoOicClient\Oic\UserInfo;

use Zend\Http;
use InoOicClient\Oic\Exception\HttpRequestBuilderException;
use InoOicClient\Oic\Exception\HttpClientException;
use InoOicClient\Oic\Exception\ErrorResponseException;
use InoOicClient\Oic\AbstractHttpRequestDispatcher;


class Dispatcher extends AbstractHttpRequestDispatcher
{

    /**
     * @var HttpRequestBuilder
     */
    protected $httpRequestBuilder;

    /**
     * @var ResponseHandler
     */
    protected $responseHandler;


    /**
     * @return HttpRequestBuilder $httpRequestBuilder
     */
    public function getHttpRequestBuilder()
    {
        if (! $this->httpRequestBuilder instanceof HttpRequestBuilder) {
            $this->httpRequestBuilder = new HttpRequestBuilder();
        }
        return $this->httpRequestBuilder;
    }


    /**
     * @param HttpRequestBuilder $httpRequestBuilder
     */
    public function setHttpRequestBuilder(HttpRequestBuilder $httpRequestBuilder)
    {
        $this->httpRequestBuilder = $httpRequestBuilder;
    }


    /**
     * @return ResponseHandler $responseHandler
     */
    public function getResponseHandler()
    {
        if (! $this->responseHandler instanceof ResponseHandler) {
            $this->responseHandler = new ResponseHandler();
        }
        return $this->responseHandler;
    }


    /**
     * @param ResponseHandler $responseHandler
     */
    public function setResponseHandler(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }


    /**
     * Sends a userinfo request and returns the response.
     * 
     * @param Request $request
     * @param Http\Request $httpRequest
     * @throws HttpRequestBuilderException
     * @throws HttpClientException
     * @throws ErrorResponseException
     * @return Response
     */
    public function sendUserInfoRequest(Request $request, Http\Request $httpRequest = null)
    {
        try {
            $httpRequest = $this->getHttpRequestBuilder()->buildHttpRequest($request, $httpRequest);
        } catch (\Exception $e) {
            throw new HttpRequestBuilderException(sprintf("Invalid request: [%s] %s", get_class($e), $e->getMessage()));
        }
        
        $httpResponse = $this->sendHttpRequest($httpRequest);
        
        $responseHandler = $this->getResponseHandler();
        $responseHandler->handleResponse($httpResponse);
        if ($responseHandler->isError()) {
            throw new ErrorResponseException($responseHandler->getError());
        }
        
        $response = $responseHandler->getResponse();
        
        return $response;
    }
}