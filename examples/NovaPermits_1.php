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

$coord_cq = "POINT(147000 171000)";
$cq = UrbanLayers::getCqdFromGeom($coord_cq, 31370, true);

echo "<pre>";
print_r($cq);
echo "</pre>"; 

$preemption = UrbanLayers::getZonePreemptionFromGeom($coord_cq);

echo "<pre>";
print_r($preemption);
echo "</pre>"; 

$cru = UrbanLayers::getCruFromGeom($coord_cq);

echo "<pre>";
print_r($cru);
echo "</pre>"; 

$coordinates = "POINT(147000 171000)"; // Lat / Lon in WKT format
$crs = 31370; // Coordinate Reference System (31370 for Lambert 72 - 4386 for WGS84)
$cqd = UrbanLayers::getCqdFromGeom($coordinates, $crs, $actif = false);

echo "<pre>";
print_r($cqd);
echo "</pre>"; 