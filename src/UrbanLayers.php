<?php
class UrbanLayers extends Main
{
  public static function getMailleList()
  {
    $url = self::GEOSERVER_BRUGIS;
    $fields = self::BRUGIS_MAILLE_FIELDS;
    $fields['propertyname'] = "GMLINK"; 

    $client = new GuzzleHttp\Client();

    try
    {
      $response = $client->request('GET', $url . "?" . http_build_query($fields) , ['timeout' => self::TIMEOUT]);
      $json = json_decode((string)$response->getBody());
    }
    catch(Exception $e)
    {
      return;
    }

    foreach($json->features as $key => $value)
    {
      $data[] = $value->properties->GMLINK;
    }

    return $data ?? null;
  }

  public static function getMaille(string $maille)
  {
    $url 	= self::GEOSERVER_BRUGIS;
    $fields = self::BRUGIS_MAILLE_FIELDS;

    if (!is_null($maille) )  { $fields['cql_filter'] = "GMLINK='".$maille."'"; };

    $client = new GuzzleHttp\Client();

    try
    {
      $response = $client->request('GET', $url . "?" . http_build_query($fields) , ['timeout' => self::TIMEOUT]);
      $json = json_decode((string)$response->getBody());
    }
    catch(Exception $e)
    {
      return;
    }

    $geom = geoPHP::load($json,'json');
    $data = $geom->out('wkt');

    return $data ?? null;
  }

}
