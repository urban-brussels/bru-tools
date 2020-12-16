<?php

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

    public static function checkArenderKnowsDocument(string $identifier): bool
    {
        $http_code = shell_exec("curl -I " . $_ENV['ARENDER_BASE_PATH'] . "/rendergwt/uploadServlet?uuid=" . $identifier . " | grep \"^HTTP\/\"");
        return $http_code === 200;
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
