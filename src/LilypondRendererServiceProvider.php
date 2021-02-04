<?php

namespace ProScholy\LilypondRenderer;
use Illuminate\Support\ServiceProvider;

class LilypondRendererServiceProvider extends ServiceProvider
{
    /**
    * Publishes configuration file.
    *
    * @return  void
    */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/lilypond_renderer.php' => config_path('lilypond_renderer.php'),
        ], 'lilypond-renderer-config');
    }
    /**
    * Make config publishment optional by merging the config from the package.
    *
    * @return  void
    */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/lilypond_renderer.php',
            'lilypond_renderer'
        );
    }
}