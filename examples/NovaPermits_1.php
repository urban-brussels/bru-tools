<?php
include("../conf.php");

$permis = NovaPermits::getPermitsCasba(null, "2018-01-01"); 

echo "<pre>";
print_r($permis);
echo "</pre>"; 
