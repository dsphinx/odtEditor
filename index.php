<!DOCTYPE html>
<meta charset="utf-8">
<?php if($_GET['file'] && file_exists('files/'.$_GET['file'])): ?>
	<?php 
		include 'PHPParser.php';
		$obj = new PHPParser('files',$_GET['file'],'files/tmp');
		$return = $obj->meta();
		echo "<title>".$return['title']."</title>";
	?>
	<script src="ckeditor/ckeditor.js"></script>
	<script src="jquery-1.9.js"></script>
	<script>
		CKEDITOR.on('instanceReady', function( evt ) {
			var editor = evt.editor;
			editor.execCommand('maximize');
		});
		var filename = "<?php echo $_GET['file']; ?>";
	</script>
	<form action="save.php?file=<?php echo $_GET['file']; ?>" method="post">
		<textarea cols="80" id="editor1" name="editor1" rows="30">
			<style id="style2" language="text/css" media="all">
				#page {
					width:210mm;
					margin:auto;
					margin-top:0;
					padding:15mm;
					border-left:1px solid black;
					border-right:1px solid black;
				}
				table,td {
					border:1px solid black;
					margin:0px;
					border-collapse:collapse;
					padding:0 10px;
				}
				p {
					font-size:12pt;
					font-style:none;
					font-weight:normal;
				}
				p {
					margin:0px;
				}
				h1 {
					font-weight:bold;
					font-size:16.1pt;
					margin:16pt 0;
					font-style:none;
				}
				h2 {
					font-weight:bold;
					font-size:14pt;
					margin:14pt 0;
					font-style:italic;
				}
				h3 {
					font-weight:bold;
					font-size:14pt;
					margin:14pt 0;
				}
				h4 {
					font-weight:bold;
					font-size:11.8pt;
					margin:11.8pt 0;
					font-style:italic;
				}
				h5 {
					font-weight:bold;
					font-size:10.5pt;
					margin:10.5pt 0;
					font-style:none;
				}
				h6 {
					font-weight:bold;
					font-size:10.5pt;
					margin:10.5pt 0;
				}
				.Contents_20_Heading {
					font-size:16pt;
					font-weight:bold;
				}
			</style>
			<?php
				$cssData = $obj->odt2html();
			?>
		</textarea>
		<data id="data" style="display:hidden"><?php echo $cssData; ?></data>
		<script>
			CKEDITOR.replace( 'editor1', {
				fullPage: true,
				language: 'de',
				defaultLanguage: 'de',
				allowedContent: true,
				minimumChangeMilliseconds: 1000,
				extraPlugins: 'wysiwygarea,close,removeformat,MySave,MyPrint,getOdt,getPDF',
				toolbar: [
					{ name: 'document', items : [ 'Source','Save','DocProps','Preview','Print','GetOdt','getPDF','Open','Close' ] },
					{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
					{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
					{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',
					'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'] },
					{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
					{ name: 'insert', items : [ 'Table','HorizontalRule' ] },'/',
					{ name: 'styles', items : [ 'Styles','Font','FontSize' ] },
					{ name: 'tools', items : [ 'Maximize' ] }]
			});
		</script>
	</form>
<?php else: ?>
	<link href="ckeditor/skins/moono/dialog.css" type="text/css" rel="stylesheet" />
	<body style="text-align:vertical">
		<section class="cke_dialog_body cke_single_page" style="margin:auto; width:400px; height:200px;">
			<header class="cke_dialog_title">Eine Datei hochladen</header>
			<div class="cke_dialog_contents" style="height:80%">
				<label class="cke_required">
					Datei auswählen:
				</label>
				<form action="upload.php" method="post" required="required" enctype="multipart/form-data">
					<input class="cke_dialog_ui_input_file" type="file" name="datei"></input><br>
					<input type="submit"/>
				</form>
			</div>
		</section>
	</body>
<?php endif; ?>
