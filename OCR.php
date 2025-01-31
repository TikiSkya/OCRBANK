<?php
include_once(__DIR__."\Rendimiento.php");



class OCR {

  public function __construct() {
    return;
  }

  public function extract($filePath) {
    $rendimiento = new Rendimiento();
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $oldFilePath = $filePath;
    // Verificar si es PDF y procesarlo
    if ($extension === 'pdf') {
      $filePath = $this->pdfToImages($filePath)[0];
    } 

    // Verificar si es una imagen (JPG, JPEG, PNG) 
    elseif (!in_array($extension, ['jpg', 'jpeg', 'png'])) { 
      return ["error" => "Formato no permitido"];
    } 

    // Ejecución de TESSERACT OCR
    $comando = "tesseract " . escapeshellarg($filePath) . " stdout -l spa -c debug_file=/dev/null";
    exec($comando, $text, $codigoSalida);


    if ($codigoSalida !== 0) {
      $this->logFile("Archivo: ".$filePath." No procesado - ".$codigoSalida,"error.txt");
    } else {
      $text = join("\n", $text);
      $text = str_replace('º', 'o', $text);
      $values = $this->getValues($text);

      if (isset($values["error"])) {
        $this->logFile("No Procesado: ".$oldFilePath.json_encode($this->getValues($text)) ."\n".$text ,"error.txt" );
        return [
              "error" => "No se pudo obtener todos los datos",
              "datos" => $this->getValues($text)
        ];
      } else {
        $this->logFile("Procesado: ". $oldFilePath.json_encode($this->getValues($text)),"processed.txt" );
        return [
          "datos" => $this->getValues($text)
        ];
      }
    }
  }

  function logFile($message, $filePath = 'log.txt') {
    // Añadir la fecha y hora al mensaje
    $date = date('Y-m-d H:i:s');
    $logMessage = "[$date] $message" . PHP_EOL;

    // Escribir el mensaje en el archivo de log
    file_put_contents($filePath, $logMessage, FILE_APPEND);
  }


  function getValues($text) {
    $origen = ["Cuenta de origen CA USD","de la Caja de Ahorro en pesos No.","de la Caja de Ahorro en dólares No.","Cuenta de origen CA UYU","de\nItaú\ncuenta","Origen CC en Dolares","Cuenta de origen CC USD"," de cuenta:"];
    $transaction = ["Número de la operación","Tu número de referencia es:","Cuenta corriente (USD]","Número de cuenta:","Número de transacción:","Nro. de Referencia:","ld Lista","Detalles de la transacción","Referencia:","número de transacción","número de transacción"];
    $pay = ["Importe a acrecitar","Monto","Total en ","Monto acreditado:","Debitamos","Monto total","Importe giro:","Moneda y monto",'Importe a USD","importe transferido uU$S',"importe a acreditar","importe transferido","importe transferido"];
    $bank = ["Banco","Banco"];
    $moneda =  ["Moneda"];
    $myAccount = ["Cuenta de destino","Número de Lote:","NETUVY SRL","en la Cuenta Corriente en dólares No.","Banco Itaú de Uruguay\ncuenta","NetUY","Cuenta destino CC USD","No de Cuenta:"];

    $getTransaction = $this->search($text, $transaction);
    $getOrigen = $this->search($text, $origen);
    $getMyAccount = $this->search($text, $myAccount);
    $getAmount = $this->searchAmount($text, $pay);
    $getBank = $this->searchBank($text, $bank);

    $values =[
      "bank" => $getBank,
      "transactionId" => $getTransaction,
      "amount" => $getAmount,
      "origen" => $getOrigen,
      "myAccount" => $getMyAccount
    ]; 

    foreach ($values as $key => $value) {
      if ($value === false) {
        if ($key != "transactionId" && $key != "origen") {
          $values["error"] = true;
          break;
        }
      }
    }
    return $values;
  }

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


  function search($haystack, $needles, $offset = 0) {
    foreach($needles as $needle) {
      $pos = strpos($haystack, $needle, $offset);
      if ($pos !== false) {
          // Usamos una expresión regular para capturar la palabra después del término encontrado
          $pattern = '/' . preg_quote($needle, '/') . '\s+(\S+)/';
          if (preg_match($pattern, $haystack, $matches, 0, $pos)) {
              return $matches[1]; // Retorna la palabra encontrada después del término
          }
      }
    }
    return false; // Ningún término encontrado
  }

  function obtenerPrimeraPalabra($texto) {

    $lineas = explode("\n", $texto);
    $primeraLinea = $lineas[0];
    return $primeraLinea ;
    // Reemplazar cualquier tipo de espacio en blanco (incluyendo saltos de línea) con un solo espacio
    $textoLimpio = preg_replace('/\s+/', ' ', trim($texto));

    // Usar expresión regular para obtener la primera palabra
    if (preg_match('/^\S+/', $textoLimpio, $coincidencia)) {
        return $coincidencia[0];
    }
    return null; // Retorna null si no hay ninguna palabra
  }

  function searchBank($haystack, $needles, $offset = 0) {
    $firstWord = $this->obtenerPrimeraPalabra($haystack);
    foreach (["itaú","santander","scotiabank","bbva"] as $bank) {
      
      if (preg_match("/\b$bank\b/", strtolower($firstWord), $coincidencia)) {
          return $coincidencia[0];

      }
    }

    foreach($needles as $needle) {
      $pos = strpos($haystack, $needle, $offset);
      if ($pos !== false) {
          // Usamos una expresión regular para capturar la palabra después del término encontrado
          $pattern = '/' . preg_quote($needle, '/') . '\s+(\S+)/';
          if (preg_match($pattern, $haystack, $matches, 0, $pos)) {

            return $matches[1]; // Retorna la palabra encontrada después del término
          }
      }
    }



    return false; // Ningún término encontrado
  }

  function searchAmount($haystack, $needles) {
    // Convertir los needles en un patrón de búsqueda
    $pattern = '/' . implode('|', array_map('preg_quote', $needles)) . '/';

    // Buscar la posición de los needles
    if (preg_match($pattern, $haystack, $matches, PREG_OFFSET_CAPTURE)) {
        $pos = $matches[0][1]; // La posición del primer needle encontrado
        $stringAfterNeedle = substr($haystack, $pos + strlen($matches[0][0])); // Subcadena después del needle

        // Usar regex para capturar el símbolo y la cantidad
        // Asegurarse de que uU$S se capture correctamente
        if (preg_match('/\s*(uU\$S|U\$S|\$|USD|uss|USD:|UYU)\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/i', $stringAfterNeedle, $moneyMatches)) {
          // Captura el símbolo y la cantidad
          $symbol = $moneyMatches[1];
          $amount = $moneyMatches[2]; // La cantidad sin el símbolo
          
          // Si el símbolo es uU$S, U$S o USD, establecer el símbolo como "USD"
          if (in_array(strtoupper($symbol), ['UU$S','U$S' ,'USD','USD:']))  {
              $symbol = 'USD';
          }
          if (in_array(strtoupper($symbol), ['UYU',"$"]))  {
            $symbol = 'UYU';
        }


          return [
              'symbol' => $symbol, // Regresa el símbolo ajustado
              'amount' => $amount
          ];
      }
    }

    return false; // Retorna falso si no se encuentra nada
  }

}