<?php
include_once(__DIR__."\Rendimiento.php");
require_once(__DIR__."\OCR.php");


$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$rendimiento = new Rendimiento();
$rendimiento->startTime();

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


$ocr = new OCR();
$info = $ocr->extract($ubicacionImagen);
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
header("Location: OCRView.php?info=" . urlencode(http_build_query($data)));




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
