<?php
namespace League\OAuth2\Client\Test\Provider;
require(__DIR__ .'/../../../src/Provider/Yahoo.php');
require(__DIR__ .'/../../../src/Provider/YahooUser.php');


use League\OAuth2\Client\Provider\Yahoo as YahooProvider;

use Mockery as m;

class YahooTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new YahooProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertEquals('/oauth2/request_auth', $uri['path']);


        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('language', $query);

        $this->assertEquals('mock_client_id', $query['client_id']);
		$this->assertEquals('none', $query['redirect_uri']);
		$this->assertEquals('en-us', $query['language']);
		$this->assertEquals('code', $query['response_type']);
		
        
        $this->assertAttributeNotEmpty('state', $this->provider);
    }

    public function testBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        

        $this->assertEquals('/oauth2/get_token', $uri['path']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');

		$token->shouldReceive('getResourceOwnerId')->once()->andReturn('mocguid');

        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);
        $this->assertEquals('/v1/user/mocguid/profile', $uri['path']);

    }
    public function testUserData()
    {
        $response = json_decode('{"profile":{"guid":"mocguid","emails":[{"handle":"mock_email","id":2,"primary":false,"type":"HOME"}],"familyName":"mock_family_name","givenName":"mock_given_name","uri":"mock_url"}}', true);
		$imageData = json_decode('{"image": {"uri": "mock_uril","height": 192,"imageUrl": "mock_image_url", "size": "192x192", "width": 192 } }',true);

        $provider = m::mock('League\OAuth2\Client\Provider\Yahoo[fetchResourceOwnerDetails,getUserImage]')->shouldAllowMockingProtectedMethods();;
        $provider->shouldReceive('fetchResourceOwnerDetails')->once()->andReturn($response);
        $provider->shouldReceive('getUserImage')->once()->andReturn($imageData);
        
        
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
        $user = $provider->getResourceOwner($token);
        

        $this->assertInstanceOf('League\OAuth2\Client\Provider\ResourceOwnerInterface', $user);

        $this->assertEquals('mocguid', $user->getId());
        $this->assertEquals('mock_given_name', $user->getFirstName());
        $this->assertEquals('mock_family_name', $user->getLastName());
        $this->assertEquals('mock_email', $user->getEmail());
        $this->assertEquals('mock_image_url', $user->getAvatar());
        

        $user = $user->toArray();

        $this->assertArrayHasKey('guid', $user['profile']);
        $this->assertArrayHasKey('handle', $user['profile']['emails'][0]);
        $this->assertArrayHasKey('familyName',$user['profile']);
        $this->assertArrayHasKey('givenName', $user['profile']);
        $this->assertArrayHasKey('imageUrl', $user);
        
    }

    /**
     * @expectedException League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function testErrorResponse()
    {
        $response = m::mock('GuzzleHttp\Psr7\Response');

        $response->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);

        $response->shouldReceive('getBody')
            ->andReturn('{"error": {"code": -1, "message": "I am an error"}}');

        $provider = m::mock('League\OAuth2\Client\Provider\Yahoo[sendRequest]')
            ->shouldAllowMockingProtectedMethods();

        $provider->shouldReceive('sendRequest')
            ->times(1)
            ->andReturn($response);

        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
		$token->shouldReceive('getResourceOwnerId')->once()->andReturn('mocguid');
        
        $user = $provider->getResourceOwner($token);
    }
}
