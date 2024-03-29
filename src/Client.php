<?php

namespace ProScholy\LilypondRenderer;

use GuzzleHttp\Client as HttpClient;
use Exception;
use ProScholy\LilypondRenderer\InputFileType;

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
        $host = $host ?? config('lilypond_renderer.host');
        $port = $port ?? config('lilypond_renderer.port');
        if (is_null($host)) {
            throw new Exception('The $host variable needs to be defined when no config (Laravel) is available.');
        }

        $base_url = $host;
        if (!empty($port)) {
            $base_url .= ':' . $port;
        }

        $this->client = new HttpClient([
            'base_uri' => $base_url
        ]);
    }

    /**
     * Create a 'make' request with given recipe out of the supported recipes.
     *
     * @param string $recipe
     * @param string $contents
     * @param boolean $zip
     * @return RenderResult
     */
    private function make(string $recipe, string $contents, InputFileType $input_t = InputFileType::LilypondSimple) : RenderResult
    {
        $name = 'file_lilypond';
        if ($input_t == InputFileType::LilypondZip) {
            $name = 'file_zip';
        } else if ($input_t == InputFileType::MusicXML) {
            $name = 'file_xml';
        }

        // todo: throw custom exceptions
        $response = $this->client->post("make?recipe=$recipe", [
            'multipart' => [
                [
                    'name'     => $name,
                    'contents' => $contents,
                    'filename' => 'score' // can be arbitrary
                ]
            ]
        ]);

        return new RenderResult($recipe, json_decode($response->getBody()->getContents()));
    }

    /**
     *  Render the $src with a given $recipe, decides automatically whether to use a zip file or textual file.
     *
     * @param string|LilypondSrc $src
     * @param string $recipe
     * @return RenderResult
     */
    public function render($src, string $recipe) : RenderResult
    {
        if ($src instanceof LilypondSrc && $src->hasIncludes()) {
            return $this->renderZip($src, $recipe);
        }

        return $this->make($recipe, (string)$src);
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
        $result = $this->make($recipe, $contents, InputFileType::LilypondZip);
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

    public function renderXml(string $src) : RenderResult
    {
        return $this->make('svgxml', $src, InputFileType::MusicXML);
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