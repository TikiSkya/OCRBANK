<?php
/*
 * Controlador que gestiona el procesamiento de archivos ingresados via web
 * 
 * 
 * @author Gaston Rivero <tikirivero@gmail.com>
 * @version 1.0.0
 * 
 */

// Dependencias
include_once(__DIR__."\models\Rendimiento.php");
require_once(__DIR__."\models\OCR.php");

// Dependencias
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Empiezo a calcular procesamiento ocr
$rendimiento = new Rendimiento();
$rendimiento->startTime();

// Verifico tipo de archivo para saber si es imagen o pdf
if ($_FILES['file']["type"]) {

  // Mueve el archivo PDF a la carpeta de subida
  $pdfFilePath = $uploadDir . basename($_FILES['file']['name']);
  if (move_uploaded_file($_FILES['file']['tmp_name'], $pdfFilePath)) {
    // Convierte el PDF a imágenes
    $imagePaths = pdfToImages($pdfFilePath);
    // Elimina el archivo PDF después de procesarlo
    unlink($pdfFilePath);
  } else {
    echo "Error al subir el archivo.";
  }
  $ubicacionImagen = $imagePaths[0];

} else {

  $imagen = $_FILES['file'];
  $ubicacionImagen = $imagen["tmp_name"];

}

// Extraccion de texto
$ocr = new OCR();
$info = $ocr->extract($ubicacionImagen);

// Limpio los datos extraidos para visualizarlos en la web
$data  = [
  "Banco" => $info["datos"]["bank"],
  "IDTransacción" => $info["datos"]["transactionId"],
  "Monto"  =>  $info["datos"]["amount"]["symbol"] . " " . $info["datos"]["amount"]["amount"],
  "Origen" =>  $info["datos"]["origen"],
  "Receptor" =>  $info["datos"]["myAccount"],

];

if (isset($info["error"])) {
  $data["Error"] = $info["error"];
}

$rendimiento->endTime();
$time = $rendimiento->duration();
$data["Procesado"] = $time;
header("Location: views/OcrResponseView.php?info=" . urlencode(http_build_query($data)));



// Funcion para transformar pdf en imagenes con imagick
function pdfToImages($pdfFilePath) {
  // Directorio temporal para guardar las imágenes
  $outputDir = __DIR__ . '/temp_images/';
  if (!file_exists($outputDir)) {
      mkdir($outputDir, 0777, true);
  }

  // Convierte cada página del PDF en una imagen PNG
  $imageBase = $outputDir . 'page';
  exec("magick convert -density 300 \"$pdfFilePath\" \"$imageBase-%d.png\"");


  // Almacena las rutas de las imágenes generadas
  $imagePaths = [];
  foreach (glob("$outputDir*.png") as $imageFile) {
      $imagePaths[] = $imageFile; // Guarda la ruta de cada imagen
  }

  return $imagePaths; // Devuelve las rutas de las imágenes
}
