<?php

namespace ProScholy\LilypondRenderer;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use ProScholy\LilypondRenderer\Models\Order;
use ProScholy\LilypondRenderer\Models\OrderItem;
use ProScholy\LilypondRenderer\Models\Recipient;

use Exception;

use ProScholy\LilypondRenderer\Exceptions\ERPApiException;
use ProScholy\LilypondRenderer\Models\Transaction;

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

    public function makeSvg($lilypond_src)
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

    public function getProcessedFile($tmp, $filename)
    {
        $response = $this->client->get("get?dir=$tmp&file=$filename");

        return $response;
    }

    public function getSvgCrop($tmp)
    {
        return $this->getProcessedFile($tmp, 'score_cropped.ly');
    }

    public function getLog($tmp)
    {
        return $this->getProcessedFile($tmp, 'score.log');
    }
}
