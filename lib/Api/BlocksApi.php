<?php

namespace Flyo\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Flyo\ApiException;
use Flyo\Configuration;
use Flyo\FormDataProcessor;
use Flyo\HeaderSelector;
use Flyo\ObjectSerializer;

/**
 * BlocksApi Class Doc Comment
 *
 * @category Class
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class BlocksApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /** @var string[] $contentTypes **/
    public const contentTypes = [
        'blockCollection' => [
            'application/json',
        ],
    ];

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?Configuration $config = null,
        ?HeaderSelector $selector = null,
        int $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: Configuration::getDefaultConfiguration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation blockCollection
     *
     * Get a page of a block collection
     *
     * @param  string $key The key of the collection, &#x60;collection.key&#x60; of the block: the uid of the placement unless the editor named it. (required)
     * @param  int|null $page The page to answer, starting at 1. A page past the last one answers the last page. (optional, default to 1)
     * @param  string|null $sort One or more of &#x60;collection.sortable&#x60;, comma separated, &#x60;-&#x60; for descending. &#x60;position&#x60; is the order the editor set and the default. (optional)
     * @param  array<string,mixed>|null $filter Conditions on the fields of &#x60;collection.filterable&#x60;, see the description of this endpoint. A field of an object with &#x60;:&#x60;, a field of the entries of a list with &#x60;.&#x60;. (optional)
     * @param  string|null $lang Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['blockCollection'] to see the possible values for this operation
     *
     * @throws \Flyo\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \Flyo\Model\Block
     */
    public function blockCollection($key, $page = 1, $sort = null, $filter = null, $lang = null, string $contentType = self::contentTypes['blockCollection'][0])
    {
        list($response) = $this->blockCollectionWithHttpInfo($key, $page, $sort, $filter, $lang, $contentType);
        return $response;
    }

    /**
     * Operation blockCollectionWithHttpInfo
     *
     * Get a page of a block collection
     *
     * @param  string $key The key of the collection, &#x60;collection.key&#x60; of the block: the uid of the placement unless the editor named it. (required)
     * @param  int|null $page The page to answer, starting at 1. A page past the last one answers the last page. (optional, default to 1)
     * @param  string|null $sort One or more of &#x60;collection.sortable&#x60;, comma separated, &#x60;-&#x60; for descending. &#x60;position&#x60; is the order the editor set and the default. (optional)
     * @param  array<string,mixed>|null $filter Conditions on the fields of &#x60;collection.filterable&#x60;, see the description of this endpoint. A field of an object with &#x60;:&#x60;, a field of the entries of a list with &#x60;.&#x60;. (optional)
     * @param  string|null $lang Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['blockCollection'] to see the possible values for this operation
     *
     * @throws \Flyo\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \Flyo\Model\Block, HTTP status code, HTTP response headers (array of strings)
     */
    public function blockCollectionWithHttpInfo($key, $page = 1, $sort = null, $filter = null, $lang = null, string $contentType = self::contentTypes['blockCollection'][0])
    {
        $request = $this->blockCollectionRequest($key, $page, $sort, $filter, $lang, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\Flyo\Model\Block',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\Flyo\Model\Block',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Flyo\Model\Block',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation blockCollectionAsync
     *
     * Get a page of a block collection
     *
     * @param  string $key The key of the collection, &#x60;collection.key&#x60; of the block: the uid of the placement unless the editor named it. (required)
     * @param  int|null $page The page to answer, starting at 1. A page past the last one answers the last page. (optional, default to 1)
     * @param  string|null $sort One or more of &#x60;collection.sortable&#x60;, comma separated, &#x60;-&#x60; for descending. &#x60;position&#x60; is the order the editor set and the default. (optional)
     * @param  array<string,mixed>|null $filter Conditions on the fields of &#x60;collection.filterable&#x60;, see the description of this endpoint. A field of an object with &#x60;:&#x60;, a field of the entries of a list with &#x60;.&#x60;. (optional)
     * @param  string|null $lang Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['blockCollection'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function blockCollectionAsync($key, $page = 1, $sort = null, $filter = null, $lang = null, string $contentType = self::contentTypes['blockCollection'][0])
    {
        return $this->blockCollectionAsyncWithHttpInfo($key, $page, $sort, $filter, $lang, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation blockCollectionAsyncWithHttpInfo
     *
     * Get a page of a block collection
     *
     * @param  string $key The key of the collection, &#x60;collection.key&#x60; of the block: the uid of the placement unless the editor named it. (required)
     * @param  int|null $page The page to answer, starting at 1. A page past the last one answers the last page. (optional, default to 1)
     * @param  string|null $sort One or more of &#x60;collection.sortable&#x60;, comma separated, &#x60;-&#x60; for descending. &#x60;position&#x60; is the order the editor set and the default. (optional)
     * @param  array<string,mixed>|null $filter Conditions on the fields of &#x60;collection.filterable&#x60;, see the description of this endpoint. A field of an object with &#x60;:&#x60;, a field of the entries of a list with &#x60;.&#x60;. (optional)
     * @param  string|null $lang Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['blockCollection'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function blockCollectionAsyncWithHttpInfo($key, $page = 1, $sort = null, $filter = null, $lang = null, string $contentType = self::contentTypes['blockCollection'][0])
    {
        $returnType = '\Flyo\Model\Block';
        $request = $this->blockCollectionRequest($key, $page, $sort, $filter, $lang, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'blockCollection'
     *
     * @param  string $key The key of the collection, &#x60;collection.key&#x60; of the block: the uid of the placement unless the editor named it. (required)
     * @param  int|null $page The page to answer, starting at 1. A page past the last one answers the last page. (optional, default to 1)
     * @param  string|null $sort One or more of &#x60;collection.sortable&#x60;, comma separated, &#x60;-&#x60; for descending. &#x60;position&#x60; is the order the editor set and the default. (optional)
     * @param  array<string,mixed>|null $filter Conditions on the fields of &#x60;collection.filterable&#x60;, see the description of this endpoint. A field of an object with &#x60;:&#x60;, a field of the entries of a list with &#x60;.&#x60;. (optional)
     * @param  string|null $lang Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['blockCollection'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function blockCollectionRequest($key, $page = 1, $sort = null, $filter = null, $lang = null, string $contentType = self::contentTypes['blockCollection'][0])
    {

        // verify the required parameter 'key' is set
        if ($key === null || (is_array($key) && count($key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $key when calling blockCollection'
            );
        }

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling BlocksApi.blockCollection, must be bigger than or equal to 1.');
        }
        




        $resourcePath = '/blocks/collection/{key}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $page,
            'page', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $sort,
            'sort', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $filter,
            'filter', // param base name
            'object', // openApiType
            'deepObject', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $lang,
            'lang', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);


        // path params
        if ($key !== null) {
            $resourcePath = str_replace(
                '{' . 'key' . '}',
                ObjectSerializer::toPathValue($key),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                $httpBody = \GuzzleHttp\Utils::jsonEncode($formParams);
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires API key authentication
        $apiKey = $this->config->getApiKeyWithPrefix('token');
        if ($apiKey !== null) {
            $queryParams['token'] = $apiKey;
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        if ($this->config->getCertFile()) {
            $options[RequestOptions::CERT] = $this->config->getCertFile();
        }

        if ($this->config->getKeyFile()) {
            $options[RequestOptions::SSL_KEY] = $this->config->getKeyFile();
        }

        return $options;
    }

    private function handleResponseWithDataType(
        string $dataType,
        RequestInterface $request,
        ResponseInterface $response
    ): array {
        if ($dataType === '\SplFileObject') {
            $content = $response->getBody(); //stream goes to serializer
        } else {
            $content = (string) $response->getBody();
            if ($dataType !== 'string') {
                try {
                    $content = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    throw new ApiException(
                        sprintf(
                            'Error JSON decoding server response (%s)',
                            $request->getUri()
                        ),
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $content
                    );
                }
            }
        }

        return [
            ObjectSerializer::deserialize($content, $dataType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    private function responseWithinRangeCode(
        string $rangeCode,
        int $statusCode
    ): bool {
        $left = (int) ($rangeCode[0].'00');
        $right = (int) ($rangeCode[0].'99');

        return $statusCode >= $left && $statusCode <= $right;
    }
}
