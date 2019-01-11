# BRU-tools
Outils basés sur les données et webservices disponibles en Région de Bruxelles-Capitale

## Installation

```sh
composer require urban-brussels/bru-tools
```

## Rénovation urbaine

### Contrat de Quartier Durable
```php
<?php
// Le point est-il situé dans un "Contrat de Quartier Durable" ?
$coordinates 	= "POINT(147000 171000)"; // Lat / Lon in WKT format
$crs 			= 31370; // Coordinate Reference System (31370 for Lambert 72 - 4386 for WGS84)
$actif 		= false; // If true, retrieves only active CQD

$cqd 			= UrbanLayers::getCqdFromGeom($coordinates, $crs, $actif);

echo "<pre>";
print_r($cqd);
echo "</pre>"; 

// Array
// (
//     [0] => stdClass Object
//         (
//             [ID_DRU] => 51
//             [NOM_FR] => Ecluse - Saint-Lazare
//             [NOM_NL] => Sluis-Sint-Lazarius
//             [DT_DEBUT] => 2008-12-23
//             [DT_FIN] => 2012-12-22
//         )

//     [1] => stdClass Object
//         (
//             [ID_DRU] => 11
//             [NOM_FR] => Duchesse de Brabant
//             [NOM_NL] => Hertogin van Brabant
//             [DT_DEBUT] => 1999-06-16
//             [DT_FIN] => 2003-06-15
//         )

// )
```

### Nova - Permis d'urbanisme
```php
to do
```

