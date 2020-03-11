<?php
use phayes\geophp;
use Symfony\Component\HttpClient\HttpClient;

class NovaPE extends Main
{
  public static function filterByGeoError(int $year = null)
  {
    $filter = "CREATIONDATE >= '".$year."-01-01' AND CREATIONDATE <= '".$year."-12-31'";
    $filter .= " AND GEOMETRY is null";
    return $filter;
  }

  public static function filterById(int $iddossier)
  {
    return 's_iddossier='.$iddossier;
  }

  public static function filterByRefnovaInt(int $refnova_int)
  {
    return "REFNOVA LIKE '%/".$refnova_int."'";
  }

  public static function filterByRefnova(string $refnova)
  {
    return "REFNOVA='".$refnova."'";
  }

  public static function filterByRefIbge(string $refibge)
  {
    return "REFIBGE='".$refibge."'";
  }

  public static function filterByRefnovaOrRefspec(string $refnova)
  {
    $refnova = strtoupper($refnova);
    return "REFNOVA='".$refnova."' OR REFMUNICIPALITY='".$refnova."'";
  }

  public static function filterByLastDaysNotif(int $days)
  {
    return "DATEDECISION<='".date("Y-m-d")."T23:59:59Z' AND DATEDECISION>='".date("Y-m-d", strtotime('-'.$days.' days'))."T23:59:59Z' AND IBGESTATUS <> 'I'";
  }

  public static function filterByLastDaysDepot(int $days)
  {
    return "CREATIONDATE<='".date("Y-m-d")."T23:59:59Z' AND CREATIONDATE>='".date("Y-m-d", strtotime('-'.$days.' days'))."T23:59:59Z'";
  }

  public static function filterByInquiryDate(string $date = null)
  {
   return "DATEDEBUTMPP <= '".date("Y-m-d")."T23:59:59Z' AND DATEFINMPP >= '".date("Y-m-d")."T00:00:00Z' AND DATEDEBUTMPP >= '".date("Y-m-d", strtotime("-40 days"))."T10:00:00Z' AND DATEFINMPP <= '".date("Y-m-d", strtotime("40 days"))."T10:00:00Z'";
 }

 public static function filterByIncidence(int $year = null)
 {
  $filter = "(CLASSIFICATION='1A' OR CLASSIFICATION='1B')";
  if(!is_null($year)) { 
    $filter .= " AND DATEDEBUTMPP >= '".$year."-01-01T00:00:00Z' AND DATEDEBUTMPP <= '".$year."-12-31T00:00:00Z'"; 
  };
  return $filter;
}

public static function filterByRequest(int $year = null, int $month = null)
{
  $next_month = $month+1;
  if(strlen($month) == 1) { $month = "0".$month; };
  if(strlen($next_month) == 1) { $next_month = "0".$next_month; };
  $filter = " CREATIONDATE >= '".$year."-".$month."-01' and CREATIONDATE < '".$year."-".$next_month."-01'"; 
  return $filter;
}

public static function filterByDecision(int $year = null, int $month = null)
{
  $next_month = $month+1;
  if(strlen($month) == 1) { $month = "0".$month; };
  if(strlen($next_month) == 1) { $next_month = "0".$next_month; };
  $filter = " DATEDECISION >= '".$year."-".$month."-01' AND DATEDECISION < '".$year."-".$next_month."-01' AND IBGESTATUS <> 'I'";
  return $filter;
}

public static function getPermitsData(string $cql_filter = null, int $srs = 31370, string $properties = null, int $count = null, string $sortBy = null)
{
  $url = self::GEOSERVER_URBIS_HTTP;
  $fields = array(
    'service' => 'WFS',
    'version' => '2.0.0',
    'request' => 'GetFeature',
    'typeName' => 'NovaCitoyenPeDossiersPoint',
    'srsName' => 'EPSG:'.$srs,
    'outputFormat' => 'json',
    'cql_filter' => ($cql_filter ?? "CREATIONDATE<='".date("Y-m-d")."T23:59:59Z'"),
    'propertyname' => $properties,
    'sortBy' => ($sortBy ?? "CREATIONDATE D"),
    'count' => ($count ?? 500),
  );
  // $client = new \GuzzleHttp\Client();
  $httpClient = HttpClient::create();

 try {
      $response = $httpClient->request('POST', $url, ['query' => $fields]);
      $statusCode = $response->getStatusCode();
      if($statusCode != 200) { return null; };
      $json = json_decode((string)$response->getContent());
    } catch (TransportExceptionInterface $e) {
      return null;
      //var_dump($e->getMessage());
    }

    $json = json_decode((string)$response->getContent());

    if(!isset($json->features)) { return null; };

  $nb = count($json->features);

  foreach($json->features as $key => $value)
  {
    $property = (array)$value->properties;

    $property['s_iddossier']  = $property['IDDOSDOSSIER']; unset($property['IDDOSDOSSIER']);
    $property['refnova']      = $property['REFNOVA']; unset($property['REFNOVA']);

    $property['datedepot']      = $property['CREATIONDATE']; unset($property['CREATIONDATE']);
    $property['datenotifdecision'] = $property['DATEDECISION']; unset($property['DATEDECISION']);

    $property['dateardosscomplet'] = $property['DATEARDOSSCOMPLET']; unset($property['DATEARDOSSCOMPLET']);
    $property['datecc'] = $property['DATECC']; unset($property['DATECC']);
    $property['aviscc'] = $property['AVISCC']; unset($property['AVISCC']);
    $property['avissiamu'] = $property['AVISSIAMU']; unset($property['AVISSIAMU']);

    $property['uuid']      = $property['UUID']; unset($property['UUID']);

    $property['realobjectfr']      = $property['OBJECTFR']; unset($property['OBJECTFR']);
    $property['realobjectnl']      = $property['OBJECTNL']; unset($property['OBJECTNL']);
    $property['datedebutmpp']      = $property['DATEDEBUTMPP']; unset($property['DATEDEBUTMPP']);
    $property['datefinmpp']      = $property['DATEFINMPP']; unset($property['DATEFINMPP']);

    $property['etatfinal']      = $property['IBGESTATUS']; unset($property['IBGESTATUS']);

    $property['pu_pe']      = "PE"; 

    $property['managingauthority'] = $property['DELAUTHORITY']; unset($property['DELAUTHORITY']);

    if($property['IDPARENTDOSSIER']!="") { 
      $property['utg'] = self::getPermitUtgData($property['IDPARENTDOSSIER'], 4326);
    };

    $property['zipcode']      = $property['utg']['ZIPCODENP'] ?? "";

    $property['streetnamefr']      = $property['utg']['STREETNAMEFRNP'] ?? "";
    $property['streetnamenl']      = $property['utg']['STREETNAMENLNP'] ?? "";

    $property['municipalityfr']      = $property['utg']['MUNICIPALITYNAMEFRNP'] ?? "";
    $property['municipalitynl']      = $property['utg']['MUNICIPALITYNAMENLNP'] ?? "";
    $property['numberpartfrom']      = $property['utg']['NUMBERFROM'] ?? "";
    $property['numberpartto']   = $property['utg']['NUMBERTO'] ?? "";

    $property['wkt']      = $property['utg']['wkt'] ?? null; unset($property['utg']['wkt']);
    $property['coord']      = $property['utg']['coord'] ?? null; unset($property['utg']['coord']);
    $property['geojson']      = $property['utg']['geojson'] ?? null; unset($property['utg']['geojson']);

    $property['referencespecifique']   = $property['REFMUNICIPALITY'];
    $property['mixedpermit']   = $property['MIXIBGE'];

    // errors
    $property['error'] = null;

    if($property['CLASSIFICATION'] == '1A') {
      $property['ei'] = "true";
      $property['ri'] = "false";
    }
    elseif($property['CLASSIFICATION'] == '1B') {
      $property['ri'] = "true";
      $property['ei'] = "false";
    }
    else {
      $property['ri'] = "false";
      $property['ei'] = "false";      
    };

      // Check MPP dates
    if( isset($property['datedebutmpp']) ) {
      $datetime1 = new \DateTime($property['datedebutmpp'] ?? null);
      $datetime2 = new \DateTime($property['datefinmpp'] ?? null);  $datetime2->add(new \DateInterval('P1D'));  /* Add 13 hours... Nova WFS at 11am */
      $interval = $datetime1->diff($datetime2);
      $property['dureempp'] = $interval->format('%a');

      $date = new \DateTime("now");
      if($date >= $datetime1 && $date <= $datetime2) {
          $property['statut_mpp']       = "actif";
          $property['statut_incidence'] = "actif";
        }
        elseif($date >= $datetime1){
          $property['statut_mpp']       = "inactif";
          $property['statut_incidence'] = "actif";
        }
        else {
          $property['statut_mpp']       = "inactif";
          $property['statut_incidence'] = "inactif";
        };
    };

    // Workaround Date CC 11 pm (ask cirb fix)
    if(isset($property['datecc'])) {
      $datetimecc = new \DateTime($property['datecc'] ?? null);
      $datetimecc->add(new \DateInterval('PT2H'));

      $property['datecc'] = $datetimecc;
    };

    
      // Date errors
      $property['error'] = null;

      if( array_key_exists("datedepot",$property) && array_key_exists("dateardosscomplet",$property) && array_key_exists("datenotifdecision",$property) && array_key_exists("datedebutmpp",$property) && array_key_exists("datefinmpp",$property) && array_key_exists("datecc",$property)
          && (
          ($property['datedepot'] > date("Y-m-d")."T23:59:59Z")
          || ($property['dateardosscomplet'] > date("Y-m-d")."T23:59:59Z")
          || ($property['datenotifdecision'] > date("Y-m-d")."T23:59:59Z")
          || ($property['datedepot'] <> '1111-11-11T01:00:00Z' && $property['datedepot']<'1800-01-01')
          || (!is_null($property['datenotifdecision']) && $property['datedepot'] > $property['datenotifdecision'])
          || (!is_null($property['datedebutmpp']) && $property['datedebutmpp'] > $property['datefinmpp'])
          || (!is_null($property['datecc']) && $property['datecc'] < $property['datedepot'])
          || (!is_null($property['datedebutmpp']) && $property['datedebutmpp'] < $property['datedepot'])
          || (!is_null($property['datefinmpp']) && $property['datefinmpp'] < $property['datedepot'])
          || ($property['streetnamefr'] == '' && $property['streetnamenl'] == '')
          || ($property['zipcode'] == '')
        ))
      {
          $property['error'] = "dates";
      };

    $data[] = $property;
  }

  return $data ?? null;
}

// public static function getNovacUrl(int $idparent = null, int $iddossier = null, int $lg = "fr", $x, $y, $date_decision = null)
// {
//   if($iddossier %2 == 0) {
//     $type = "perm";
//   }
//   else {
//     $type = "doss";
//   }

//   $url = "http://novac-pe.irisnet.be/assets/detail/detail.html?id=".$idparent."&lng=".$fr."&type=".$type."&dossid=".$iddossier."&x=".$x."&y=".$y;
//   return $url;
// }

public static function getPermitUtgData(string $iddossier = null, int $srs = 31370)
{
  $url = self::GEOSERVER_URBIS_HTTP;
  $fields = array(
    'service' => 'WFS',
    'version' => '2.0.0',
    'request' => 'GetFeature',
    'typeName' => 'NovaCitoyenPeutGsPoint',
    'srsName' => 'EPSG:'.$srs,
    'outputFormat' => 'json',
    'cql_filter' => "IDDOSDOSSIER='".$iddossier."'"
  );
  $httpClient = HttpClient::create();

 try {
      $response = $httpClient->request('POST', $url, ['query' => $fields]);
      $statusCode = $response->getStatusCode();
      if($statusCode != 200) { return null; };
      $json = json_decode((string)$response->getContent());
    } catch (TransportExceptionInterface $e) {
      return null;
      //var_dump($e->getMessage());
    }

    $json = json_decode((string)$response->getContent());

    if(!isset($json->features[0])) { return null; };

  $property = (array)$json->features[0]->properties;

  if(isset($json->features[0]->geometry)) { 
    $property['lon'] = $json->features[0]->geometry->coordinates[0];
    $property['lat'] = $json->features[0]->geometry->coordinates[1];
  };


       // Get centroid, wkt
  $geom = $json->features[0]->geometry;
  if(!is_null($geom))
  {
    $geom_json = \geoPHP::load($geom, 'json');
    $property['wkt'] = $geom_json->out('wkt');
    $centroid = $geom_json->centroid();
    $property['coord'][0] = str_replace(",", ".", $centroid->getX());
    $property['coord'][1] = str_replace(",", ".", $centroid->getY());    
    $property['geojson'] = json_encode($geom);    
  }
  else { 
    $property['wkt'] = null;
    $property['coord'] = [];
  };

  $data = $property;

  return $data ?? null;
}

}
