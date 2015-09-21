# Yahoo Provider for OAuth 2.0 Client
[![Build Status](https://travis-ci.org/hayageek/oauth2-yahoo.svg)](https://travis-ci.org/hayageek/oauth2-yahoo)

This package provides Yahoo OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

This package is compliant with [PSR-1][], [PSR-2][] and [PSR-4][]. If you notice compliance oversights, please send
a patch via pull request.


## Requirements

The following versions of PHP are supported.

* PHP 5.5
* PHP 5.6
* PHP 7.0
* HHVM

## Installation

To install, use composer:

```
composer require hayageek/oauth2-yahoo
```

## Usage

### Authorization Code Flow

```php
$provider = new League\OAuth2\Client\Provider\Yahoo([
    'clientId'     => '{Yahoo-app-id}',
    'clientSecret' => '{Yahoo-app-secret}',
    'redirectUri'  => 'https://example.com/callback-url',
]);

if (!empty($_GET['error'])) {

    // Got an error, probably user denied access
    exit('Got error: ' . $_GET['error']);

} elseif (empty($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    // State is invalid, possible CSRF attack in progress
    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the owner details
        $ownerDetails = $provider->getResourceOwner($token);

        //Use these details to create a new profile
        echo 'Name: '.$ownerDetails->getName()."<br>";
	    echo 'FirstName: '.$ownerDetails->getFirstName()."<br>";
    	echo 'Lastname: '.$ownerDetails->getLastName()."<br>";
    
	    echo 'Email: '.$ownerDetails->getEmail()."<br>";
	    echo 'Image: '.$ownerDetails->getAvatar()."<br>";    
        
        

    } catch (Exception $e) {

        // Failed to get user details
        exit('Something went wrong: ' . $e->getMessage());

    }

    
	// Use this to interact with an API on the users behalf
	echo "Token: ". $token->getToken()."<br>";

	// Use this to get a new access token if the old one expires
	echo  "Refresh Token: ".$token->getRefreshToken()."<br>";

	// Number of seconds until the access token will expire, and need refreshing
	echo "Expires:" .$token->getExpires()."<br>";

    
    
}


```

### Refreshing a Token

```php

$provider = new League\OAuth2\Client\Provider\Yahoo([
    'clientId'     => '{Yahoo-app-id}',
    'clientSecret' => '{Yahoo-app-secret}',
    'redirectUri'  => 'https://example.com/callback-url',
]);

$grant = new League\OAuth2\Client\Grant\RefreshToken();
$token = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing



## Credits

- 
- 


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-Yahoo/blob/master/LICENSE) for more information.
