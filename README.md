# Requirements

This package needs a running Lilypond Renderer server.
So far, this is a part of the ProScholy server dockerized codebase, but will be published as a stand-alone image.

# Installing the package

Add following to your composer.json

~~~
"require": {
    "proscholy/lilypond-renderer-client": "0.5.*"
},

"repositories": [
    {
        "type": "vcs", 
        "url": "https://github.com/proscholy/lilypond-renderer-client"
    }
],
~~~

then run

~~~
composer update
php artisan vendor:publish --provider="ProScholy\LilypondRenderer\LilypondRendererServiceProvider"
~~~

and finally, add these config values to the .env file

~~~
LILYPOND_HOST= #host of the lilypond server
LILYPOND_PORT= #port of the lilypond server
~~~