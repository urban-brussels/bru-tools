<?php
include("../conf.php");

$permis = Casba::getPermitsCasba(null, "2018-01-01"); 

echo "<pre>";
print_r($permis);
echo "</pre>"; 
