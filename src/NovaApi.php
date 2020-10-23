<?php

use Symfony\Component\HttpClient\HttpClient;

class NovaApi
{
    public static function getApiToken(array $nova_api_env)
    {
        $output = shell_exec('curl -k -d "grant_type=client_credentials&scope=' . $nova_api_env['scope'] . '" -H "Authorization: Basic ' . base64_encode($nova_api_env['consumer_key'] . ":" . $nova_api_env['consumer_secret']) . '" ' . $nova_api_env['endpoint'] . 'api/token');
        $exp = explode('"', $output);
        return $exp[3];
    }

    public static function getApiDocsList(array $nova_api_env, ?array $id_list, string $type = "ID", bool $with_key = false)
    {
        $content = [
            'auth_bearer' => self::getApiToken($nova_api_env),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],

            'body' => self::getReferencesJson($id_list, $type),
        ];

        if ($with_key) {
            $content['headers']['ADVICE_EXTERNAL'] = $nova_api_env['ADVICE_EXTERNAL'];
            $content['headers']['x-jwt-api-key'] = $nova_api_env['jwt'];
        }
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $nova_api_env['endpoint'] . 'api/nova-api/document/1.0.0/list/', $content);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if ($statusCode == 200 && $content) {
                $content = $response->toArray();
            };
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        return $content['publications'] ?? null;
    }

    public static function getApiDocDownload(array $nova_api_env, string $identifier, bool $with_key = false)
    {
        $content = [
            'auth_bearer' => self::getApiToken($nova_api_env),
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ]
        ];

        if ($with_key) {
            $content['headers']['x-jwt-api-key'] = $nova_api_env['jwt'];
        }

        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $nova_api_env['endpoint'] . 'api/nova-api/document/1.0.0/download/identifier/UUID/' . $identifier, $content);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        return $content ?? null;
    }

    public function getAuthorizationString(array $nova_api_env)
    {
        return base64_encode($nova_api_env['username'] . ":" . $nova_api_env['password']);
    }

    public static function getReferencesJson(?array $id_list, string $type = "ID")
    {
        $identifiers["identifiers"] = array();

        $nb = is_countable($id_list) ? count($id_list) : 0;

        for ($i = 0; $i < $nb; $i++) {
            $identifiers["identifiers"][$i]['identifier']['key'] = $id_list[$i];
            $identifiers["identifiers"][$i]['identifier']['type'] = $type;
            // CONTEXT
            $identifiers["identifiers"][$i]['identifier']['context'] = 'CASE';
        };

        return json_encode($identifiers);
    }

}
