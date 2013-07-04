<?php

include "../PHPParser.php";

$obj = new PHPParser('../files',$_GET['file'],'../files/tmp');

$content = '<?xml version="1.0" encoding="utf-8"?><all>'.str_replace('&','&amp;',$_POST['editor1']).'</all>';

$obj->html2odt($content);

?>
