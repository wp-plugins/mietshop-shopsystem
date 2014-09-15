<?php
if (!class_exists('CosmoshopWidgetAdmin')) {
   class CosmoshopWidgetAdmin {

      public function __construct() {
      } // function

      public function Hauptmenue($CosmoshopWidget) {
         if (!current_user_can('manage_options')) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
         } // if

         $hinweis = '';

         if (get_option('cosmoshop-widget-style-file') == "1") {
            $css_dir           = plugin_dir_path( __FILE__ ).'css/';
            $css_file          = "widget-style-user.css";
            
            if (!file_exists($css_dir.$css_file)) {
               $datei = fopen($css_dir.$css_file, "w");
               fwrite($datei, "/* --- Mietshop-Widget User-CSS --- */\n");
               fclose($datei);
               $hinweis = $hinweis."<div>Datei '".$css_file."' wurde angelegt!</div>";
            } // if
         } // if

         $template_liste = array();
         $template_dir = plugin_dir_path( __FILE__ ).'template/';
         $handle = opendir($template_dir);
         while(($file = readdir($handle)) !== false) {
            if(!is_dir($template_dir.$file)) {
               preg_match("/^(.*)_(.*?)\.php/i", $file, $treffer);
               $template = $treffer[2];
               if (!in_array($template, $template_liste)) {
                  array_push($template_liste, $template);
               } // if
            } // if
         } // while

         $select_optionen_template = '';
         foreach ($template_liste as $template) {
            $select_optionen_template .= '<option value="'.$template.'"'.((get_option('cosmoshop-widget-template') == $template) ? ' selected="selected"' : '').'>'.$template.'</option>';
         } // foreach
?>
   <div class=wrap> 
      <div id="icon-options-general" class="icon32"><br /></div>
      <h2><?php echo CosmoshopWidget::titel ?></h2>
      <form action="options.php" method="post" name="cosmoshop-widget">
         <?php settings_fields('cosmoshopwidget-option-group'); ?>
         <?php do_settings_sections('cosmoshopwidget-option-group'); ?>
         <script language="JavaScript" type="text/javascript">
            window.onload = function (){
               CosmoshopWidget_ChangeTexteditorButton(document.getElementById("cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen").checked);
               CosmoshopWidget_ChangeEinbindung(document.getElementById("cosmoshop-widget-einbindung"));
               CosmoshopWidget_ChangeExpertenAnsicht(document.getElementById("cosmoshop-widget-adminkonfiguration-expertenansicht").checked);
            } // function

            function CosmoshopWidget_ChangeTexteditorButton(checked) {
            } // function

            function CosmoshopWidget_ChangeEinbindung(el) {
               var index = el.selectedIndex;
               var value = el.options[index].value;

               if (value == "text") {
                  jQuery("#tr_cosmoshop-widget-caching").show();
                  jQuery("#hilfe_iframe").hide();
                  jQuery("#hilfe_text").show();
               } // if
               if (value == "iframe") {
                  jQuery("#tr_cosmoshop-widget-caching").hide();
                  jQuery("#hilfe_iframe").show();
                  jQuery("#hilfe_text").hide();
               } // if
            } // function

            function CosmoshopWidget_ChangeExpertenAnsicht(checked) {
               var tr_experten = ["sprache", "pfadscript", "script-param-vorschau", "script-param-bestseller", "script-param-highlight", "tinymce-button", "tinymce-platzhalter", "texteditor-button", "texteditor-button-funtkion", "style", "style-file", "template", "cache_del"];
               var i;
               for (i=0; i<tr_experten.length; ++i) {
                  if (checked == true) {
                     jQuery("#tr_cosmoshop-widget-" + tr_experten[i]).show();
                  } else {
                     jQuery("#tr_cosmoshop-widget-" + tr_experten[i]).hide();
                  }
               } // for
            } // function
         </script>
         
         <?php echo $hinweis ?>
         <p><small>(Links zu Hilfen und Erklärungen s. <a href="plugins.php">Plugin-Listing '<?php echo CosmoshopWidget::titel ?>'</a></small></p>

         <table class="form-table">
            <tr>
               <th scope="row">Experten-Ansicht</th>
               <td><input name="cosmoshop-widget-adminkonfiguration-expertenansicht" id="cosmoshop-widget-adminkonfiguration-expertenansicht" type="checkbox" onChange="CosmoshopWidget_ChangeExpertenAnsicht(this.checked)" value="1"<?php echo (get_option('cosmoshop-widget-adminkonfiguration-expertenansicht') == 1 ? ' checked' : '') ?>></td>
            </tr>
            <tr>
               <th scope="row">Shop-URL</th>
               <td><input name="cosmoshop-widget-shopurl" type="text" value="<?php echo get_option('cosmoshop-widget-shopurl') ?>" class="regular-text" style="width: 550px;" /><br>
                   <small>(z.B. "http://www.mietshop.de")</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-sprache">
               <th scope="row">Widget-Sprache</th>
               <td><input name="cosmoshop-widget-sprache" type="text" value="<?php echo get_option('cosmoshop-widget-sprache') ?>" class="regular-text" style="width: 550px;" /><br>
                   <small>(z.B. "de")</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-pfadscript">
               <th scope="row">Pfad zum Widget-Skript</th>
               <td><input name="cosmoshop-widget-script-pfad" type="text" value="<?php echo get_option('cosmoshop-widget-script-pfad') ?>" class="regular-text" style="width: 550px;" /><br>
                   <small>(z.B. "/cgi-bin/interfaces/widgets/widget.cgi")</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-script-param-vorschau">
               <th>Widget für Artikelvorschau</th>
               <td><input name="cosmoshop-widget-script-param-vorschau" type="text" value="<?php echo get_option('cosmoshop-widget-script-param-vorschau') ?>" class="regular-text" /><br>
                   <small>(z.B. "ArticlePreview")</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-script-param-bestseller">
               <th>Widget für Bestseller</th>
               <td><input name="cosmoshop-widget-script-param-bestseller" type="text" value="<?php echo get_option('cosmoshop-widget-script-param-bestseller') ?>" class="regular-text" /><br>
                   <small>(z.B. "Bestseller")</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-script-param-highlight">
               <th>Widget für Highlight</th>
               <td><input name="cosmoshop-widget-script-param-highlight" type="text" value="<?php echo get_option('cosmoshop-widget-script-param-highlight') ?>" class="regular-text" /><br>
                   <small>(z.B. "Highlight")</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-tinymce-button">
               <th scope="row">Button in WYSIWYG-Editor anzeigen</th>
               <td><input name="cosmoshop-widget-adminkonfiguration-tinymce-button-anzeigen" type="checkbox" value="1"<?php echo (get_option('cosmoshop-widget-adminkonfiguration-tinymce-button-anzeigen') == 1 ? ' checked' : '') ?>></td>
            </tr>
            <tr id="tr_cosmoshop-widget-tinymce-platzhalter">
               <th scope="row">Platzhalter in WYSIWYG-Editor anzeigen</th>
               <td><input name="cosmoshop-widget-adminkonfiguration-tinymce-platzhalter-anzeigen" type="checkbox" value="1"<?php echo (get_option('cosmoshop-widget-adminkonfiguration-tinymce-platzhalter-anzeigen') == 1 ? ' checked' : '') ?>></td>
            </tr>
            <tr id="tr_cosmoshop-widget-texteditor-button">
               <th scope="row">Button in Text-Editor anzeigen</th>
               <td><input name="cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen" id="cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen" type="checkbox" onChange="CosmoshopWidget_ChangeTexteditorButton(this.checked)" value="1"<?php echo (get_option('cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen') == 1 ? ' checked' : '') ?>></td>
            </tr>
            <tr id="tr_cosmoshop-widget-texteditor-button-funtkion">
               <th scope="row">Funktion für Button im Text-Editor</th>
               <td><input name="cosmoshop-widget-adminkonfiguration-texteditor-button-funktion" type="text" value="<?php echo get_option('cosmoshop-widget-adminkonfiguration-texteditor-button-funktion') ?>" class="regular-text" style="width: 550px;" /><br>
                   <small>(z.B. "[<?php echo CosmoshopWidget::shortcode ?> widget=artikelvorschau artnum=...]", "[<?php echo CosmoshopWidget::shortcode ?> widget=bestseller]", "[<?php echo CosmoshopWidget::shortcode ?> widget=highlight]", "[<?php echo CosmoshopWidget::shortcode ?> widget=artikelvorschau|bestseller|highlight artnum=... class=alignleft|alignright]")</small></td>
            </tr>
            <tr>
               <th scope="row">Einbindungs-Art</th>
               <td><select name="cosmoshop-widget-einbindung" id="cosmoshop-widget-einbindung" style="width:200px;" onchange="CosmoshopWidget_ChangeEinbindung(this);">
                      <option value="iframe"<?php echo (get_option('cosmoshop-widget-einbindung') == "iframe") ? ' selected="selected"' : ''; ?>>iFrame</option>
                      <option value="text"<?php echo (get_option('cosmoshop-widget-einbindung') == "text") ? ' selected="selected"' : ''; ?>>direkt im Text</option>
                   </select>
                   <div id="hilfe_iframe">
                      <small>Die Widgets werden per IFrame in Wordpress in die Texte eingebunden.<br>
                        <b>Vorteile:</b> immer aktuelle Daten; keine Probleme mit Wordpress-Caching-Funktionen; die Wordpress-Seite kann unabhängig von den Widgets aufgebaut werden<br>
                        <b>Nachteile:</b> nicht optimal in Bezug auf SEO
                      </small>
                     </div>
                   <div id="hilfe_text">
                     <small>Die Widgets werden direkt in die Texte eingebunden.<br>
                        <b>Vorteile:</b> bessere Lösung in Bezug auf SEO<br>
                        <b>Nachteile:</b> ggf. Probleme mit Wordpress-Caching-Funktionen; die Wordpress-Seite kann erst nach dem vollständigem laden aller Widgets aufgebaut werden
                        </ul>
                     </small>
                  </div>
               </td>
            </tr>
            <tr id="tr_cosmoshop-widget-caching">
               <th>Caching-Zeit</th>
               <td><input name="cosmoshop-widget-cachingzeit" type="text" value="<?php echo get_option('cosmoshop-widget-cachingzeit') ?>" class="regular-text" /><br>
                   <small>(Zwischenspeichern der Widget-Daten in Sekunden; z.B. 900 für 15min, 0 bzw. leer für 'kein Caching')</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-cache_del">
               <th>Cache leeren</th>
               <td><input name="cosmoshop-widget-cache_del" type="checkbox" value="1"><br>
                   <small>(Cache beim speichern der Optionen leeren)</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-template">
               <th>Template</th>
               <td><select name="cosmoshop-widget-template" id="cosmoshop-widget-template" style="width:200px;">
                      <?php echo $select_optionen_template ?>
                   </select><br>
                   <small>(Template-Set, falls eigenen Templates erstellt worden sind)</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-style">
               <th>CSS-Style</th>
               <td><input name="cosmoshop-widget-style" type="text" value="<?php echo get_option('cosmoshop-widget-style') ?>" class="regular-text" /><br>
                   <small>(wird in den Wrapper-Div-Container eingefügt, kann für eigenen CSS-Anpassungen genutzt werden)</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-style-file">
               <th>CSS-Style-Datei nutzen</th>
               <td><input name="cosmoshop-widget-style-file" type="checkbox" value="1"<?php echo (get_option('cosmoshop-widget-style-file') == 1 ? ' checked' : '') ?>><br>
                   <small>(Style-Datei widget-style-user.css für eigenen CSS-Anpassungen nutzen)</small></td>
            </tr>
            <tr id="tr_cosmoshop-widget-preishinweis">
               <th>Preis-Hinweis</th>
               <td><input name="cosmoshop-widget-preishinweis" type="text" value="<?php echo get_option('cosmoshop-widget-preishinweis') ?>" class="regular-text" /><br>
                   <small>(<font color="red">Wichtig:</font> Preishinweis; auch in den Theme-Widgets beachten!)</small></td>
            </tr>
         </table>
         <input type="hidden" name="action" value="update" />
         <input type="hidden" name="page_options" value="cosmoshop-widget-adminkonfiguration-expertenansicht,cosmoshop-widget-shopurl,cosmoshop-widget-script-pfad,cosmoshop-widget-script-param-vorschau,cosmoshop-widget-script-param-bestseller,cosmoshop-widget-script-param-highlight,cosmoshop-widget-adminkonfiguration-tinymce-button-anzeigen,cosmoshop-widget-adminkonfiguration-tinymce-platzhalter-anzeigen,cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen,cosmoshop-widget-adminkonfiguration-texteditor-button-funktion,cosmoshop-widget-einbindung,cosmoshop-widget-cachingzeit" />
         <p class="submit"><input type="submit" id="save" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
      </form>
   </div>
<?php
      } // function
   } // class
} // if
