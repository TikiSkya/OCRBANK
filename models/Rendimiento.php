<?php
require_once(__DIR__."\OCR.php");
class Rendimiento {

    public $startTime;
    public $endTime;
    public $duration;
    public $contador;

    public function __construct() {
        $this->contador = (float)0;
    }

    public function startTime() {
        $this->startTime = microtime(true);
    }

    public function sumCount($sec) {
        $this->contador += $sec;
    }

    public function endTime() {
        $this->endTime = microtime(true);
    }

    public function duration() {
        $this->duration = ($this->endTime - $this->startTime) ;
        return number_format($this->duration,3);
    }


    public function testTimeProcess($amount) {
        $this->startTime();

        $directory = __DIR__."/../test_comprobantes";// Especifica la ruta de la carpeta
        $ocr = new OCR();

        if (is_dir($directory)) {
            $files = scandir($directory); // Obtiene todos los archivos y carpetas
            $filteredFiles = array_values($files); // Reindexar el array
            shuffle($filteredFiles); // Mezclar los archivos aleatoriamente
            $count = 0;
            foreach ($filteredFiles as $file) {
                if ($count >= $amount) {
                    break;
                }

                if ($file !== "." && $file !== "..") { // Ignora . y ..
                    $filePath = $directory . DIRECTORY_SEPARATOR . $file;
                    
                    if (is_file($filePath)) {
                        $info = $ocr->extract($filePath);
                    } 
                    $count += 1;
                }

            }
        } else {
            echo "El directorio no existe.\n";
        }

        $this->endTime();
        $this->sumCount($this->duration());
        return $this->duration();

    }
}