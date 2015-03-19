<script type="text/javascript" src="js/plupload.js"></script>
<script type="text/javascript" src="js/plupload.flash.js"></script>
<script type="text/javascript" src="scripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		tinyMCE.init({ 
	        // General options 
	        mode : "textareas", 

			// Location of TinyMCE script
			script_url : 'scripts/tiny_mce/tiny_mce.js',
			editor_selector :"tinyMCE",

			// General options
			theme : "advanced",
			theme_advanced_statusbar_location : "bottom",
			spellchecker_rpc_url: 'scripts/tiny_mce/plugins/spellchecker/rpc.php',
			
			plugins : "phpimage,pre,autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
			theme_advanced_buttons1 : "phpimage,spellchecker,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,formatselect,fontselect,fontsizeselect,|,bullist,numlist,|,|,link,unlink,anchor,|,insertdate,inserttime,|,forecolor,backcolor",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_resizing : true,
			max_chars : "10", 
			max_chars_indicator : "lengthBox", 
			
			 theme_advanced_path : false,
		     setup : function(ed) {
		     	
		        ed.onInit.add(function(ed)
		        {
		            ed.getDoc().body.style.fontSize = '12pt';
		        });
		          ed.onKeyUp.add(function(ed, e) {    
		               var strip = (tinyMCE.activeEditor.getContent()).replace(/(<([^>]+)>)/ig,"");
		               var text = strip.split(' ').length + " Words, " +  strip.length + " Characters";
				        tinymce.DOM.setHTML(tinymce.DOM.get(tinyMCE.activeEditor.id + '_path_row'), text);  
			    });
		     },

			// Example content CSS (should be your site CSS)
			content_css : "css/content.css",

			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",

			// Replace values for the template plugin
			template_replace_values : {
				username : "Some User",
				staffid : "991234"
			}
		});
	});
</script>
