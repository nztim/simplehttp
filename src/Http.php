<?php namespace NZTim\SimpleHttp;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;

class Http
{
    /** @var string */
    private $bodyFormat;
    /** @var array */
    private $options;

    public function __construct()
    {
        $this->bodyFormat = 'json';
        $this->options = [
            'http_errors' => false,
        ];
    }

    public function withoutRedirecting(): Http
    {
        $this->mergeOptions(['allow_redirects' => false]);
        return $this;
    }

    public function asJson(): Http
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    public function asFormParams(): Http
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    public function asMultipart(): Http
    {
        return $this->bodyFormat('multipart');
    }

    private function bodyFormat(string $format): Http
    {
        $this->bodyFormat = $format;
        return $this;
    }

    public function contentType(string $contentType): Http
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    public function accept(string $header): Http
    {
        return $this->withHeaders(['Accept' => $header]);
    }

    public function withHeaders(array $headers): Http
    {
        $this->mergeOptions(['headers' => $headers]);
        return $this;
    }

    public function withBasicAuth(string $username, string $password)
    {
        $this->mergeOptions(['auth' => [$username, $password]]);
        return $this;
    }

    public function withDigestAuth(string $username, string $password)
    {
        $this->mergeOptions(['auth' => [$username, $password, 'digest']]);
        return $this;
    }

    public function timeout(int $seconds)
    {
        $this->mergeOptions(['timeout' => $seconds]);
        return $this;
    }

    public function get($url, $params = [])
    {
        $this->mergeOptions(['query' => $params]); // Use query instead of body for GET
        return $this->send('GET', $url, []);
    }

    public function post($url, $params = [])
    {
        return $this->send('POST', $url, $params);
    }

    public function patch($url, $params = [])
    {
        return $this->send('PATCH', $url, $params);
    }

    public function put($url, $params = [])
    {
        return $this->send('PUT', $url, $params);
    }

    public function delete($url, $params = [])
    {
        return $this->send('DELETE', $url, $params);
    }

    public function send($method, $url, $params)
    {
        $this->mergeOptions([$this->bodyFormat => $params]);
        $this->mergeQuery($url);
        try {
            return new HttpResponse((new GuzzleClient)->request($method, $url, $this->options));
        } catch (ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        }
    }

    private function mergeOptions(...$options)
    {
        $this->options = array_merge_recursive($this->options, ...$options);
    }

    private function mergeQuery(string $url)
    {
        // Parse URL query string and turn it into an array
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        if ($query) {
            $this->mergeOptions(['query' => $query]);
        }
    }
}
