<?php

namespace ProScholy\LilypondRenderer;

use GuzzleHttp\Client as HttpClient;
use Exception;

class Client
{
    protected $client;

    /**
     * Create a new instance of the Client. If using Laravel, the $host and $port variables are loaded from the config automatically.
     *
     * @param string|null $host
     * @param string|null $port
     */
    public function __construct(?string $host = null, ?string $port = null)
    {
        if (!function_exists('config')) {
            if (is_null($host) || is_null($port)) {
                throw new Exception('Both $host and $port variable need to be defined when no config (Laravel) is available.');
            }

            $this->client = new HttpClient([
                'base_uri' => $host . ':' . $port
            ]);
        } else {
            $this->client = new HttpClient([
                'base_uri' => config('lilypond_renderer.host') . ':' . config('lilypond_renderer.port')
            ]);
        }
    }

    /**
     * Create a 'make' request with given recipe out of the supported recipes.
     *
     * @param string $recipe
     * @param string $contents
     * @param boolean $zip
     * @return RenderResult
     */
    private function make(string $recipe, string $contents, bool $is_zip = false) : RenderResult
    {
        // todo: throw custom exceptions
        $response = $this->client->post("make?recipe=$recipe", [
            'multipart' => [
                [
                    'name'     => $is_zip ? 'file_zip' : 'file_lilypond', // input name, needs to stay the same
                    'contents' => $contents,
                    'filename' => $is_zip ? 'score.zip' : 'score.ly' // doesn't matter
                ]
            ]
        ]);

        return new RenderResult($recipe, json_decode($response->getBody()->getContents()));
    }

    /**
     *  Render the $lilypond_src with a given $recipe, decides automatically whether to use a zip file or textual file.
     *
     * @param string|LilypondSrc $lilypond_src
     * @param string $recipe
     * @return RenderResult
     */
    public function render($lilypond_src, string $recipe) : RenderResult
    {
        if ($lilypond_src instanceof LilypondSrc && $lilypond_src->hasIncludes()) {
            return $this->renderZip($lilypond_src, $recipe);
        }

        return $this->make($recipe, (string)$lilypond_src);
    }

    /**
     * Render the LilypondSrc with a given $recipe.
     *
     * @param LilypondSrc $lilypond_src
     * @param string $recipe
     * @return RenderResult
     */
    public function renderZip(LilypondSrc $lilypond_src, string $recipe) : RenderResult
    {
        $zipStream = $lilypond_src->getZippedSrcStream();
        $contents = stream_get_contents($zipStream);

        // obtain the result of the `make` request and close the memory stream
        $result = $this->make($recipe, $contents, true);
        fclose($zipStream);
        return $result;
    }

    /**
     * Render the $lilypond_src as svg, optionally cropped.
     *
     * @param string|LilypondSrc $lilypond_src
     * @param boolean $crop
     * @return RenderResult
     */
    public function renderSvg($lilypond_src, $crop = true) : RenderResult
    {
        return $this->render($lilypond_src, $crop ? 'svgopt' : 'svg');
    }

    /**
     * Get contents of a processed (output) file (after the render).
     *
     * @param string $tmp
     * @param string $filename
     * @return string
     */
    public function getProcessedFile(string $tmp, string $filename) : string
    {
        $response = $this->client->get("get?dir=$tmp&file=$filename");

        return $response->getBody()->getContents();
    }


    /**
     * Get the log file from a rendered result.
     *
     * @param RenderResult $res
     * @return string
     */
    public function getResultLog(RenderResult $res) : string
    {
        return $this->getProcessedFile($res->getTmp(), 'log.txt');
    }

    /**
     * Get the primary output file from the result.
     *
     * @param RenderResult $res
     * @return string
     */
    public function getResultOutputFile(RenderResult $res) : string
    {
        if (!$res->isSuccessful()) {
            throw new Exception("The result was unsuccessful, cannot get the final output file.");
        }

        return $this->getProcessedFile($res->getTmp(), $res->getRecipeOutputFile());
    }


    /**
     * Delete the files that are stored as the result of rendering.
     *
     * @param RenderResult $res
     * @return boolean true if deleting was successful, otherwise false.
     */
    public function deleteResult(RenderResult $res) : bool
    {
        $response = $this->deleteResultAsync($res)->wait();

        $success = $response->getBody()->getContents() == "ok\n";

        if ($success) {
            $res->markAsDeleted();
        }

        return $success;
    }

    private function deleteResultAsync(RenderResult $res)
    {
        $tmp = $res->getTmp();
        return $this->client->getAsync("del?dir=$tmp");
    }
}