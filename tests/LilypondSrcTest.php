<?php

use Orchestra\Testbench\TestCase;
// use ProScholy\LilypondRenderer\LilypondBasicTemplate;
use ProScholy\LilypondRenderer\LilypondBasicTemplate;
use ProScholy\LilypondRenderer\LilypondSrc;

class LilypondBasicTemplateTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['ProScholy\LilypondRenderer\LilypondRendererServiceProvider'];
    }

    public function testRaw()
    {
        $src = new LilypondBasicTemplate('{ c }');

        $this->assertEquals('{ c }', trim((string)$src));
    }

    public function testDefaultLayout()
    {
        $src = new LilypondBasicTemplate('{ c }');
        $src->applyDefaultLayout();

        $this->assertStringContainsString("\layout", (string)$src);
    }

    public function testDefaultLayoutFontSize()
    {
        $src = new LilypondBasicTemplate('{ c }');
        $src->applyDefaultLayout('amiri', 4);

        $this->assertStringContainsString("fontSize = 4", (string)$src);
    }

    public function testInfinitePaper()
    {
        $src = new LilypondBasicTemplate('{ c }');
        $src->applyInfinitePaper();

        $this->assertStringContainsString("\paper", (string)$src);
        $this->assertStringContainsString("120 mm", (string)$src);
        
        $src->applyInfinitePaper(200);
        $this->assertStringContainsString("200 mm", (string)$src);
    }

    public function testOriginalKey()
    {
        $src = new LilypondBasicTemplate('{ c }');
        $src->setOriginalKey('fis');

        $this->assertStringContainsString("originalKey = fis", (string)$src);
    }

    public function testIncludeDir()
    {
        $src = new LilypondSrc('');
        $src->withIncludeDirectory('satb_parts');

        $this->assertContains('satb_parts/base-tkit.ly', $src->getIncludeFiles());
    }
    
    /**
     * @expectedException Exception
     */
    public function testIncludeWrongDir()
    {
        $src = new LilypondSrc('');

        $src->withIncludeDirectory('saminamina_ee_wakawaka_e_e');
    }
}