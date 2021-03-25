<?php

use Orchestra\Testbench\TestCase;
use ProScholy\LilypondRenderer\LilypondSrc;

class LilypondSrcTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['ProScholy\LilypondRenderer\LilypondRendererServiceProvider'];
    }

    public function testRaw()
    {
        $src = new LilypondSrc('{ c }');

        $this->assertEquals('{ c }', trim((string)$src));
    }

    public function testDefaultLayout()
    {
        $src = new LilypondSrc('{ c }');
        $src->applyLayout();

        $this->assertStringContainsString("\layout", (string)$src);
    }

    public function testDefaultLayoutFontSize()
    {
        $src = new LilypondSrc('{ c }');
        $src->applyLayout('default_layout', 'amiri', 4);

        $this->assertStringContainsString("fontSize = 4", (string)$src);
    }

    public function testInfinitePaper()
    {
        $src = new LilypondSrc('{ c }');
        $src->applyInfinitePaper();

        $this->assertStringContainsString("\paper", (string)$src);
        $this->assertStringContainsString("120 mm", (string)$src);
        
        $src->applyInfinitePaper(200);
        $this->assertStringContainsString("200 mm", (string)$src);
    }

    public function testOriginalKey()
    {
        $src = new LilypondSrc('{ c }');
        $src->setOriginalKey('fis');

        $this->assertStringContainsString("originalKey = fis", (string)$src);
    }

    public function testTargetKey()
    {
        $src = new LilypondSrc('{ c }');
        $src->setTargetKey('fis');

        $this->assertStringContainsString("targetKey = fis", (string)$src);
    }
    
    public function testDisableMelodie()
    {
        $src = new LilypondSrc('{ c }');
        $src->disableParts(['melodie']);
    
        $this->assertStringContainsString("melodie = ##f", (string)$src);
    }
}