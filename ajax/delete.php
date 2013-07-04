<?php

if(file_exists($_POST['filename'])) {
	unlink('../files/'.$_POST['filename']);
	system('rm -rf ../files/tmp/'.$_POST['filename']);
}

?>
