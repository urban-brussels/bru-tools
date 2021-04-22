<?php

use Symfony\Component\HttpClient\HttpClient;

class PspdfkitApi
{

    public static function uploadDocument(string $uuid, string $path)
    {
        $curl = "curl -X POST \"http://pvdcl-arndr01:5000/api/documents\" -H  \"accept: application/json\" -H  \"Authorization: Token token=secret\" -H  \"Content-Type: multipart/form-data\" -F \"document_id=".$uuid."\" -F \"keep_current_annotations=\" -F \"overwrite_existing_document=false\" -F \"title=\" -F \"url=\" -F \"file=@".$path."".$uuid."\"";
        exec($curl);
    }

    public static function checkDocument(string $uuid)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request(
            'GET',
            "http://pvdcl-arndr01:5000/api/documents/".$uuid."/document_info",
            [
                'headers' => [
                    'Authorization' => 'Token token=secret',
                ],
            ]
        );

        return $response->getStatusCode() === 200 ? true : false;
    }
}
