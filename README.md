# Requirements

This package needs a running [Lilypond renderer server](https://github.com/proscholy/lilypond-renderer-server).

# Installing the package

Add following to your composer.json and then run `composer update`.

~~~
"require": {
    "proscholy/lilypond-renderer-client": "0.9.*"
},

"repositories": [
    {
        "type": "vcs", 
        "url": "https://github.com/proscholy/lilypond-renderer-client"
    }
],
~~~

If used in a Laravel environment, than execute this command..
~~~
php artisan vendor:publish --provider="ProScholy\LilypondRenderer\LilypondRendererServiceProvider"
~~~

..and, add these config values to the .env file

~~~
LILYPOND_HOST= #host of the lilypond server
LILYPOND_PORT= #port of the lilypond server
~~~


## Developing

The phpunit.xml file is configured to expect a running LilyPond server on localhost, port 3100.
So, ensure this has been running and composer is installed.

Then, unit tests can be executed with `./vendor/bin/phpunit`.

# Documentation

This library provides a PHP link to creating (unified) scores in LilyPond.
It supports generic LilyPond source code input, as well as a sophisticated custom template.

## Usage example

This library provides four basic classes:

- `Client.php` that provides communication with the renderer server
- `LilypondPartsRenderConfig.php` which stores render-specific configuration
- `LilypondPartsTemplate.php` which builds up a LilyPond "renderable code"
- `RenderedScore.php` which encapsulates the renderer server's response

An example of workflow may look like this:

~~~
<?php

use ProScholy\LilypondRenderer\Client;
use ProScholy\LilypondRenderer\LilypondPartsRenderConfig;
use ProScholy\LilypondRenderer\LilypondPartsTemplate;
use ProScholy\LilypondRenderer\RenderedScore;

// 1. instantiate a client with the address
// of a running server instance
$client = new Client('localhost', '3100');

// 2. create a render layout configuration
$config = new LilypondPartsRenderConfig([
    'merge_rests' => true,
    'hide_bar_numbers' => false,
    'hide_voices' => ['tenor', 'bas']
]);

// 3. instantiate a templated LilyPond source
$src = new LilypondPartsTemplate('helperVariable = { d }', $config)
    ->withPart('chorus', 'solo = { c \helperVariable }');

// 4. do the rendering process and retrieve the output file
/* @var RenderedScore */
$res = $client->renderSvg($src);
$svg = $client->getResultOutputFile($res);
~~~

See the tests/ files for more example usage.

## Available layout configurations

All available configuration is available via the LilypondPartsRenderConfig object.
Consult the function getDefaultConfigData() which contains instructions about available configurations.

Internally, these settings are used to conditionally include "stubs" (src/lilypond_stubs/parts/*.txt) that
work either independently or are related to the custom template in src/ly_includes/satb_parts (such as defining variables).