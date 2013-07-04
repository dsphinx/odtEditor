<?php

move_uploaded_file($_FILES['datei']['tmp_name'], 'files/'.time().$_FILES['datei']['name'] );
header("LOCATION: index.php?file=".time().$_FILES['datei']['name']);

?>
