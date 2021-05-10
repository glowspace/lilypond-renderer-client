<?php

use Orchestra\Testbench\TestCase;
use ProScholy\LilypondRenderer\LilypondPartsRenderConfig;
use ProScholy\LilypondRenderer\LilypondPartsTemplate;
// use ProScholy\LilypondRenderer\LilypondSrc;

class LilypondPartsTemplateTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['ProScholy\LilypondRenderer\LilypondRendererServiceProvider'];
    }

    public function testGlobalSrc()
    {
        $src = new LilypondPartsTemplate('globalProperty = { c }');

        $srcStr = (string)$src;
        $this->assertStringContainsString('\include "satb_parts/satb-header.ly"', $srcStr);
        // global.ily is required in each part, not in the total file
        // this may change so that global.ily is only in the total file
        // $this->assertStringContainsString('\include "global.ily"', $srcStr);
        $this->assertStringContainsString('\include "satb_parts/satb-footer.ly"', $srcStr);

        $this->assertContains('global.ily', array_keys($src->getIncludeFilesString()));
        $this->assertContains('satb_parts/base-tkit.ly', array_keys($src->getIncludeFiles()));
        $this->assertStringContainsString('globalProperty = { c }', $src->getIncludeFilesString()['global.ily']);
        $this->assertStringContainsString('\layout { \context { \Staff \consists "Merge_rests_engraver" } }', $src->getIncludeFilesString()['global.ily']);
        $this->assertStringContainsString('font = #"amiri"', $src->getIncludeFilesString()['global.ily']);
    }


    public function testAddPart()
    {
        $src = new LilypondPartsTemplate();

        $src->withPart('sloka', 'solo = { c }');

        $this->assertContains('sloka.ly', array_keys($src->getIncludeFilesString()));
        $this->assertStringContainsString('solo = { c }', $src->getIncludeFilesString()['sloka.ly']);
        $this->assertStringContainsString('timeSignature = \time 4/4', $src->getIncludeFilesString()['sloka.ly']);
    }

    public function testCustomGlobalConfig()
    {
        $config = new LilypondPartsRenderConfig([
            'version' => '2.20.0',
            'two_voices_per_staff' => false,
            'global_transpose_relative_c' => 'g',
            'merge_rests' => false,
            'hide_bar_numbers' => false,
            'force_part_breaks' => true,

            'hide_voices' => ['akordy']
        ]);

        $src = new LilypondPartsTemplate('', $config);

        $src->withPart('sloka', 'solo = { c }');

        $this->assertStringContainsString('\version "2.20.0"', (string)$src);
        $this->assertStringContainsString('twoVoicesPerStaff = ##f', $src->getIncludeFilesString()['global.ily']);
        $this->assertStringContainsString('globalTransposeRelativeC = g', $src->getIncludeFilesString()['global.ily']);
        $this->assertStringContainsString('akordyHide = ##t', $src->getIncludeFilesString()['global.ily']);
    }
}