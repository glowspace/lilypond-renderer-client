<?php

use Orchestra\Testbench\TestCase;
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
        $this->assertStringContainsString('\include "satb_parts/satb_header.ily"', $srcStr);
        // $this->assertStringContainsString('\include "global.ly"', $srcStr);
        $this->assertStringContainsString('\include "satb_parts/satb_footer.ily"', $srcStr);

        $this->assertContains('global.ily', array_keys($src->getIncludeFilesString()));
        $this->assertContains('satb_parts/base-tkit.ly', array_keys($src->getIncludeFiles()));
        $this->assertStringContainsString('globalProperty = { c }', $src->getIncludeFilesString()['global.ily']);
        $this->assertStringContainsString('TwoVoicesPerStaff = ##t', $src->getIncludeFilesString()['global.ily']);
    }


    public function testAddPart()
    {
        $src = new LilypondPartsTemplate();

        $src->withPart('sloka', 'solo = { c }');

        $this->assertContains('sloka.ly', array_keys($src->getIncludeFilesString()));
        $this->assertStringContainsString('solo = { c }', $src->getIncludeFilesString()['sloka.ly']);
        $this->assertStringContainsString('endPartTimeSignature = \time 4/4', $src->getIncludeFilesString()['sloka.ly']);
    }
}