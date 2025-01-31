# OCRBank

## Table of Contents

* [OCRBank](#ocrbank)
  * [Información](#información)
  * [Instalación](#installing-ocrbank)
  * [Running Tesseract](#running-tesseract)
  * [Soporte](#soporte)
  * [License](#license)
  * [Dependencies](#dependencies)
  * [Latest Version of README](#latest-version-of-readme)

## Información

Este repositorio contiene prototipo OCR para automatización del Proceso de Validación de Pagos Bancarios en Uruguay Mediante Tecnología de Reconocimiento Óptico de Caracteres (OCR).

Motor OCR utilizado: https://github.com/tesseract-ocr/tesseract

Librerias utilizadas:
Contiene librerias `libtesseract` y `imagemagick`

## Installing OCRBank Windows

Descargamos la versión para nuestra computadora, ya sea de 32 o 64 bits y descargar instalador de tesseract ocr según arquitectura.

La ruta en donde se instaló por defecto es:
`C:\Program Files\Tesseract-OCR`

**Descargar idioma español**
Por defecto, tesseract incluye únicamente el inglés. Para agregar más idiomas vamos al repositorio necesario - [`Repositorio Idiomas`](https://tesseract-ocr.github.io/tessdoc/Data-Files)

bajar hasta encontrar el idioma que dice spa y descárgalo.

Ese archivo vamos a colocarlo en la ruta de instalación de Tesseract OCR (C:\Program Files\Tesseract-OCR) dentro de la carpeta /tessdata.

puedes verificar si la instalación del idioma fue correcto ejecutando ´tesseract --list-langs´ en consola.

**OPCIONAL: libreria imagemagick**

Es necesaria descargar esta libreria en caso de querer procesar archivos de tipo PDF.

https://imagemagick.org/script/download.php


## Soporte

Para mas información o reportar problemas enviar email a tikirivero@gmail.com

## Dependencies

Tesseract uses [Leptonica library](https://github.com/DanBloomberg/leptonica) for opening input images (e.g. not documents like pdf).
[png](https://sourceforge.net/projects/libpng) and
[tiff](http://www.simplesystems.org/libtiff) (for multipage tiff).
