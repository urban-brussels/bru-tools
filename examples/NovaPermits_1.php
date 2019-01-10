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


$maille = UrbanLayers::getMaille("EVE-01"); 

echo "<pre>";
print_r($maille);
echo "</pre>"; 