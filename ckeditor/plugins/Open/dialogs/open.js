CKEDITOR.dialog.add( 'open', function ( editor ) {
    return {
        title: 'Open File',
        minWidth: 400,
        minHeight: 200,

        contents: [
            {
                id: 'tab-basic',
                label: 'Basic Settings',
				elements: [
					{
						type: 'file',
						id: 'file',
						label: 'File',
						validate: CKEDITOR.dialog.validate.notEmpty( "You have to choose a file" )
					}
				]
			}
		],
		onOk: function() {
			
		}
    };
});
