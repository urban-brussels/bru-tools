<?php
use phayes\geophp;
use Symfony\Component\HttpClient\HttpClient;

class Main
{
  const GEOSERVER_URBIS_ADM   = "//geoservices-urbis.irisnet.be/geoserver/UrbisAdm/wms";
  const GEOSERVER_URBIS_LOC   = "//geoservices.irisnet.be/localization/Rest/Localize";
  const GEOSERVER_URBIS     = "//geoservices-others.irisnet.be/geoserver/ows";
  const GEOSERVER_BRUGIS      = "//www.mybrugis.irisnet.be/geoserver/wms";
  const GEOSERVER_BRU_MOBIL   = "//data-mobility.brussels/geoserver/bm_public_transport/wms";
  const TIMEOUT = 10; 

  const NOVA_WFS_FIELDS = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'Nova:vmnovaurbanview',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
      'propertyname' => 's_iddossier,typedossier,refnova,zipcode,municipalityfr,municipalitynl,statutpermisfr,statutpermisnl,officeautorized,officeexisting,officeprojected,dateardosscomplet,datenotifdecision,datelimitepermis,datecc,mpp,avisfd,streetnamefr,streetnamenl,numberpartfrom,numberpartto,referencespecifique,geometry'
    );

  const BRUGIS_MAILLE_FIELDS = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'BDU:Maille',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
      'sortBy' => 'GMLINK A'
    );

  const BRUGIS_CQDPROG_FIELDS = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'BDU_DRU:Contrats_de_quartiers_Programmes',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
      'sortBy' => 'ID_DRU D'
    );

  const BRUGIS_CQDPROJ_FIELDS = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'BDU_DRU:Contrats_de_quartiers_Projets',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
      'sortBy' => 'ID_DRU D'
    );

  const BRUGIS_CRUPROG_FIELDS = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'BDU_DRU:Contrats_de_renovation_urbaine_Programmes',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json'
    );

  const BRUGIS_PREEMPTION_FIELDS = array(
      'service' => 'WFS',
      'version' => '2.0.0',
      'request' => 'GetFeature',
      'typeName' => 'BDU_DRU:Zones_de_preemption',
      'srsName' => 'EPSG:31370',
      'outputFormat' => 'json',
    );
}
