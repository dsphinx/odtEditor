CKEDITOR.plugins.add('close',{icons:'Close',init:function(editor){editor.ui.addButton('Close',{label:editor.lang.close.toolbar,command:'close',toolbar:'document'});editor.addCommand('close',{exec:function(editor){$.ajax({type:"POST",url:"ajax/delete.php",data:{"filename":filename},success:function(){alert(editor.lang.close.success)}});}});}});