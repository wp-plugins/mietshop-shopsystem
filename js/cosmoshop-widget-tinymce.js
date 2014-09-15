(function(){
   // creates the plugin
   tinymce.create('tinymce.plugins.cosmoshopwidget', {
      init : function(ed, url) {
         var t = this;

         // Button anzeigen
         if (global_cosmoshopwidget.tinymce_button) {
            ed.addButton('cosmoshopwidget', {
               title : global_cosmoshopwidget.titel + ' Shortcode',
               //image : url + '/../pic/wysiwyg_cosmoshopwidget_icon.png',
               icon: 'icon wysiwyg-cosmoshopwidget-icon',
               onclick : function() {
                  var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
                  W = W - 80;
                  H = H - 84;
                  tb_show(global_cosmoshopwidget.titel + ' Shortcode', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=cosmoshopwidget-form' );
               }
            });
         } // if

         // Grafiken als Platzhalter anstelle des Shortcodes anzeigen
         if (global_cosmoshopwidget.tinymce_platzhalter) {
            //replace shortcode before editor content set

            ed.on('BeforeSetcontent', function(o) {
               o.content = t._prepareContentView_cosmoshopwidget(o.content, url);
            });

            //replace shortcode as its inserted into editor (which uses the exec command)
            ed.onExecCommand.add(function(ed, cmd) {
               if (cmd ==='mceInsertContent'){
                  tinyMCE.activeEditor.setContent(t._prepareContentView_cosmoshopwidget(tinyMCE.activeEditor.getContent(), url) );
               }
            });

            //replace the image back to shortcode on save
            ed.onPostProcess.add(function(ed, o) {
               if (o.get)
                  o.content = t._prepareContentSave_cosmoshopwidget(o.content);
            });
         } // if
      },

      createControl : function(n, cm) {
         return null;
      },

      _prepareContentView_cosmoshopwidget : function(co, url) {
         return co.replace(/\[mietshop-widget([^\]]*)\]/g, function(a,b){
            var shortcode  = global_cosmoshopwidget.shortcode; 
            var titel      = global_cosmoshopwidget.titel;

            var css        = "";
            var pic        = '';
            var text       = '';
            var widget     = '';

            if (b.match(/artikelvorschau/i)) { widget = 'artikelvorschau';    text = 'Artikel-Vorschau';    pic = 'wysiwyg_cosmoshopwidget_artikelvorschau.png' };
            if (b.match(/bestseller/i))      { widget = 'bestseller';         text = 'Artikel-Bestseller';  pic = 'wysiwyg_cosmoshopwidget_bestseller.png' };
            if (b.match(/highlight/i))       { widget = 'highlight';          text = 'Artikel-Highlight';   pic = 'wysiwyg_cosmoshopwidget_highlight.png' };
            
            if (b.match(/alignleft/))        { css = ' alignleft'; }; 
            if (b.match(/alignright/))       { css = ' alignright'; }; 

            var n = b.match(/artnum=[^\s]+\s?/);
            if (n) {
               widget = widget + ' ' + n[0];
            } // if

            var out = '<img class="mietshopwidget' + css + '" src="' + url + '/../pic/' + pic + '" title="widget=' + widget + '" alt="' +  titel + ': ' + text + '"/>'
            return out;
         });
      },
      
      _prepareContentSave_cosmoshopwidget : function(co) {
         //return co;
         function getAttr(s, n) {
            n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
            return n ? tinymce.DOM.decode(n[1]) : '';
         };

         return co.replace(/(<img[^>]+>)/g, function(a,im) {
            var cls = getAttr(im, 'class');
            if ( cls.indexOf('mietshopwidget') != -1 ) {
               var css_class = "";
               if (cls.indexOf('alignleft')  != -1 ) { css_class = " class=alignleft"; }
               if (cls.indexOf('alignright') != -1 ) { css_class = " class=alignright"; }
               var out =  '<p>[' + global_cosmoshopwidget.shortcode + ' ' + tinymce.trim(getAttr(im, 'title')) + css_class + ']</p>';
               return out;
            }
            return a;
         });
      },
    });

    // registers the plugin. DON'T MISS THIS STEP!!!
    tinymce.PluginManager.add('cosmoshopwidget', tinymce.plugins.cosmoshopwidget);

    // executes this when the DOM is ready
    jQuery(function(){
        // creates a form to be displayed everytime the button is clicked
        // you should achieve this using AJAX instead of direct html code like this
        var form = jQuery('<div id="cosmoshopwidget-form">\
        <table id="cosmoshopwidget-table" class="form-table">\
           <tr>\
              <th><label for="cosmoshopwidget-widget">Widget</label></th>\
              <td><select name="widget" id="cosmoshopwidget-widget" style="width:200px;" onchange="admin_change_widget_tinymce();">\
                     <option value="highlight">Highlight</option>\
                     <option value="bestseller">Bestseller</option>\
                     <option value="artikelvorschau">Artikelvorschau</option>\
                  </select></td>\
           </tr>\
           <tr id="tr-artnum" style="display: none;">\
              <th><label for="cosmoshopwidget-artnum">Artikelnummer</label></th>\
              <td><input type="text" name="artnum" id="cosmoshopwidget-artnum" value="" onblur="admin_change_widget_tinymce_artikelvorschau();"/>\
              &nbsp;&nbsp;&nbsp;<input type="button" id="update" class="button-primary" value="aktualisieren" onclick="return false;" />\
              <br />\
              <small>Artikelnummer für die Artikel-Vorschau</small></td>\
           </tr>\
           <tr>\
              <th><label for="cosmoshopwidget-class">Ausrichtung</label></th>\
              <td><select name="class" id="cosmoshopwidget-class" style="width:200px;">\
                     <option value="">keine</option>\
                     <option value="alignleft">Links</option>\
                     <option value="alignright">Rechts</option>\
                  </select><br />\
              <small>CSS-Klasse für das Layout / Ausrichtung</small></td>\
           </tr>\
        </table>\
        <p class="submit">\
            <input type="button" id="cosmoshopwidget-submit" class="button-primary" value="Widget einf&uuml;gen" name="submit" />\
        </p>\
        <div class="vorschau">\
         <div id="vorschau_artikelvorschau" style="display: none;">\
          <p>Vorschau für <b>Artikelvorschau</b><sup>*</sup></p>\
            <iframe src="" class="admin-cosmoshop-widget-vorschau-widget" id="cosmoshop-widget-vorschau-widget"><p>Ihr Browser kann leider keine eingebetteten Frames anzeigen</p></iframe>\
         </div>\
         <div id="vorschau_bestseller" style="display: none;">\
          <p>Vorschau für <b>Bestseller</b><sup>*</sup></p>\
          <div>\
          <iframe src="' + global_cosmoshopwidget.url + '?ls=' + global_cosmoshopwidget.sprache + '&widget=Bestseller" class="admin-cosmoshop-widget-besteller-widget" id="cosmoshop-widget-bestseller"><p>Ihr Browser kann leider keine eingebetteten Frames anzeigen</p></iframe>\
          </div>\
         </div>\
         <div id="vorschau_highlight" style="">\
          <p>Vorschau für <b>Highlight</b><sup>*</sup></p>\
          <div>\
          <iframe src="' + global_cosmoshopwidget.url + '?ls=' + global_cosmoshopwidget.sprache + '&widget=Highlight" class="admin-cosmoshop-widget-highlight-widget" id="cosmoshop-widget-highlight"><p>Ihr Browser kann leider keine eingebetteten Frames anzeigen</p></iframe>\
          </div>\
         </div>\
         <div class="hinweis">\
            <small><sup>*</sup> die Vorschau erfolgt <u>hier immer</u> im iFrame, im WYSIWYG-Editor wird eine Platzhalter-Grafik eingefügt</small>\
         </div>\
         <script language="JavaScript" type="text/javascript">\
            function admin_change_widget_tinymce() {\
               if (document.getElementById("cosmoshopwidget-widget").value == "artikelvorschau") {\
                  document.getElementById("tr-artnum").style="display:table-row;";\
                  document.getElementById("vorschau_artikelvorschau").style="display:inline;";\
                  document.getElementById("vorschau_bestseller").style="display:none;";\
                  document.getElementById("vorschau_highlight").style="display:none;";\
                  admin_change_widget_tinymce_artikelvorschau();\
               }\
               if (document.getElementById("cosmoshopwidget-widget").value == "bestseller") {\
                  document.getElementById("tr-artnum").style="display:none;";\
                  document.getElementById("vorschau_artikelvorschau").style="display:none;";\
                  document.getElementById("vorschau_bestseller").style="display:inline;";\
                  document.getElementById("vorschau_highlight").style="display:none;";\
               }\
               if (document.getElementById("cosmoshopwidget-widget").value == "highlight") {\
                  document.getElementById("tr-artnum").style="display:none;";\
                  document.getElementById("vorschau_artikelvorschau").style="display:none;";\
                  document.getElementById("vorschau_bestseller").style="display:none;";\
                  document.getElementById("vorschau_highlight").style="display:inline;";\
               }\
            }\
            function admin_change_widget_tinymce_artikelvorschau() {\
               script = global_cosmoshopwidget.url;\
               script = script + "?ls=global_cosmoshopwidget.sprache";\
               script = script + "&widget=ArticlePreview";\
               script = script + "&artnum=" + document.getElementById("cosmoshopwidget-artnum").value;\
               document.getElementById("cosmoshop-widget-vorschau-widget").src = script;\
            }\
         </script>\
      </div>');
        
        var table = form.find('table');
        form.appendTo('body').hide();
        
        // handles the click event of the submit button
        form.find('#cosmoshopwidget-submit').click(function(){
            // defines the options and their default values
            // again, this is not the most elegant way to do this
            // but well, this gets the job done nonetheless
            var options = { 
                'widget'      : '',
                'artnum'      : '',
                'class'       : ''
                };
            var shortcode = '[' + global_cosmoshopwidget.shortcode;
            
            for( var index in options) {
                var value = table.find('#cosmoshopwidget-' + index).val();
                
                // attaches the attribute to the shortcode only if it's different from the default value
                if ( value !== options[index] )
                    shortcode += ' ' + index + '=' + value + '';
            }
            
            shortcode += ']';
            
            // inserts the shortcode into the active editor
            tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
            
            // closes Thickbox
            tb_remove();
        });
    });
})()
