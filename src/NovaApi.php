<?php
use Symfony\Component\HttpClient\HttpClient;

class NovaApi
{
    public static function getApiToken(array $nova_api_env)
    {
        $output = shell_exec('curl -k -d "grant_type=client_credentials&scope='.$nova_api_env['scope'].'" -H "Authorization: Basic '.base64_encode($nova_api_env['consumer_key'].":".$nova_api_env['consumer_secret']).'" '.$nova_api_env['endpoint'].'api/token');
        $exp = explode('"', $output);
        return $exp[3];
    }

    public static function getApiDocsList(array $nova_api_env, ?array $id_list, string $type = "ID")
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $nova_api_env['endpoint'].'api/nova-api/document/1.0.0/list/', [
            'auth_bearer' => self::getApiToken(),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                // 'Cache-Control' => 'no-cache',
            ],

            'body' => self::getReferencesJson($id_list, $type),
        ]);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if($statusCode == 200 && $content) {
                $content = $response->toArray();
            };
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        return $content['publications'] ?? null;
    }

    public static function getApiDocDownload(array $nova_api_env, string $identifier)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $nova_api_env['endpoint'].'api/nova-api/document/1.0.0/download/identifier/UUID/'.$identifier, [
            'auth_bearer' => self::getApiToken(),
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ]
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        return $content ?? null;
    }

    public function getAuthorizationString(array $nova_api_env)
    {
        return base64_encode($nova_api_env['username'].":".$nova_api_env['password']);
    }

    public function getFixMyStreetList(array $nova_api_env, $zipcode)
    {
        $token = self::getApiToken($nova_api_env);

        $httpClient = HttpClient::create(['auth_bearer' => $token,
            'headers' => [
                'Accept' => 'application/hal+json',
            ]
        ]);

        $response = $httpClient->request('GET', $nova_api_env['endpoint'].'/api/fixmystreet/1.0.0/incidents', [
            'query' => [
                'municipality' => $zipcode
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

    public static function getReferencesJson(?array $id_list, string $type = "ID")
    {
        $identifiers["identifiers"] = array();

        $nb = is_countable($id_list) ? count($id_list) : 0;

        for($i = 0; $i < $nb; $i++) {
            $identifiers["identifiers"][$i]['identifier']['key']  = $id_list[$i];
            $identifiers["identifiers"][$i]['identifier']['type'] = $type;
        };

        return json_encode($identifiers);
    }

}
