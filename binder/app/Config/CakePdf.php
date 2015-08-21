<?php
/**
 * CakePdf.php
 */
$config = array(
    'engine' => 'CakePdf.WkHtmlToPdf',
    'options' => array(
        'print-media-type' => false,
        'outline' => true,
        'dpi' => 96
    ),
    'margin' => array(
        'bottom' => 15,
        'left' => 50,
        'right' => 30,
        'top' => 45
    ),
    'orientation' => 'landscape',
    'download' => true
);
