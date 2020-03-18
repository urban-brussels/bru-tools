<?php
class NextcloudApi
{
    public static function createFolder(array $nextcloud_env, string $folder_name = null)
    {
        $query = 'curl -u '.$nextcloud_env['user'].':'.$nextcloud_env['password'].' -X MKCOL https://'.$nextcloud_env['url'].'/remote.php/dav/files/'.$nextcloud_env['user'].'/share/'.$folder_name;
        $output = shell_exec($query);
        return $query;
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

        $data_curl = shell_exec('curl -u "'.$nextcloud_env['user'].':'.$nextcloud_env['password'].'" -H "OCS-APIRequest: true" -X POST https://'.$nextcloud_env['url'].'/ocs/v1.php/apps/files_sharing/api/v1/shares -d path="/'.$folder_name.'" '.$share_rules);

        $xml = new \SimpleXMLElement($data_curl);
        $data['url'] 		= str_replace("http://", "https://", $xml->data->url);

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
