<?php
class NovaPermits extends Main
{
  public static function getPermits(string $cql_filter = null, int $count = null, ?string $orderBy = "datenotifdecision, dateardosscomplet D", string $output = "data")
  {
    $url    = self::GEOSERVER_URBIS;
    $fields = self::NOVA_WFS_FIELDS;
    if (!is_null($cql_filter) )  { $fields['cql_filter'] = $cql_filter; };
    if (!is_null($count) )       { $fields['count'] = $count; };
     if (!is_null($orderBy) )     { $fields['orderBy'] = $orderBy; };

    // Return url only
    if($output == "url") { return $url. "?" . http_build_query($fields); };

    $client = new GuzzleHttp\Client();

    try
    {
      $response = $client->request('POST', $url, ['form_params' => $fields, 'timeout' => self::TIMEOUT]);
      $json = json_decode((string)$response->getBody());
    }
    catch(Exception $e)
    {
      return;
    }
    $nb = isset($json->features) ? count($json->features) : 0;

    foreach($json->features as $key => $value)
    {
      $casba_properties = (array)$value->properties;
      unset($casba_properties['bbox']);
      $casba_properties['dateardosscomplet'] = substr($casba_properties['dateardosscomplet'],0,10);
      $casba_properties['datenotifdecision'] = substr($casba_properties['datenotifdecision'],0,10);
      $casba_properties['datelimitepermis'] = substr($casba_properties['datelimitepermis'],0,10);
      $casba_properties['datecc'] = substr($casba_properties['datecc'],0,10);
      $geom = $value->geometry;
      if(!is_null($geom))
      {
        $centroid = geoPHP::load($geom, 'json')->centroid();
        $casba_properties['coord'][0] = $centroid->getX();
        $casba_properties['coord'][1] = $centroid->getY();        
      };

      $data[] = $casba_properties;
    }

    return $data ?? null;
  }
}
