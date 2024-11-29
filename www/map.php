<?php
//session_start();

require 'mapdata.php';

if(!isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}

if($num_devices == 0) {
    echo '
    <div style="display: flex; justify-content: center;">
        <h1>No hay dispositivos vinculados a tu cuenta.</h1>
    </div>
    <div style="display: flex; justify-content: center;">
        <img src="bombCS16.png" alt="No devices found" style="width: 600px; height: 600px;">
    </div>
    <div style="display: flex; justify-content: center;">
        <a href="settings.php">Ir a configuración</a>
    </div>
    ';
    exit();
}
if($nogpsData == true) {
    echo '
    <div style="display: flex; justify-content: center;">
        <h1>No hay datos registrados.</h1>
    </div>
    <div style="display: flex; justify-content: center;">
        <img src="noGPSdata.png" alt="No data found" style="width: 1152px; height: 768px;">
    </div>
    ';
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
    
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        .leaflet-container {
            height: 400px;
            width: 600px;
            max-width: 100%;
            max-height: 100%;
        }
        #map-layer {
            margin: 0 auto;
            text-align: center;
            width: 100%; /*1000px;*/
            height: 90%; /*800px;*/
        }
        #settings-link {
            position: absolute;
            top: 30px;
            right: 10px;
        }
    </style>

    <title>Furry Finder</title>
</head>

<body>
    
    <a id="settings-link" href="settings.php">Configuración</a>
    <h1 style="text-align: center;">Última localización de tu mascota</h1>
    <div id="map-layer"></div>

    <script>
        const catIcon = L.icon({
          iconUrl: 'catPIN.png',

          //Aca dice como es el tamaño del pin y donde se posiciona el pin en una coordenada
          iconSize:     [37.5, 62.5], // size of the icon
          iconAnchor:   [18.75, 62.5], // point of the icon which will correspond to marker's location
          popupAnchor:  [0, -65]
        });
        const dogIcon = L.icon({
          iconUrl: 'dogPIN.png',

          //Aca dice como es el tamaño del pin y donde se posiciona el pin en una coordenada
          iconSize:     [37.5, 62.5], // size of the icon
          iconAnchor:   [18.75, 62.5], // point of the icon which will correspond to marker's location
          popupAnchor:  [0, -65]
        });
        
        const animalGroups = [
            L.layerGroup(),
            L.layerGroup(),
            L.layerGroup(),
            L.layerGroup(),
            L.layerGroup()
        ];
        
        var overlays = {
            '<?php echo $devices_data[0]['animal_name'];?>': animalGroups[0]
        };

        <?php
            // Mostrar los datos de la matriz
            for ($i = 0; $i < 5; $i++) {
                for ($j = 0; $j < 10; $j++) {
                    $row = $gps_data[$i][$j];
                    if ($row) {
                        $lat = $row['lat'];
                        $lng = $row['lng'];
                        $_did = $row['device_id'];
                        $animal_type = $devices_data[$i]['animal_type'];
                        $animal_name = $devices_data[$i]['animal_name'];
                        $created_at = date('d/m/Y H:i:s', strtotime($row['created_at']) - 3 * 60 * 60);
                        // Mostrar los datos en la página
                        echo "var marker = L.marker([$lat, $lng], {icon: ${animal_type}Icon})
                                            .bindPopup('<b>$animal_name</b><br/>$created_at')
                                            .addTo(animalGroups[$i]);
                        overlays['$animal_name'] = animalGroups[$i];";
                    }
                }
            }
        ?>
        
        const map = L.map('map-layer', {
            center: [<?php echo $centerLat;?>, <?php echo $centerLng;?>],
            zoom: 16,
            layers: [animalGroups[0]]
        });

        for (let i = 1; i < 5; i++) {
            if(animalGroups[i].getLayers().length > 0) {
                map.addLayer(animalGroups[i]);
            }
        }

        for (let i = 0; i < 5; i++) {
            var group = animalGroups[i];
            for (let j = 1; j < 10; j++) {
                var marker = group.getLayers()[j];
                if (marker) {
                    marker.setOpacity(0);
                }
            }
        }

        // Define a custom control
        const markerPickerControl = L.Control.extend({
        onAdd: function(map) {
            // Create the select element
            const markerPicker = L.DomUtil.create('select', 'marker-picker');

            // Add the options to the select element
            const optionAll = L.DomUtil.create('option', '', markerPicker);
            optionAll.value = 'all';
            optionAll.text = 'Mostrar todo';

            const optionLast = L.DomUtil.create('option', '', markerPicker);
            optionLast.value = 'last';
            optionLast.text = 'Último marcador';

            markerPicker.value = 'last';

            // Add the onchange event to the select element
            markerPicker.onchange = function() {
            const value = markerPicker.value;

                if (value === 'all') {
                    // Mostrar todos los marcadores
                    for (let i = 0; i < 5; i++) {
                        var group = animalGroups[i];
                        for (let j = 0; j < 10; j++) {
                            var marker = group.getLayers()[j];
                            if (marker) {
                                marker.setOpacity(1);
                            }
                        }
                    }
                } else if (value === 'last') {
                    // Ocultar todos los marcadores excepto los primeros de cada índice
                    for (let i = 0; i < 5; i++) {
                        var group = animalGroups[i];
                        for (let j = 1; j < 10; j++) {
                            var marker = group.getLayers()[j];
                            if (marker) {
                                marker.setOpacity(0);
                            }
                        }
                    }
                }
            };

            // Add the select element to the control container
            L.DomEvent.disableClickPropagation(markerPicker);
            const container = L.DomUtil.create('div', 'marker-picker-container');
            container.appendChild(markerPicker);

            return container;
        },

        onRemove: function(map) {
            // Nothing to do here
        }
        });

        // Add the custom control to the map
        const markerPickerControlInstance = new markerPickerControl({ position: 'topright' });
        markerPickerControlInstance.addTo(map);

        const layerControl = L.control.layers(null, overlays).addTo(map);

        const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            minZoom: 10,
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
    </script>
</body>
</html>