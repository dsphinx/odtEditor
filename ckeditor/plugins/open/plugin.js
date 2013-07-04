CKEDITOR.plugins.add( 'Open', {
    icons: 'Open',
    init: function( editor ) {
        editor.ui.addButton( 'Open', {
			label: 'Open File',
			command: 'open',
			toolbar: 'document'
		});
		CKEDITOR.dialog.add( 'open', this.path + 'dialogs/open.js' );
    }
    
});
