<?php
require_once 'vendor/restler.php';
use Luracast\Restler\Restler;

$r = new Restler();
$r->addAPIClass('hoteles');
$r->addAPIClass('services');
$r->setSupportedFormats('JsFormat', 'JsonFormat');
$r->addAuthenticationClass('SimpleAuth');
$r->handle();
