/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

/**
 * @fileOverview Preview plugin.
 */

(function() {
	var pluginPath;

	var previewCmd = { modes:{wysiwyg:1,source:1 },
		canUndo: false,
		readOnly: 1,
		exec: function( editor ) {
			var sHTML,
				config = editor.config,
				baseTag = config.baseHref ? '<base href="' + config.baseHref + '"/>' : '',
				isCustomDomain = CKEDITOR.env.isCustomDomain(),
				eventData;

			if ( config.fullPage ) {
				sHTML = editor.getData().replace( /<head>/, '$&' + baseTag ).replace( /[^>]*(?=<\/title>)/, '$& &mdash; ' + editor.lang.preview.preview );
			} else {
				var bodyHtml = '<body ',
					body = editor.document && editor.document.getBody();

				if ( body ) {
					if ( body.getAttribute( 'id' ) )
						bodyHtml += 'id="' + body.getAttribute( 'id' ) + '" ';
					if ( body.getAttribute( 'class' ) )
						bodyHtml += 'class="' + body.getAttribute( 'class' ) + '" ';
				}

				bodyHtml += '>';

				sHTML = editor.config.docType + '<html dir="' + editor.config.contentsLangDirection + '">' +
					'<head>' +
						baseTag +
						'<title>' + editor.lang.preview.preview + '</title>' +
						CKEDITOR.tools.buildStyleHtml( editor.config.contentsCss ) +
					'</head>' + bodyHtml +
						editor.getData() +
					'</body></html>';
			}

			var iWidth = 640,
				// 800 * 0.8,
				iHeight = 420,
				// 600 * 0.7,
				iLeft = 80; // (800 - 0.8 * 800) /2 = 800 * 0.1.
			try {
				var screen = window.screen;
				iWidth = Math.round( screen.width * 0.8 );
				iHeight = Math.round( screen.height * 0.7 );
				iLeft = Math.round( screen.width * 0.1 );
			} catch ( e ) {}

			// (#9907) Allow data manipulation before preview is displayed.
			// Also don't open the preview window when event cancelled.
			if ( !editor.fire( 'contentPreview', eventData = { dataValue: sHTML } ) )
				return false;

			var sOpenUrl = '';
			if ( isCustomDomain ) {
				window._cke_htmlToLoad = eventData.dataValue;
				sOpenUrl = 'javascript:void( (function(){' +
					'document.open();' +
					'document.domain="' + document.domain + '";' +
					'document.write( window.opener._cke_htmlToLoad );' +
					'document.close();' +
					'window.opener._cke_htmlToLoad = null;' +
					'})() )';
			}

			// With Firefox only, we need to open a special preview page, so
			// anchors will work properly on it. (#9047)
			if ( CKEDITOR.env.gecko ) {
				window._cke_htmlToLoad = eventData.dataValue;
				sOpenUrl = pluginPath + 'print.html';
			}

			var oWindow = window.open( sOpenUrl, null, 'toolbar=yes,location=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=' +
				iWidth + ',height=' + iHeight + ',left=' + iLeft );

			if ( !isCustomDomain && !CKEDITOR.env.gecko ) {
				var doc = oWindow.document;
				doc.open();
				doc.write( eventData.dataValue );
				doc.close();

				// Chrome will need this to show the embedded. (#8016)
				CKEDITOR.env.webkit && setTimeout( function() {
					doc.body.innerHTML += '';
				}, 0 );
			}

			return true;
		}
	};

	var pluginName = 'MyPrint';

	// Register a plugin named "preview".
	CKEDITOR.plugins.add( pluginName, {
		lang: 'de,en',
		icons: 'Print', // %REMOVE_LINE_CORE%
		init: function( editor ) {

			// Preview is not used for the inline creator.
			if ( editor.elementMode == CKEDITOR.ELEMENT_MODE_INLINE )
				return;

			pluginPath = this.path;

			editor.addCommand( pluginName, previewCmd );
			editor.ui.addButton && editor.ui.addButton( 'Print', {
				label: editor.lang.print.toolbar,
				command: pluginName,
				toolbar: 'document,40'
			});
		}
	});
})();