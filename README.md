# SimpleHttp

A Guzzle wrapper designed for simple HTTP requests.

Based on Adam Wathan's kitetail/zttp.

### Usage

```php

$http = new NZTim\SimpleHttp\Http;

// Query parameters as well as arrays can be used:
$http->get('https://example.com/?foo=bar');
$http->get('https://example.com', ['foo' => 'bar']);

// JSON is default but requests can be sent as form parameters and multipart:
$http->post('https://example.com/', ['foo' => 'bar'); // JSON
$http->asFormParams()->post('https://example.com/', ['foo' => 'bar']); // Form parameters
$http->asMultipart()->post('https://example.com/', [['foo' => 'bar'], ['baz' => 'qux']); // Multipart

// Sending extra headers:
$http->withHeaders(['Custom' => 'Header'])->post('https://example.com/', ['foo' => 'bar');

// Supported HTTP methods:
$http->get('https://example.com/');
$http->post('https://example.com/');
$http->patch('https://example.com/');
$http->put('https://example.com/');
$http->delete('https://example.com/');

// Handling the response:
$response = $http->get('https://example.com');
$response->body(); // Body as a string
$response->json(); // Body as an array (decoded JSON)
$response->headers(); // Array of headers
$response->header('Content-Type'); // Specific header

// Boolean response methods:
$response->isOk();
$response->isSuccess();
$response->isClientError();
$response->isServerError();
```

### Upgrading

* v3.0: PHP 7.4|8.0 minimum, add HEAD request method
