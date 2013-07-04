<?php

file_put_contents("tmp/".$_GET['file']."/print.html",$_POST['editor1'].'<script>window.print();window.close();</script>');
echo "/tmp/".$_GET['file']."/print.html";

?>
