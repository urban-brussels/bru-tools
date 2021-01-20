<?php
use ici\ici_tools\WfsLayer;
use Symfony\Component\HttpClient\HttplugClient;


class UrbanLayers extends Main
{
	public static function getMailleList()
	{
		$url = self::GEOSERVER_BRUGIS;
		$fields = self::BRUGIS_MAILLE_FIELDS;
		$fields['propertyname'] = "GMLINK"; 

		$client = new GuzzleHttp\Client();

    // return $url . "?" . http_build_query($fields);

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

	public static function getMailleGeomFromCode(string $maille)
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

	public static function getMailleFromGeom(string $coord_wkt, int $crs = 31370)
	{
		$url 	= self::GEOSERVER_BRUGIS;
		$fields = self::BRUGIS_MAILLE_FIELDS;

		$fields['cql_filter'] = "INTERSECTS(GEOMETRY, ".$coord_wkt.")";

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

		$data = $json->features[0]->properties->GMLINK;


		return $data ?? null;
	}

	public static function getCqdFromGeom(string $coord_wkt, int $crs = 31370, bool $actif = true)
	{
		$url = self::GEOSERVER_BRUGIS;
		$fields = self::BRUGIS_CQDPROG_FIELDS;
		$fields['propertyname'] = "ID_DRU,NOM_FR,NOM_NL,DT_DEBUT,DT_FIN"; 
		$fields['cql_filter'] = "INTERSECTS(GEOMETRY, ".$coord_wkt.")";
		if($actif) { $fields['cql_filter'] .= " AND ACTIF='Oui'"; };

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
			$data[] = $value->properties;
		}

		return $data ?? "out";
	} 

	public static function getZonePreemptionFromGeom(string $coord_wkt, int $crs = 31370, bool $actif = true)
	{
		$url = self::GEOSERVER_BRUGIS;
		$fields = self::BRUGIS_PREEMPTION_FIELDS;
		$fields['cql_filter'] = "INTERSECTS(GEOMETRY, ".$coord_wkt.")";
		if($actif) { $fields['cql_filter'] .= " AND ACTIF='1'"; };

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
			$data[] = $value->properties;
		}

		return $data ?? "out";
	} 


	public static function getCruFromGeom(string $coord_wkt, int $crs = 31370, bool $actif = true)
	{
		$url = self::GEOSERVER_BRUGIS;
		$fields = self::BRUGIS_CRUPROG_FIELDS;
		$fields['cql_filter'] = "INTERSECTS(GEOMETRY, ".$coord_wkt.")";
		//if($actif) { $fields['cql_filter'] .= " AND ACTIF='1'"; };

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
			$data[] = $value->properties;
		}

		return $data ?? "out";
	}

	public static function getEspStructurantsFromGeom(string $coord_wkt, int $crs = 31370)
    {
        $wfs = new WfsLayer('https://gis.urban.brussels/geoserver/ows', 'PER:PRDD_A10_C9_AXES_STRUCTURANTS');
        $json = $wfs->setCqlFilter("INTERSECTS(GEOM, " . $coord_wkt . ")")->setOutputSrs($crs)->getResults();
        
        return $json;
    }

    public static function getZicheeFromGeom(string $coord_wkt, int $crs = 31370, int $buffer = 0): bool
    {
        $wfs = new WfsLayer('https://gis.urban.brussels/geoserver/ows', 'BDU:Zichee');
        $json = $wfs->setCqlFilter("INTERSECTS(GEOMETRY, BUFFER(" . $coord_wkt . ",".$buffer."))")->setOutputSrs($crs)->getResults();

        return count($json->features) > 0 ? true : false;
    }

    public static function getEdrlrFromGeom(string $coord_wkt, int $crs = 31370): bool
    {
        $wfs = new WfsLayer('https://gis.urban.brussels/geoserver/ows', 'BDU:EDRLR');
        $json = $wfs->setCqlFilter("INTERSECTS(GEOMETRY, " . $coord_wkt . ")")->setOutputSrs($crs)->getResults();

        return count($json->features) > 0 ? true : false;
    }
}
