<?php
use phayes\geophp;
use Symfony\Component\HttpClient\HttpClient;

class NovaPU extends Main
{
  public static function getPermitType(string $refnova)
  {
    if( strstr($refnova, 'IPE') || strstr($refnova, 'CL') || strstr($refnova, 'IRCE') || strstr($refnova, 'ICE') || strstr($refnova, 'C_') || strstr($refnova, 'PLP') || strstr($refnova, 'IRPE') ) {
      return "PE"; 
    }
    else {
      return "PU";
    }
  }

  public static function getNovaLink(int $iddossier)
  {
    return "https://nova.brussels/nova-ui/page/open/request/AcmDisplayCase.xhtml?ids=&id=".$iddossier."&uniqueCase=true";
  }

  public static function filterByDataError()
  {
    // Date + 6 months
    $date = new \DateTime();
    $interval = new \DateInterval('P6M');
    $date->add($interval);

    $filter = "(datedepot>'".date("Y-m-d")."T23:59:59Z' OR dateardosscomplet>'".date("Y-m-d")."T23:59:59Z' OR datenotifdecision>'".date("Y-m-d")."T23:59:59Z'";
    $filter .= " OR (datedepot <> '1111-11-11T01:00:00Z' AND datedepot<'1800-01-01')";
    $filter .= " OR datedepot>datenotifdecision";
    //$filter .= " OR datedebutmpp>datefinmpp";
    $filter .= " OR datecc < datedepot";
   // $filter .= " OR datedebutmpp < datedepot";
   // $filter .= " OR datefinmpp < datedepot";
    $filter .= " OR (streetnamefr == '' AND streetnamenl == '')";
    $filter .= " OR zipcode == '') ";
    return $filter;
  }

  public static function filterByGeoError(int $year = null)
  {
    $filter = "datedepot >= '".$year."-01-01' AND datedepot <= '".$year."-12-31'";
    $filter .= " AND geometry is null";
    return $filter;
  }

  public static function filterById(int $iddossier)
  {
    return 's_iddossier='.$iddossier;
  }

  public static function filterByRefnovaInt(int $refnova_int)
  {
    return "refnova LIKE '%/".$refnova_int."'";
  }

  public static function filterByRefnova(string $refnova)
  {
    $refnova = strtoupper($refnova);
    return "refnova='".$refnova."'";
  }
  
  public static function filterByRefnovas(array $refnovas)
  {
    return "refnova IN ('".implode("','", $refnovas)."')";
  }

  public static function filterByMunicipality(int $zipcode)
  {
    if($zipcode == 1000) {
      $filter = "zipcode IN (1000, 1020, 1120, 1130)"; }
      else {
        $filter = "zipcode='".$zipcode."'";
      }

      return "AND ".$filter;
    }

    public static function filterByRefnovaOrRefspec(string $refnova)
    {
      $refnova = strtoupper($refnova);
      return "refnova='".$refnova."' OR referencespecifique='".$refnova."'";
    }

    public static function filterByRefIbge(string $refibge)
    {
      return "refmixedpermit='".$refibge."'";
    }

    public static function filterByLastDaysNotif(int $days)
    {
      return "datenotifdecision<='".date("Y-m-d")."T23:59:59Z' AND datenotifdecision>='".date("Y-m-d", strtotime('-'.$days.' days'))."T23:59:59Z'";
    }

    public static function filterByLastDaysDepot(int $days)
    {
      return "datedepot<='".date("Y-m-d")."T23:59:59Z' AND datedepot>='".date("Y-m-d", strtotime('-'.$days.' days'))."T23:59:59Z'";
    }

    public static function filterByInquiryDate(string $date = null)
    {
      return "datedebutmpp <= '".date("Y-m-d")."T23:59:59Z' AND datefinmpp >= '".date("Y-m-d")."T00:00:00Z' AND datedebutmpp >= '".date("Y-m-d", strtotime("-40 days"))."T10:00:00Z' AND datefinmpp <= '".date("Y-m-d", strtotime("40 days"))."T10:00:00Z'";
   }

    public static function filterByInquiryCheck()
    {
       return "datedebutmpp <= '".date("Y-m-d", strtotime("+7 days"))."T23:59:59Z' AND datefinmpp >= '".date("Y-m-d")."T00:00:00Z' AND datedebutmpp >= '".date("Y-m-d", strtotime("-40 days"))."T10:00:00Z' AND datefinmpp <= '".date("Y-m-d", strtotime("40 days"))."T10:00:00Z'";
   }

   public static function filterByIncidence(int $year = null)
   {
    $filter = "(ri=true or ei=true)";
    if(!is_null($year)) { 
      $filter .= " and datedebutmpp >= '".$year."-01-01' and datedebutmpp <= '".$year."-12-31'"; 
    };
    return $filter;
  }


  public static function filterByConcertation(int $year = null, int $month = null)
  {
    $next_month = $month+1;
    if(strlen($month) == 1) { $month = "0".$month; };
    if(strlen($next_month) == 1) { $next_month = "0".$next_month; };
    $filter = " datecc >= '".$year."-".$month."-01' and datecc < '".$year."-".$next_month."-01'"; 
    return $filter;
  }

  public static function filterByRequest(int $year = null, int $month = null)
  {
    $next_month = $month+1;
    if(strlen($month) == 1) { $month = "0".$month; };
    if(strlen($next_month) == 1) { $next_month = "0".$next_month; };
    $filter = " datedepot >= '".$year."-".$month."-01' and datedepot < '".$year."-".$next_month."-01'"; 
    return $filter;
  }

  public static function filterByDecision(int $year = null, int $month = null)
  {
    $next_month = $month+1;
    if(strlen($month) == 1) { $month = "0".$month; };
    if(strlen($next_month) == 1) { $next_month = "0".$next_month; };
    $filter = " datenotifdecision >= '".$year."-".$month."-01' and datenotifdecision < '".$year."-".$next_month."-01'"; 
    return $filter;
  }

  public static function filterByNewCobatOpen()
  {
    $filter = "datedepot >= '2019-09-01' AND datedepot <= '2020-12-31'"; // Attention, virer le filtre 2020 dans le futur
    $filter .= " AND datenotifdecision is null";
    $filter .= " AND (typedossier in ('PFD', 'PFU') OR avisfd = 'Oui')";
    $filter .= " AND refnova is not null";
    return $filter;
  }

  public static function filterByGeometry(?string $geom, $buffer = "-0.7")
  {
    if(is_null($geom)) { return 's_iddossier=000000'; };
    if(!is_null($buffer) && !stristr($geom, "POINT")) { $geom = "buffer(".$geom.",".$buffer.")"; };
    return "INTERSECTS(geometry, ".$geom.")";
  }

  public static function filterByPointDistance(?string $point, $meters = 15)
  {
    return "DWITHIN(geometry, ".$point.", ".$meters.", meters)";
  }

  public static function getPermitsData(string $cql_filter = null, int $srs = 31370, string $properties = null, int $count = null, string $sortBy = null)
  {
    $url = 'http:'.self::GEOSERVER_URBIS;
    $fields = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'Nova:vmnovaurbanview',
      'srsName' => 'EPSG:'.$srs,
      'outputFormat' => 'json',
      'cql_filter' => ($cql_filter ?? "datedepot<='".date("Y-m-d")."T23:59:59Z'")." AND realobjectfr NOT LIKE 'test %' AND refnova NOT LIKE '%AC/%'",
      'propertyname' => $properties,
      'sortBy' => ($sortBy ?? "datedepot D"),
      'count' => ($count ?? 2000),
    );

    $httpClient = HttpClient::create();

    //if(is_null($cql_filter)) { return $fields; exit; };

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
      unset($property['bbox']);

      // Clean dates
      foreach($property as $k => $v)
      {
        if(strstr($k, "date")) {
          $property[$k] = !is_null($v) ? substr($v,0,10) : null;
        }
        // Cleaning objects
        elseif(strstr($k, "object")) {
          $property[$k] = trim($v);
        }
        // Replace Oui/Non by true/false
        elseif(in_array($k, ['mpp', 'aviscbe', 'avisfd', 'aviscc'])) {
          if($v === 'Non' || $v === 'false' || $v === false)   { $property[$k] = "false"; }
          elseif($v === 'Oui' || $v === 'true' || $v === true) { $property[$k] = "true"; }
          else                          { $property[$k] = null; };
        };
      };

      // Correction status (il y a des permis octroyés qui restent avec un statut "Instruction")
      if(isset($property['statutpermisfr']) && $property['statutpermisfr'] == "Octroyé") {
        $property['etatfinal'] = "V";
      };

      // Get centroid, wkt
      $geom = $value->geometry;
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

      // Get CaSBA sums
      if( isset($property['officeautorized']) ) {
        $property['casba_autorized'] = $property['officeautorized'] ?? null + ($property['intgoodsautorized'] ?? null);
        $property['casba_existing']  = $property['officeexisting'] ?? null + ($property['intgoodsexisting'] ?? null);
        $property['casba_projected'] = $property['officeprojected'] ?? null + ($property['intgoodsprojected'] ?? null);
      };

      $property['pu_pe']      = "PU";
      if(isset($property['casesubtype']) && in_array($property['casesubtype'], ["PFD", "PFU", "SFD", "ECO", "CPFD", "GOU_PU", "LPFD", "LPFU", "CPFU", "LCFU", "LSFD"])) {
      	$property['managingauthority'] = "REGION";
      }
      elseif(isset($property['casesubtype'])) {
      	$property['managingauthority'] = "COMMUNE";
      }
      
      // Check MPP dates
      if( isset($property['datedebutmpp']) ) {
        $datetime1 = new \DateTime($property['datedebutmpp'] ?? null);
        $datetime2 = new \DateTime($property['datefinmpp'] ?? null);  $datetime2->add(new \DateInterval('P1D'));  /* Add 13 hours... Nova WFS at 11am */
        $interval = $datetime1->diff($datetime2);
        $property['dureempp'] = $interval->format('%a');

        $date = new \DateTime("now");
        if($date >= $datetime1 && $date <= $datetime2){
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

      // Date errors
      $property['error'] = null;

      if( array_key_exists("datedepot",$property) && array_key_exists("dateardosscomplet",$property) && array_key_exists("datenotifdecision",$property) && array_key_exists("datedebutmpp",$property) && array_key_exists("datefinmpp",$property) && array_key_exists("datecc",$property)
        && (
          ($property['datedepot'] > date("Y-m-d")."T23:59:59Z")
          || ($property['dateardosscomplet'] > date("Y-m-d")."T23:59:59Z")
          || ($property['datenotifdecision'] > date("Y-m-d")."T23:59:59Z")
          || ($property['datedepot'] <> '1111-11-11T01:00:00Z' && $property['datedepot']<'1800-01-01')
          || (!is_null($property['datenotifdecision']) && $property['datedepot'] > $property['datenotifdecision'])
       //   || (!is_null($property['datedebutmpp']) && $property['datedebutmpp'] > $property['datefinmpp'])
          || (!is_null($property['datecc']) && $property['datecc'] < $property['datedepot'])
       //   || (!is_null($property['datedebutmpp']) && $property['datedebutmpp'] < $property['datedepot'])
       //   || (!is_null($property['datefinmpp']) && $property['datefinmpp'] < $property['datedepot'])
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

}
