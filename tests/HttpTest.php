<?php

use NZTim\SimpleHttp\Http;
use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase
{
    /** @test */
    function query_parameters_can_be_passed_as_an_array()
    {
        $response = (new Http)->get('https://httpbin.org/get', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertArraySubset([
            'args' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function query_parameters_in_url_are_sent()
    {
        $response = (new Http)->get('https://httpbin.org/get?foo=bar&baz=qux');
        $this->assertArraySubset([
            'args' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function query_parameters_in_urls_can_be_combined_with_array_parameters()
    {
        $response = (new Http)->get('https://httpbin.org/get?foo=bar', ['baz' => 'qux']);
        $this->assertArraySubset([
            'args' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function post_content_is_json_by_default()
    {
        $response = (new Http)->post('https://httpbin.org/post', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertEquals('application/json', $response->json()['headers']['Content-Type']);
        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function post_content_can_be_sent_as_form_params()
    {
        $response = (new Http)->asFormParams()->post('https://httpbin.org/post', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertEquals('application/x-www-form-urlencoded', $response->json()['headers']['Content-Type']);
        $this->assertArraySubset([
            'form' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function post_content_can_be_sent_as_multipart()
    {
        $response = (new Http)->asMultipart()->post('https://httpbin.org/post', [
            [
                'name'     => 'foo',
                'contents' => 'bar',
            ],
            [
                'name'     => 'baz',
                'contents' => 'qux',
            ],
            [
                'name'     => 'test-file',
                'contents' => 'test contents',
                'filename' => 'test-file.txt',
            ],
        ]);
        $results = $response->json();
        $this->assertArraySubset([
            'form' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $results);
        $this->assertEquals('test contents', $results['files']['test-file']);
        $this->assertStringStartsWith('multipart', $results['headers']['Content-Type']);
    }

    /** @test */
    function post_content_can_be_sent_as_json_explicitly()
    {
        $response = (new Http)->asJson()->post('https://httpbin.org/post', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertEquals('application/json', $response->json()['headers']['Content-Type']);
        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function get_with_additional_headers()
    {
        $response = (new Http)->withHeaders(['Custom' => 'Header'])->get('https://httpbin.org/get');
        $this->assertEquals('Header', $response->json()['headers']['Custom']);
    }

    /** @test */
    function post_with_additional_headers()
    {
        $response = (new Http)->withHeaders(['Custom' => 'Header'])->post('https://httpbin.org/post');
        $this->assertEquals('Header', $response->json()['headers']['Custom']);
    }

    /** @test */
    function the_accept_header_can_be_set_via_shortcut()
    {
        $response = (new Http)->accept('banana/sandwich')->post('https://httpbin.org/post');
        $this->assertEquals('banana/sandwich', $response->json()['headers']['Accept']);
    }

    /** @test */
    function exceptions_are_not_thrown_for_40x_responses()
    {
        $response = (new Http)->get('https://httpbin.org/status/418');
        $this->assertEquals(418, $response->status());
    }

    /** @test */
    function exceptions_are_not_thrown_for_50x_responses()
    {
        $response = (new Http)->get('https://httpbin.org/status/508');
        $this->assertEquals(508, $response->status());
    }

    /** @test */
    function redirects_are_followed_by_default()
    {
        $response = (new Http)->get('https://httpbin.org/redirect/1');
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    function redirects_can_be_disabled()
    {
        $response = (new Http)->withoutRedirecting()->get('https://httpbin.org/redirect-to?url=http%3A%2F%2Fexample.com%2F');
        $this->assertEquals(302, $response->status());
        $this->assertEquals('http://example.com/', $response->header('Location'));
    }

    /** @test */
    function patch_requests_are_supported()
    {
        $response = (new Http)->patch('https://httpbin.org/patch', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function put_requests_are_supported()
    {
        $response = (new Http)->put('https://httpbin.org/put', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function delete_requests_are_supported()
    {
        $response = (new Http)->delete('https://httpbin.org/delete', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function query_parameters_are_respected_in_post_requests()
    {
        $response = (new Http)->post('https://httpbin.org/post?banana=sandwich', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertArraySubset([
            'args' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function query_parameters_are_respected_in_put_requests()
    {
        $response = (new Http)->put('https://httpbin.org/put?banana=sandwich', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertArraySubset([
            'args' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function query_parameters_are_respected_in_patch_requests()
    {
        $response = (new Http)->patch('https://httpbin.org/patch?banana=sandwich', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertArraySubset([
            'args' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function query_parameters_are_respected_in_delete_requests()
    {
        $response = (new Http)->delete('https://httpbin.org/delete?banana=sandwich', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertArraySubset([
            'args' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $response->json());
    }

    /** @test */
    function can_retrieve_the_raw_response_body()
    {
        $response = (new Http)->get('https://httpbin.org/robots.txt');
        $this->assertStringStartsWith('User-agent: *', $response->body());
    }

    /** @test */
    function can_retrieve_response_header_values()
    {
        $response = (new Http)->get('https://httpbin.org/get');
        $this->assertEquals('application/json', $response->header('Content-Type'));
        $this->assertEquals('application/json', $response->headers()['Content-Type']);
    }

    /** @test */
    function can_check_if_a_response_is_success()
    {
        $response = (new Http)->get('https://httpbin.org/get');
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isClientError());
        $this->assertFalse($response->isServerError());
    }

    /** @test */
    function can_check_if_a_response_is_redirect()
    {
        $response = (new Http)->withoutRedirecting()->get('https://httpbin.org/redirect/1');
        $this->assertTrue($response->isRedirect());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isClientError());
        $this->assertFalse($response->isServerError());
    }

    /** @test */
    function can_check_if_a_response_is_client_error()
    {
        $response = (new Http)->get('https://httpbin.org/status/404');
        $this->assertTrue($response->isClientError());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isServerError());
    }

    /** @test */
    function can_check_if_a_response_is_server_error()
    {
        $response = (new Http)->get('https://httpbin.org/status/508');
        $this->assertTrue($response->isServerError());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isClientError());
    }

    /** @test */
    function is_ok_is_an_alias_for_is_success()
    {
        $response = (new Http)->get('https://httpbin.org/status/200');
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isClientError());
        $this->assertFalse($response->isServerError());
    }

    /** @test */
    function can_use_basic_auth()
    {
        $response = (new Http)->withBasicAuth('zttp', 'secret')->get('https://httpbin.org/basic-auth/zttp/secret');
        $this->assertTrue($response->isSuccess());
        $response = (new Http)->withBasicAuth('fail', 'wrong')->get('https://httpbin.org/basic-auth/zttp/secret');
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isClientError());
    }

    /** @test */
    function can_use_digest_auth()
    {
        $response = (new Http)->withDigestAuth('zttp', 'secret')->get('https://httpbin.org/digest-auth/auth/zttp/secret');
        $this->assertTrue($response->isSuccess());
        $response = (new Http)->withDigestAuth('fail', 'wrong')->get('https://httpbin.org/digest-auth/auth/zttp/secret');
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isClientError());
    }

    /**
     * @test
     * @expectedException \NZTim\SimpleHttp\ConnectionException
     */
    function client_will_force_timeout()
    {
        (new Http)->timeout(1)->get('https://httpbin.org/delay/3');
    }
}
