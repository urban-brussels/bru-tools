<?php
class Casba extends Main
{
  const TIMEOUT = 10; 

  public static function getPermitsCasba(string $limit = null, int $limit_date = 2000)
  {
    $orderBy  = 'datenotifdecision, dateardosscomplet';
    $cql_filter = " AND (dateardosscomplet > '".$limit_date."-01-01')"; // OR dateardosscomplet is null
    if(!is_null($limit)) 
    { 
      $cql_filter .= " AND ".$limit; 
    };

    $url = self::GEOSERVER_URBIS;
    $fields = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'Nova:vmnovaurbanview',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
      'cql_filter' => "casba='yes'".$cql_filter, // AND dateardosscomplet > '2014-01-01'
      'propertyname' => 's_iddossier,typedossier,refnova,zipcode,municipalityfr,statutpermisfr,officeautorized,officeexisting,officeprojected,dateardosscomplet,datenotifdecision,datelimitepermis,datecc,mpp,avisfd,streetnamefr,numberpartfrom,numberpartto,referencespecifique,geometry',
      'sortBy' => $orderBy
    );
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
        $geom2 = geoPHP::load($geom, 'json');
        $centroid = $geom2->centroid();
        $casba_properties['coord'][0] = $centroid->getX();
        $casba_properties['coord'][1] = $centroid->getY();        
      };

      $data[] = $casba_properties;
    }

    return $data ?? null;
  }

  public static function getMailleList()
  {
    $url = self::GEOSERVER_BRUGIS;
    $fields = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'BDU:Maille',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
      'propertyname' => 'GMLINK',
      'sortBy' => 'GMLINK A'
    );
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
    $url = self::GEOSERVER_BRUGIS;
    $fields = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'BDU:Maille',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
      'cql_filter' => "GMLINK='".$maille."'",
      'propertyname' => 'GEOMETRY'
    );
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

  public static function getZone_Affectation(array $coord)
  {

    $lat = $coord[1];
    $lon = $coord[0];

    /// WMS QUERY ///
    $url = self::GEOSERVER_BRUGIS;
    $fields = array(
      'service'       => 'WMS',
      'version'       => '2.0.0',
      'request'       => 'GetFeatureInfo',
      'layers'        => 'BDU:Affectations',
      'query_layers'  => 'BDU:Affectations',
      'srs'           => 'EPSG:31370',
      'info_format'   => 'application/json',
      'bbox'          => $lon.','.$lat.','.($lon + 0.000001).','.($lat + 0.000001),
      'height'        => 1,
      'width'         => 1,
      'x'             => 1,
      'y'             => 1,
      'buffer'        => 1,
      'propertyname'  => 'NAME_FR,NAME_NL,AFFECTATION'
    );

    $client   = new GuzzleHttp\Client();

    $url .= "?".http_build_query($fields);
  //  echo $url; exit;

    try { 
      $response = $client->request('GET', $url, ['form_params' => $fields, 'timeout' => self::TIMEOUT]);
      $json = json_decode((string)$response->getBody());
    }
    catch(Exception $e){
      return;
    }

    $nb = isset($json->features)?count($json->features):0;
    if($nb>0) 
    {     
      $data['name']['fr'] = ucfirst($json->features[0]->properties->NAME_FR);
      $data['name']['nl'] = ucfirst($json->features[0]->properties->NAME_NL);
      $data['code']     = $json->features[0]->properties->AFFECTATION;
    };  

    $data['name']['fr'] = $data['name']['fr'] ?? null;
    $data['name']['fr'] = str_replace("Zones ", "Z. ", $data['name']['fr']);
    $data['name']['fr'] = str_replace("habitation à prédominance résidentielle", "habitat. à pr. réd.", $data['name']['fr']);

    return $data['name']['fr'];
  } 

}
