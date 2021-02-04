<?php

namespace ProScholy\LilypondRenderer;

use GuzzleHttp\Client as HttpClient;
// use GuzzleHttp\Exception\RequestException;
// use GuzzleHttp\Exception\ClientException;

// use ProScholy\LilypondRenderer\Models\Order;
// use ProScholy\LilypondRenderer\Models\OrderItem;
// use ProScholy\LilypondRenderer\Models\Recipient;

// use Exception;

// use ProScholy\LilypondRenderer\Exceptions\ERPApiException;
// use ProScholy\LilypondRenderer\Models\Transaction;

class Client
{
    protected $client;

    public function __construct()
    {
        $this->client = new HttpClient([
            'base_uri' => config('lilypond_renderer.host') . ':' . config('lilypond_renderer.port')
        ]);
    }

    // private function request(string $method, string $endPoint, array $params = [])
    // {
    //     try {
    //         $response = $this->client->request($method, $endPoint, $params);
    //     } catch (RequestException $ex) {
    //         $resp = $ex->getResponse();
    //         $data = json_decode($resp->getBody()->getContents());

    //         throw new ERPApiException($data->status . ' (' . $resp->getStatusCode() . ')', $resp->getStatusCode());
    //     }

    //     // other possible exceptions: GuzzleHttp\Exception\ServerException (504: timed out)
    //     // but those should be catched in the app...I guess? 

    //     return json_decode($response->getBody()->getContents());
    // }

    public function renderSvg($lilypond_src)
    {
        $response = $this->client->post('make?recipe=svgcrop', [
            'multipart' => [
                [
                    'name'     => 'file_lilypond', // input name, needs to stay the same
                    'contents' => $lilypond_src,
                    'filename' => 'score.ly' // doesn't matter
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function isRenderSuccessful($result)
    {
        foreach ($result[0]->contents as $file) {
            if ($file->name == 'score_cropped.svg') {
                return true;
            }
        }

        return false;
    }

    public function getProcessedFile($tmp, $filename)
    {
        $response = $this->client->get("get?dir=$tmp&file=$filename");

        return $response->getBody()->getContents();
    }

    public function getSvgCrop($res)
    {
        $tmp = $res[0]->name;
        return $this->getProcessedFile($tmp, 'score_cropped.svg');
    }

    public function getLog($res)
    {
        $tmp = $res[0]->name;
        return $this->getProcessedFile($tmp, 'log.txt');
    }

    public function deleteResult($res)
    {
        $tmp = $res[0]->name;
        $response = $this->client->get("del?dir=$tmp");

        return $response->getBody()->getContents() == "ok\n";
    }
}
