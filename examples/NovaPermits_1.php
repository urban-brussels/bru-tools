<?php
include("../conf.php");

$permis = NovaPermits::getPermits(null, 5); 

echo "<pre>";
print_r($permis);
echo "</pre>"; 


$url = NovaPermits::getPermits(null, 5, null, "url"); 

echo "<pre>";
print_r($url);
echo "</pre>"; 


$maille = UrbanLayers::getMailleList(); 

echo "<pre>";
print_r($maille);
echo "</pre>"; 


$maille = UrbanLayers::getMailleGeomFromCode("EVE-01"); 

echo "<pre>";
print_r($maille);
echo "</pre>"; 

$coord_cq = "POINT(150000 169339)";
$cq = UrbanLayers::getCqdFromGeom($coord_cq, 31370, true);

echo "<pre>";
print_r($cq);
echo "</pre>"; 

$maille = UrbanLayers::getMailleFromGeom($coord_cq);

echo "<pre>";
print_r($maille);
echo "</pre>"; 
