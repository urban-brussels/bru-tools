<?php
include("conf.php");

$permis = Casba::getPermitsCasba(null, "2018-01-01"); 
print_r($permis);
