<?php
include("../conf.php");

$permis = NovaPermits::getPermitsCasba(null, 2018); 

echo "<pre>";
print_r($permis);
echo "</pre>"; 
