$(document).ready(function () {
	//uploader
	if (window.location.href.indexOf('?') > 0) {
		var char = '&';
	}
	else {
		var char = '?';
	}
	
	$('#imagesUploader').pluploadQueue({
		runtimes: 'html5, html4, flash',
//		flash_swf_url : 'plupload.flash.swf',
		filters: [
		          {title : "Image files", extensions : "jpg,gif,png"}
		],
		url: window.location.href+char+'do=gallery-upload',
		
		preinit : {
			UploadComplete: function(up, file) {
				window.location.reload();
			}
		}
	});
	
	$(".grid-datepicker").live('focus', function () {
		$(this).datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "HH:mm"
		});
	});
});

function loadTinyMCE (path) {	
	tinyMCE.baseURL = path+"/js/tinymce";
	
	tinymce.init({
	    selector: "textarea",
	    language: "cs",
	    width: "100%",
	    height: "300",
	    autoresize_min_height: "300",
	    autoresize_max_height: "1000",
	    plugins: "autoresize, table, link, image, code, paste, autolink, media",
	    image_advtab: true,
	    style_formats: [
						{title: 'Odstavec', block: 'p'},
						{title: 'Nadpis 1', block: 'h1'},
						{title: 'Nadpis 2', block: 'h2'},
						{title: 'Nadpis 3', block: 'h3'},
						{title: 'Nadpis 4', block: 'h4'},
						{title: 'Nadpis 5', block: 'h5'},
						{title: 'Vertikální zarovnání', selector: 'td', styles: {'vertical-align': 'top'}}
	                    ],
	    menubar: "format table",
	    toolbar: "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | removeformat | code",
	    relative_urls: false,
	    remove_script_host: false,	    
//	    convert_urls: false,
	    entity_encoding: 'named',
	    paste_as_text: true,
	    file_browser_callback : function (field_name, url, type, win) {
	    	var url = window.location.toString();
			var pages = url.split('/');
			var browser = '', stop;
			
			for (var i=0; i<pages.length; i++) {
				if (pages[i] != 'homepage') {
					if (stop != 1) {
						browser = browser+pages[i]+'/';
					}
				}
				else {
					stop = 1;
				}  
			}
			
			tinymce.activeEditor.windowManager.open({
			    file : browser+'browser/'+type,
			    title : 'File manager',
			    width : 960,
			    height : 600,
			    resizable : "no",
			    close_previous : "no"
			});
			return false;
	    }
	});
}

function addUrl (url, alt) {
	var altInput = $(top.document).find("div[role=dialog] input:eq(1)");

	$(top.document).find("div[role=dialog] input:first").val(url);
	if (altInput.val() == '') {
		altInput.val(alt);
	}
	
	top.tinymce.activeEditor.windowManager.close();
}