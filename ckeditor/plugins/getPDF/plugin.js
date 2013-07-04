CKEDITOR.plugins.add('getPDF',{
icons:'getPDF',init:function(editor){
editor.ui.addButton('getPDF',{
label:"get PDF",command:'getpdf',toolbar:'document'});editor.addCommand('getpdf',{
exec:function(editor){
$.ajax({
type:"POST",url:"ajax/getPDF.php?filename="+filename,data:{
"editor1":editor.getData(),"data":$("#data").html()},success:function(){
window.open('files/tmp/'+filename+'.pdf');}});}});}});
