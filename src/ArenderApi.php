<?php
use Symfony\Component\HttpClient\HttpClient;

class ArenderApi
{
    public static function removeSpecialCharacters(string $string): string
    {
        return \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')
            ->transliterate($string);
    }

    public static function sanitizeFolderName(string $document_title, string $reference): string
    {
        $document_title = self::removeSpecialCharacters($document_title);
        $references = [str_replace('/', '', $reference) . ' - ', $reference, str_replace('/', '', $reference)];

        $document_title = utf8_encode(trim(str_replace($references, '', $document_title)));
        $document_title = preg_replace('/^\d+_/', '', $document_title);
        $document_title = substr($document_title, 0, strrpos($document_title, "."));
        $document_title = str_replace('_', ' ', $document_title);
        $document_title = str_replace("'", "´", $document_title);
        
        return $document_title;
    }

    public static function checkArenderKnowsDocument(string $identifier): ?string
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $_ENV['ARENDER_BASE_PATH'] . "/arendergwt/uploadServlet?uuid=" . $identifier, ['timeout' => 3000]);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if ($statusCode !== 200) {
                return null;
            };
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        $uuid = substr($content, 1, -1);

        $response = $httpClient->request('GET', "http://10.128.82.38:8761/document/?documentId=" . $uuid);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if ($content !== "true") {
//                dd("http://10.128.82.38:8761/document/?documentId=" . $uuid, $content);

                return null;
            };
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        return $uuid;

    }
    
    public static function defineCategories(string $locale): array
    {
        $json['title'] = '';
        $json['references'][0]['title'] = $locale === 'nl' ? 'Plannen' : 'Plans';
        $json['references'][1]['title'] = $locale === 'nl' ? 'Foto\'s' : 'Photos';
        $json['references'][2]['title'] = $locale === 'nl' ? 'Anderen' : 'Autres';
        return $json;
    }

    public static function documentCategory(string $document_title): int
    {
        if (stripos($document_title, 'photo') !== false || stripos($document_title, 'foto') !== false) {
            return 1;
        }

        if (stripos($document_title, 'plan') !== false || stripos($document_title, 'coupe') !== false) {
            return 0;
        }

        return 2;
    }

    public static function uuidFromPostedJson($json): string
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV['ARENDER_BASE_PATH'] . '/arendergwt/compositeAccessorServlet',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
