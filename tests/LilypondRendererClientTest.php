<?php

use Orchestra\Testbench\TestCase;
use ProScholy\LilypondRenderer\Client;

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

        $this->assertIsArray($res);
        $this->assertTrue(count($res) == 1);
        $this->assertIsObject($res[0]);
        $this->assertObjectHasAttribute('name', $res[0]);
        $this->assertIsString($res[0]->name);
        $this->assertObjectHasAttribute('contents', $res[0]);
        $this->assertIsArray($res[0]->contents);

        return $res;
    }

    /**
     * @depends testBasicLilypond
     */
    public function testLilypondSuccess($res)
    {
        $this->assertTrue($this->client->isRenderSuccessful($res));
    }

    /**
     * @depends testBasicLilypond
     */
    public function testLilypondGetSvg($res)
    {
        $svg = $this->client->getSvgCrop($res);

        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
    }
    
    /**
     * @depends testBasicLilypond
     */
    public function testLilypondGetLog($res)
    {
        $log = $this->client->getLog($res);

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
        return $res;
    }

    /**
     * @depends testDeleteResult
     */
    public function testDeleteDeletedResult($res)
    {
        $deleted = $this->client->deleteResult($res);

        $this->assertFalse($deleted);
    }

    public function testDeleteNonExistentDir()
    {
        $fakeDir = new stdClass();
        $fakeDir->name = "your_mama";

        $deleted = $this->client->deleteResult([$fakeDir]);

        $this->assertFalse($deleted);
    }


    // MALFORMED LILYPOND SRC

    public function testLilypondErr()
    {
        $res = $this->client->renderSvg('{ c ');

        $this->assertFalse($this->client->isRenderSuccessful($res));
        return $res;
    }

    /**
     * @depends testLilypondErr
     */
    public function testLilypondErrorLog($res)
    {
        $log = $this->client->getLog($res);

        $this->assertIsString($log);
        $this->assertStringContainsString('fatal error: failed files: "score.ly"', $log);
    }
}