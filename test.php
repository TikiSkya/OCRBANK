<?php
include_once(__DIR__."\models\Rendimiento.php");


/*
 * Test de rendimiento de tiempo con comprobantes de prueba
 * 
 * @amount cantidad de archivos a medir tiempo
 */
function testTime($amount): float {
    $rendimiento = new Rendimiento();
    $rendimiento->testTimeProcess($amount);
    return $rendimiento->contador;
}



echo testTime(5);