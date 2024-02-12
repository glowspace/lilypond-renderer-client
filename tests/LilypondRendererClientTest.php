<?php

use Orchestra\Testbench\TestCase;
use ProScholy\LilypondRenderer\Client;
use ProScholy\LilypondRenderer\RenderResult;
use ProScholy\LilypondRenderer\LilypondBasicTemplate;
use ProScholy\LilypondRenderer\LilypondPartsTemplate;
use ProScholy\LilypondRenderer\LilypondSrc;

class LilypondRendererClientTest extends TestCase
{
    protected Client $client;

    protected function getPackageProviders($app)
    {
        return ['ProScholy\LilypondRenderer\LilypondRendererServiceProvider'];
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->client = new Client();
    }

    public function testBasicLilypond()
    {
        $res = $this->client->renderSvg('{ c }');

        $this->assertIsString($res->getTmp());
        $this->assertIsArray($res->getContents());

        return $res;
    }

    /**
     * @depends testBasicLilypond
     */
    public function testLilypondSuccess($res)
    {
        $this->assertTrue($res->isSuccessful());
    }

    /**
     * @depends testBasicLilypond
     */
    public function testLilypondGetSvg($res)
    {
        $svg = $this->client->getResultOutputFile($res);

        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
    }
    
    /**
     * @depends testBasicLilypond
     */
    public function testLilypondGetLog($res)
    {
        $log = $this->client->getResultLog($res);

        $this->assertIsString($log);
        $this->assertStringContainsString('Success: compilation successfully completed', $log);
    }

    /**
     * @depends testBasicLilypond
     */
    public function testDeleteResult($res)
    {
        $deleted = $this->client->deleteResult($res);

        $this->assertTrue($deleted);
        $this->assertTrue($res->isDeleted());

        return $res;
    }

    /**
     * @depends testDeleteResult
     */
    public function testDeleteDeletedResult($res)
    {
        $deletingSuccess = $this->client->deleteResult($res);

        $this->assertFalse($deletingSuccess);
        $this->assertTrue($res->isDeleted());
    }

    public function testDeleteNonExistentDir()
    {
        $fakeDir = new stdClass();
        $fakeDir->name = "your_mama";
        $fakeResult = new RenderResult('whatever_recipe', [$fakeDir]);

        $deletingSuccess = $this->client->deleteResult($fakeResult);

        $this->assertFalse($deletingSuccess);
    }

    public function testLilypondFromLilypondBasicTemplate()
    {
        $ly_src = new LilypondBasicTemplate('{ c }');
        $ly_src->applyDefaultLayout()->applyInfinitePaper();

        $res = $this->client->renderSvg($ly_src, false);

        $this->assertIsString($res->getTmp());
        $this->assertIsArray($res->getContents());

        return $res;
    }

    /**
     * @depends testLilypondFromLilypondBasicTemplate
     */
    public function testLilypondFromLilypondBasicTemplateSuccess($res)
    {
        $this->assertTrue($res->isSuccessful());

        $svg = $this->client->getResultOutputFile($res);

        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
    }


    // MALFORMED LILYPOND SRC

    public function testLilypondErr()
    {
        $res = $this->client->renderSvg('{ c ');

        $this->assertFalse($res->isSuccessful());
        return $res;
    }

    /**
     * @depends testLilypondErr
     */
    public function testLilypondErrorLog($res)
    {
        $log = $this->client->getResultLog($res);

        $this->assertIsString($log);
        $this->assertStringContainsString('fatal error: failed files: "score.ly"', $log);
    }

    public function testLilypondZip()
    {
        $res = $this->client->renderZip(new LilypondSrc('{ c }'), 'svg');

        $this->assertIsString($res->getTmp());
        $this->assertIsArray($res->getContents());

        return $res;
    }

    public function testLilypondZipMultipleFiles()
    {
        $src = new LilypondSrc('\include "satb_parts/vynech.ly"  \vynech { c }', [
            'satb_parts/vynech.ly'
        ]);

        $res = $this->client->renderSvg($src);

        $this->assertIsString($res->getTmp());
        $this->assertIsArray($res->getContents());

        return $res;
    }

    /**
     * @depends testLilypondZipMultipleFiles
     */
    public function testZipMultipleFilesSuccess($res)
    {
        $this->assertTrue($res->isSuccessful());
    }


    public function testLilypondPartRender()
    {
        $src = new LilypondPartsTemplate("stuff = { c' }");
        $src->withPart('1-2-3', "solo = { \\stuff d' e' }\n soloText = \\lyricmode { a -- hoj -- ky } ");
        $src->withPart('R', "solo = { fis'2 gis'2 }", 'fis');   

        $res = $this->client->renderSvg($src, true);

        $this->assertIsString($res->getTmp());
        $this->assertIsArray($res->getContents());

        file_put_contents('logs/score.zip', $src->getZippedSrcStream());
    }

    public function testBasicLilypondPdf()
    {
        $res = $this->client->render('{ c }', 'pdf');

        $this->assertIsString($res->getTmp());
        $this->assertIsArray($res->getContents());
        $this->assertTrue($res->isSuccessful());
    }

    public function testMusicXml() {
        // read test.xml
        $xml = file_get_contents(__DIR__ . '/test.xml');
        $res = $this->client->renderXml($xml);

        $this->assertIsString($res->getTmp());
        $this->assertIsArray($res->getContents());
        $this->assertTrue($res->isSuccessful());
    }
}