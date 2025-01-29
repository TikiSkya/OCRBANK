<?php
if (isset($_GET['info'])) {
    $info = urldecode($_GET['info']);
    parse_str($info, $info);


} else {
    $info = "No hay información disponible.";
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Comprobante</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>
    <div class="container">
        <header>
            <h1>Detalles del Comprobante Cargado</h1>
        </header>
        <div class="details-box">
            <?php

            foreach ($info as $clave => $valor) {
                if ($clave == "Error") {

                    echo "<div class='error-message'>". htmlspecialchars($info['Error'])."</div>";
                } else {
                    echo "<p><strong>$clave:</strong> $valor</p>";
                }
            }
            ?>
        </div>
        <div class="actions">
            <a href="index.html" class="button">Subir Otro Archivo</a>
        </div>
    </div>
</body>

</html>
