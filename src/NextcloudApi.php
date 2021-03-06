<?php
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class NextcloudApi
{
    public static function createFolder(array $nextcloud_env, string $folder_name = null)
    {
        $query = 'curl -u '.$nextcloud_env['user'].':'.$nextcloud_env['password'].' -X MKCOL https://'.$nextcloud_env['url'].'/remote.php/dav/files/'.$nextcloud_env['user'].'/'.$nextcloud_env['root_folder'].'/'.$folder_name;
        $output = shell_exec($query);
        return $query;
    }
    
    public static function listFilesFromFolder(array $nextcloud_env, string $folder_name)
    {
        if(!strstr($folder_name, '/remote.php/dav/')) {
            $url = '/remote.php/dav/files/'.$nextcloud_env['user'].'/'.$nextcloud_env['root_folder'].'/'.$folder_name.'/';
        }
        else {
            $url = substr($folder_name, strpos($folder_name, '/remote.php/'));
        }

        $query = 'curl -u '.$nextcloud_env['user'].':'.$nextcloud_env['password'].' -X PROPFIND https://'.$nextcloud_env['url'].$url;
        $output = shell_exec($query);

        $data = [];

        $crawler = new Crawler($output);
        $doc['href'] = $crawler->filterXPath('//d:multistatus/d:response/d:href');

        $n = 0;
        foreach ($doc['href'] as $href) {
            if(substr($href->nodeValue, -1) !== '/'){
                $data[$n]['href'] = $href->nodeValue;
                $data[$n]['src'] = 'nextcloudapi';
                $exp_href = explode('/', $data[$n]['href']);
                $data[$n]['name']['label'] = urldecode($exp_href[count($exp_href)-1]);
                $data[$n]['identifier']['key'] = sha1($data[$n]['href']);
            }
            elseif($href->nodeValue !== $url && $href->nodeValue !== $folder_name && $href->nodeValue !== '/y'.$url) {
                $new_data = self::listFilesFromFolder($nextcloud_env, $href->nodeValue);
                $data = array_merge($data, $new_data);
            }
            $n++;
        }
        return $data;
    }

    public static function getApiDocDownload(array $nextcloud_env, ?string $file_path)
    {
        if(is_null($file_path)) {
            return null;
        }
        
        $content = [
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ],
            'auth_basic' => [$nextcloud_env['user'], $nextcloud_env['password']],
        ];

        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://'.strtok($nextcloud_env['url'],'/').$file_path, $content);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        return $content ?? null;
    }

    public static function shareFolder(array $nextcloud_env, string $folder_name = null, bool $password = true, $expireDate = null)
    {
        // Case 1: Shared with password
        if($password) {
            $share_password = self::createPassword();
            $share_rules = ' -d shareType=3 -d permissions=31 -d publicUpload=true -d password="'.$share_password.'"';
        }
        // Case 2: Public link with expiry date
        elseif(!is_null($expireDate) && $expireDate != "") {
            $date = new \DateTime($expireDate);
            $date->add(new \DateInterval('P1D'));
            $date_fin = $date->format('Y-m-d');

            $share_rules = ' -d shareType=3 -d permissions=1 -d publicUpload=false -d hideDownload=true -d expireDate="'.$date_fin.'"';
        }
        // Case 3: Public link without expiry date
        elseif(is_null($expireDate) || $expireDate == "") {
            $share_rules = ' -d shareType=3 -d permissions=1 -d publicUpload=false';
        };

        $data_curl = shell_exec('curl -u "'.$nextcloud_env['user'].':'.$nextcloud_env['password'].'" -H "OCS-APIRequest: true" -X POST https://'.$nextcloud_env['url'].'/ocs/v1.php/apps/files_sharing/api/v1/shares -d path="/'.$nextcloud_env['root_folder'].'/'.$folder_name.'" '.$share_rules);

        $xml = new \SimpleXMLElement($data_curl);
        $data['url'] 		= str_replace("http://", "https://", $xml->data->url);
        
        $data['curl'] 		= 'curl -u "'.$nextcloud_env['user'].':'.$nextcloud_env['password'].'" -H "OCS-APIRequest: true" -X POST https://'.$nextcloud_env['url'].'/ocs/v1.php/apps/files_sharing/api/v1/shares -d path="/'.$nextcloud_env['root_folder'].'/'.$folder_name.'" '.$share_rules;

        $data['password'] 	= $share_password ?? null;
        return $data;
    }

    public static function createPassword($length = 8)
    {
        return substr(str_shuffle('123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz'), 1, $length);
    }

    public static function createFolderName($folder_name = null)
    {
        $date = new \DateTime(null, new \DateTimeZone('Europe/Brussels'));
        $date_txt = $date->format('Y-m-d H:i:s');

        // date("Y-m-d h:i:s")

        $folder_name = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($folder_name);
        $folder_name = preg_replace("/[^a-zA-Z0-9\s]/", "", $folder_name);
        $folder_name = preg_replace('!\s+!', ' ', $folder_name);
        $folder_name = $date_txt." - ".$folder_name;
        $folder_name = str_replace(" ", "%20", $folder_name);

        return $folder_name;
    }

}
