# Meta Tags

[![Build Status](https://travis-ci.org/Torann/meta-tags.svg)](https://travis-ci.org/Torann/meta-tags)
[![Latest Stable Version](https://poser.pugx.org/torann/meta-tags/v/stable.png)](https://packagist.org/packages/torann/meta-tags)
[![Total Downloads](https://poser.pugx.org/torann/meta-tags/downloads.png)](https://packagist.org/packages/torann/meta-tags)
[![Patreon donate button](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://www.patreon.com/torann)
[![Donate weekly to this project using Gratipay](https://img.shields.io/badge/gratipay-donate-yellow.svg)](https://gratipay.com/~torann)
[![Donate to this project using Flattr](https://img.shields.io/badge/flattr-donate-yellow.svg)](https://flattr.com/profile/torann)
[![Donate to this project using Paypal](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4CJA2A97NPYVU)

Extremely simple meta tag generator.

## Installation

- [Meta Tags Generator on Packagist](https://packagist.org/packages/torann/meta-tags)
- [Meta Tags Generator on GitHub](https://github.com/Torann/meta-tags)

From the command line run

```
$ composer require torann/meta-tags
```

## Methods

This package comes with a dynamic tag creation method that allows for the simple creation of tags without having to have a specific method for that tag. So, what this means is even though the tag you wish to create isn't listed in the methods bellow, you can still create it (see example).

 **/MetaTags/Manager.php**

 - `tag($name, $value)`
 - `type($type)`
 - `image($path, array $attributes = [])`
 - `url($url = null)`
 - `set($name, $value, array $attributes = [])`
 - `get($key, $type = 'general')`
 - `validation($type, $attributes = [])`
 - `config($key, $default = null)`

## Example

```php
$og = new \MetaTags\Manager();

$og->type('article')
    ->title('All Trains Run on Time')
    ->description('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.')
    ->url()
    ->profile([
        'username' => 'Torann'
    ])
    ->image('https://upload.wikimedia.org/wikipedia/commons/9/95/Train_Station_Panorama.jpg')
    ->twitterImageAlt('Train Station in Upstate New York')
    ->twitterSite('@lyften', [
        'id' => '4Df665K',
    ])
    ->siteName('My Train Website')
    ->video('http://examples.opengraphprotocol.us/media/video/train.mp4', [
        'secure_url' => 'https://examples.opengraphprotocol.us/media/video/train.mp4'
    ]);

echo $og; // Will output all meta tags
```

## Configuration

The config options are simple and easy to customize. Simply pass an array of new options when initializing a new instance of the Meta Tag manager.

**Defaults**

```php
[
    'validate' => false,
    'twitter' => true,
    'truncate' => [
        'description' => 160,
        'twitter:title' => 70,
        'og:description' => 200,
        'twitter:description' => 200,
    ],
]
```

### Values

- **validate**: When enable the package will validate tag values and attributes, warning you when there is something wrong.
- **truncate**: Is a key value pair linking the tag property value with the max number of characters allowed in the content.
- **twitter**: When set to `true`, the package will render the basic Twitter cards from the Open Graph values. _Note_: this will automatically be set to true when a Twitter specific tag is set.

### Configuration Example

```php
$og = new \MetaTags\Manager([
    'validate' => true,
    'twitter' => false,
    'truncate' => [
        'description' => 200,
    ],
]);
```

## Change Log

**v0.0.1**

 - First release
