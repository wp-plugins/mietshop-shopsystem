<?php
if (!class_exists('CosmoshopWidget')) {
   class CosmoshopWidget {
      protected static $classobj;
      const titel     = 'Mietshop Shopsystem';
      const shortcode = 'mietshop-widget';

      var $widget_id = 0;

      public function CosmoshopWidget() {
         register_activation_hook(CosmoshopWidget_FILE, array('CosmoshopWidget',   'on_activation'));
         register_deactivation_hook(CosmoshopWidget_FILE, array('CosmoshopWidget', 'on_deactivation'));
         register_uninstall_hook(CosmoshopWidget_FILE, array('CosmoshopWidget',    'on_uninstall'));

         // CSS und JS für Frontend aktivieren
         add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

         // Cosmoshop-Widget für Content aktivieren
         add_shortcode(CosmoshopWidget::shortcode, array($this, 'shortcode'));

         // Cosmoshop-Widget für Widgets aktivieren
         add_action('widgets_init', array($this, 'widget_init'));

         if (is_admin()) {
            // Plugin-Optionen-Link in Menü einfügen
            add_action('admin_menu', array($this, 'admin_add_options_page_link'));

            // Plugin-Optionen registrieren
            add_action('admin_init', array($this, 'admin_register_settings'));

            // Button für Text-Editor einfügen
            if (get_option('cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen') == 1)
               add_action('admin_print_footer_scripts', array($this, 'admin_texteditor_add_quicktags'));

            // Plugin-Hilfe einfügen
            add_action('contextual_help', array($this, 'admin_contextual_help'), 10, 3 );

            // CSS, JS und JS-Var für Admin/TinyMCE aktivieren
            add_action('admin_head', array($this, 'admin_custom_admin_head_tinymce'));

            // Button für TinyMCE einfügen
            add_filter('mce_buttons', array($this, 'admin_tinymce_add_button'));
            add_filter('mce_external_plugins', array($this, 'admin_tinymce_js'));

            // Link 'Einstellungen' im Plugin-Listing einfügen
            add_filter('plugin_action_links', array($this, 'admin_plugin_action_links' ), 10, 2);

            // weitere Links im Plugin-Listing einfügen
            add_filter('plugin_row_meta', array($this, 'admin_plugin_meta_link'), 10, 2);
         } // if
      } // function

      function widget_init() {
         // Cosmoshop-Widget für Widgets aktivieren
         require_once('cosmoshop-widget-widget.php');
         register_widget('cosmoshopwidget_widget');
         register_widget('cosmoshopwidget_widget_text');
      } // function

      function enqueue_scripts() {
         // CSS und JS für Frontend aktivieren
         $plugin_directory = plugin_basename(__FILE__); 
         wp_enqueue_style('cosmoshopwidget', plugins_url('css/widget-style.css', __FILE__), array(), '1.0.0', 'all');
         if (get_option('cosmoshop-widget-style-file') == "1") {
            wp_enqueue_style('cosmoshopwidgetuser', plugins_url('css/widget-style-user.css', __FILE__), array(), '1.0.0', 'all');
         } // if

         wp_enqueue_script('jquery');
      } // function

      public function shortcode($atts) {
         // Cosmoshop-Widget für Content aktivieren
         $this->widget_id += 1;
         $widget_id = $this->widget_id;
         extract(shortcode_atts( array(
            'artnum' => '',
            'widget' => '',
            'class'  => 'alignnone',
         ), $atts));

         $widget                 = strtolower($widget);

         $widgets = array('artikelvorschau', 'bestseller', 'highlight');
         if (!in_array($widget, $widgets)) return ""; 

         $script                 = get_option('cosmoshop-widget-shopurl').get_option('cosmoshop-widget-script-pfad');
         $sprache                = get_option('cosmoshop-widget-sprache');
         $widget_style           = get_option('cosmoshop-widget-style');
         $widget_class           = $class;

         $template_typ           = get_option('cosmoshop-widget-einbindung');
         $template_dir           = plugin_dir_path( __FILE__ ).'template/';
         $template_file_name     = $template_typ.'_content_'.$widget.'_'.get_option('cosmoshop-widget-template');
         $template_file          = $template_dir.$template_file_name.'.php';

         if (!is_readable($template_file)) {
            $template_file_name  = $template_typ.'_content_'.$widget.'_default';
            $template_file       = $template_dir.$template_file_name.'.php';
         } // if

         if (!is_readable($template_file)) return "";

         $shop_widget_html = ''; // wird im Template benötigt
         if (get_option('cosmoshop-widget-einbindung') == "text") {
            $url = $script;
            if ($widget == "artikelvorschau")   $url .=  '?ls='.$sprache.'&widget='.get_option('cosmoshop-widget-script-param-vorschau').'&artnum='.$artnum;
            if ($widget == "bestseller")        $url .=  '?ls='.$sprache.'&widget='.get_option('cosmoshop-widget-script-param-bestseller');
            if ($widget == "highlight")         $url .=  '?ls='.$sprache.'&widget='.get_option('cosmoshop-widget-script-param-highlight');
            
            $id = $widget.'_'.$sprache;
            if ($widget == "artikelvorschau") $id .= '_'.$artnum;

            $shop_widget_html = $this->get_cosmoshopwidget_html($url, $id);
         } // if

         $parameter = ""; // wird im Template benötigt
         if (get_option('cosmoshop-widget-einbindung') == "iframe") {
            if ($widget == "artikelvorschau")   $parameter =  '?ls='.$sprache.'&widget='.get_option('cosmoshop-widget-script-param-vorschau').'&artnum='.$artnum;
            if ($widget == "bestseller")        $parameter =  '?ls='.$sprache.'&widget='.get_option('cosmoshop-widget-script-param-bestseller');
            if ($widget == "highlight")         $parameter =  '?ls='.$sprache.'&widget='.get_option('cosmoshop-widget-script-param-highlight');
         } // if

         include($template_file);

         return $widget_html; // widget_html wird in include($template_file) gesetzt
      } // function

      public function get_cosmoshopwidget_html($url, $id) {
         // holt den Widget-Code vom Cosmoshop
         $cachingzeit = intval(get_option('cosmoshop-widget-cachingzeit'));
         if ((empty($cachingzeit)) || ($cachingzeit < 0))  $cachingzeit = 0;

         $cosmoshop_widget_html = "";

         $cache_dir           = plugin_dir_path( __FILE__ ).'cache/';
         $cache_file_name     = $id.'.dat';
         $cache_file          = $cache_dir.$cache_file_name;

         if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
         } // if

         if (($cachingzeit > 0) && (is_readable($cache_file))) {
            $last_change      = filemtime($cache_file);
            $diff             = time() - $last_change;

            if ($diff < $cachingzeit) $cosmoshop_widget_html = file_get_contents($cache_file);
         } // if

         if ($cosmoshop_widget_html == "") {
            $response = wp_remote_get($url, array('user-agent' => 'WP-CosmoshopWidget', ));
            if (is_wp_error($response)) {
               $error_message = $response->get_error_message();
            } else {
               $cosmoshop_widget_html = $response['body'];
            } // else

            if (($cachingzeit > 0) && ($cosmoshop_widget_html != "")) {
               if (is_readable($cache_dir)) {
                  $datei = fopen($cache_file, "w");
                  fwrite($datei, $cosmoshop_widget_html);
                  fclose($datei);
               } // if
            } // if
         } // if

         return $cosmoshop_widget_html;
      } // function

      function admin_plugin_meta_link($input, $file) {
         // weitere Links im Plugin-Listing einfügen
         if (plugin_basename(CosmoshopWidget_FILE) == $file) {
            return array_merge(
               $input,
               array(
                  '<a href="http://www.mietshop.de/content/kostenlosen-shop-eroeffnen.html?utm_source=wordpress&utm_medium=plugin&utm_campaign=mietshop-wordpress" target="_blank">Shop installieren</a>',
               )
            );
         } // if
         
         return $input;
      } // function

      function admin_plugin_action_links($links, $file) {
         // Link 'Einstellungen' im Plugin-Listing einfügen
         if (plugin_basename(CosmoshopWidget_FILE) == $file) {
            $links[] = '<a href="options-general.php?page=cosmoshop-widget-functions.php">' . __('Settings') . '</a>';
         } // if
         return $links;
      } // function

      function admin_register_settings() {                                                                              // ok
         // Plugin-Optionen registrieren
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-adminkonfiguration-expertenansicht');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-shopurl', array($this, 'admin_options_validate_shopurl'));
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-sprache');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-script-pfad');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-script-param-vorschau');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-script-param-bestseller');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-script-param-highlight');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-adminkonfiguration-tinymce-button-anzeigen');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-adminkonfiguration-tinymce-platzhalter-anzeigen');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-adminkonfiguration-texteditor-button-funktion');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-einbindung');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-cachingzeit');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-cache_del', array($this, 'admin_options_caching_del'));
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-template');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-style');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-style-file');
         register_setting('cosmoshopwidget-option-group', 'cosmoshop-widget-preishinweis');
      } // function

      function admin_options_validate_shopurl($input) {
         $input = preg_replace('/\/cgi-bin\/.*/', '', $input); 
         return $input;
      } // function
      
      function admin_options_caching_del($input) {
         if ($input == "1") {
            $cache_dir = plugin_dir_path( __FILE__ ).'cache/';
            $handle = opendir($cache_dir);
            while(($file = readdir($handle)) !== false) {
               if(!is_dir($cache_dir.$file)) {
                  unlink($cache_dir.$file);
               } // if
            } // while
         } // if
      } //function

      function admin_texteditor_add_quicktags() {
         // Button für Text-Editor einfügen
         if (!wp_script_is('quicktags')) return "";
         $value = get_option('cosmoshop-widget-adminkonfiguration-texteditor-button-funktion');
         if ($value != '') {
            $button = "<script type=\"text/javascript\">QTags.addButton('".CosmoshopWidget::titel."', '".CosmoshopWidget::titel."', '".$value."', '', 'w' );</script>";
            echo $button;
         } // if
      } // function

      function admin_custom_admin_head_tinymce() {
         // CSS, JS und JS-Var für Admin aktivieren
         $css_url = plugin_dir_url( __FILE__ ).'css/widget-admin-style.css';
         echo '<link rel="stylesheet" type="text/css" href="' . $css_url . '" />';
         $cosmoshopwidget_url = get_option('cosmoshop-widget-shopurl').get_option('cosmoshop-widget-script-pfad');
         $tinymce_button      = '"tinymce_button":'.(get_option('cosmoshop-widget-adminkonfiguration-tinymce-button-anzeigen') == 1 ? 'true' : 'false');
         $tinymce_platzhalter = '"tinymce_platzhalter":'.(get_option('cosmoshop-widget-adminkonfiguration-tinymce-platzhalter-anzeigen') == 1 ? 'true' : 'false');
         $js = 'var global_cosmoshopwidget = {"titel":"'.CosmoshopWidget::titel.'", "shortcode":"'.CosmoshopWidget::shortcode.'","url":"'.$cosmoshopwidget_url.'","sprache":"'.get_option('cosmoshop-widget-sprache').'",'.$tinymce_button.','.$tinymce_platzhalter.'}';
         echo '<script type="text/javascript">'.$js.'</script>';
      } // function

      function admin_tinymce_add_button($buttons) {
         array_push($buttons, '|', 'cosmoshopwidget');
         return $buttons;
      } // function

      function admin_tinymce_js($plugins) {
         $plugins['cosmoshopwidget'] = plugin_dir_url( __FILE__ ).'js/cosmoshop-widget-tinymce.js';
         return $plugins;
      } // function

      public static function on_activation() {
         if (get_option('cosmoshop-widget') == "") {
            add_option('cosmoshop-widget-install', '1');

            add_option('cosmoshop-widget-adminkonfiguration-expertenansicht',                '0');
            add_option('cosmoshop-widget-shopurl',                                           '');
            add_option('cosmoshop-widget-sprache',                                           'de');
            add_option('cosmoshop-widget-script-pfad',                                       '/cgi-bin/interfaces/widgets/widget.cgi');
            add_option('cosmoshop-widget-script-param-vorschau',                             'ArticlePreview');
            add_option('cosmoshop-widget-script-param-bestseller',                           'Bestseller');
            add_option('cosmoshop-widget-script-param-highlight',                            'Highlight');
            add_option('cosmoshop-widget-adminkonfiguration-tinymce-button-anzeigen',        '1');
            add_option('cosmoshop-widget-adminkonfiguration-tinymce-platzhalter-anzeigen',   '1');
            add_option('cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen',     '1');
            add_option('cosmoshop-widget-adminkonfiguration-texteditor-button-funktion',     '['.CosmoshopWidget::shortcode.' widget=artikelvorschau|bestseller|highlight artnum=... class=alignleft|alignright]');
            add_option('cosmoshop-widget-einbindung',                                        'text');
            add_option('cosmoshop-widget-cachingzeit',                                       '900');
            add_option('cosmoshop-widget-template',                                          'default');
            add_option('cosmoshop-widget-style',                                             '');
            add_option('cosmoshop-widget-style-file',                                        '');
            add_option('cosmoshop-widget-preishinweis',                                      '* Alle Preise inkl. MwSt. zzgl. Versandkosten');
         } // if
      } // function

      public static function on_deactivation() {
      } // function

      public function on_uninstall() {
         delete_option('cosmoshop-widget-install');

         delete_option('cosmoshop-widget-adminkonfiguration-expertenansicht');
         delete_option('cosmoshop-widget-shopurl');
         delete_option('cosmoshop-widget-sprache');
         delete_option('cosmoshop-widget-script-pfad');
         delete_option('cosmoshop-widget-script-param-vorschau');
         delete_option('cosmoshop-widget-script-param-bestseller');
         delete_option('cosmoshop-widget-script-param-highlight');
         delete_option('cosmoshop-widget-adminkonfiguration-tinymce-button-anzeigen');
         delete_option('cosmoshop-widget-adminkonfiguration-tinymce-platzhalter-anzeigen');
         delete_option('cosmoshop-widget-adminkonfiguration-texteditor-button-anzeigen');
         delete_option('cosmoshop-widget-adminkonfiguration-texteditor-button-funktion');
         delete_option('cosmoshop-widget-einbindung');
         delete_option('cosmoshop-widget-cachingzeit');
         delete_option('cosmoshop-widget-cache_del');
         delete_option('cosmoshop-widget-template');
         delete_option('cosmoshop-widget-style');
         delete_option('cosmoshop-widget-style-file');
         delete_option('cosmoshop-widget-preishinweis');
      } // function

      public function admin_contextual_help($contextual_help, $screen) {
         if ($screen = "widgets") {
         } // if

         if (false) {
            get_current_screen()->add_help_tab( array(
               'id'      => 'cosmoshop-widget-'.$screen,
               'title'   => CosmoshopWidget::titel,
               'content' => 'Screen: "'.$screen.'"',
            ));
         } // if

         return $contextual_help;
      } // function

      public function admin_add_options_page_link() {
         // Plugin-Optionen-Link in Menü einfügen
         add_options_page(
            CosmoshopWidget::titel,
            CosmoshopWidget::titel, 
            'manage_options', 
            basename(__FILE__),
            array($this, 'admin_edit_plugin_options')
         );
      } // function

      public function admin_edit_plugin_options() {
         // Maske für Plugin-Optionen
         require("cosmoshop-widget-options-admin.php");
         $Admin = new CosmoshopWidgetAdmin();
         $Admin->Hauptmenue($this);
      } // function

   } // class
} // if

?>
