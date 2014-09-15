<?php
class cosmoshopwidget_widget_text extends WP_Widget {
   function cosmoshopwidget_widget_text() {
      parent::__construct(
         'cosmoshop-widget_text',                 // Base ID
         CosmoshopWidget::titel.': Hinweis',      // Name
          array(
            'description' => CosmoshopWidget::titel.'-Hinweis Anzeigen'
         )
      );
   } // function

   function widget($args, $instance) {
      extract($args);
      $title                  = apply_filters('widget_title', $instance['title']);
      // $text                   = apply_filters('widget_title', $instance['text']);

      $text                   = get_option('cosmoshop-widget-preishinweis');
      $widget_style           = get_option('cosmoshop-widget-style');

      echo $before_widget;

      if (!empty($title))
         echo $before_title . $title . $after_title;

      echo '<div class="wrapper-mietshop-widget-hinweis '.$widget_style.'">'.$text.'</div>';

      echo $after_widget;
   } // function

   function update($new_instance, $old_instance ) {
      $instance = array();

      $instance['title']         = strip_tags($new_instance['title']);
      //$instance['text']          = strip_tags($new_instance['text']);
      
      $text = strip_tags($new_instance['text']);
      update_option('cosmoshop-widget-preishinweis', $text);

      return $instance;
   } // function

   function form($instance) {
      $defaults = array(
         'title'        => '',
         'text'         => ''
      );
      $instance = wp_parse_args((array)$instance, $defaults);

      $title      = $instance['title'];
      //$text       = $instance['text'];
      $text       = get_option('cosmoshop-widget-preishinweis');
      
      ?>
      <p>
         <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Titel:'; ?></label>
         <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
         <label for="<?php echo $this->get_field_id('text'); ?>"><?php echo 'Text:'; ?></label>
         <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo esc_attr($text); ?>" />
      </p>
      <?php
   } // function
}

class cosmoshopwidget_widget extends WP_Widget {
   function cosmoshopwidget_widget() {
      parent::__construct(
         'cosmoshop-widget',           // Base ID
         CosmoshopWidget::titel,       // Name
          array(
            'description' => CosmoshopWidget::titel.'-Widget anzeigen'
         )
      );
   } // function

   function widget($args, $instance) {
      extract($args);
      $title                  = apply_filters('widget_title', $instance['title']);
      $widget                 = $instance['widget_typ'];
      $artnum                 = $instance['artnum'];

      $script                 = get_option('cosmoshop-widget-shopurl').get_option('cosmoshop-widget-script-pfad');
      $sprache                = get_option('cosmoshop-widget-sprache');
      $widget_style           = get_option('cosmoshop-widget-style');

      $template_typ           = get_option('cosmoshop-widget-einbindung');
      $template_dir           = plugin_dir_path( __FILE__ ).'template/';
      $template_file_name     = $template_typ.'_widget_'.$widget.'_'.get_option('cosmoshop-widget-template');
      $template_file          = $template_dir.$template_file_name.'.php';

      if (!is_readable($template_file)) {
         $template_file_name  = $template_typ.'_widget_'.$widget.'_default';
         $template_file       = $template_dir.$template_file_name.'.php';
      } // if

      if (!is_readable($template_file)) {
         return "";
      } // if

      $shop_widget_html = ''; // wird im Template benötigt
      if (get_option('cosmoshop-widget-einbindung') == "text") {
         $url = $script;
         if ($widget == "artikelvorschau")   $url .=  '?widget='.get_option('cosmoshop-widget-script-param-vorschau').'&artnum='.$artnum;
         if ($widget == "bestseller")        $url .=  '?widget='.get_option('cosmoshop-widget-script-param-bestseller');
         if ($widget == "highlight")         $url .=  '?widget='.get_option('cosmoshop-widget-script-param-highlight');
            
         $id = $widget.'_'.$sprache;
         if ($widget == "artikelvorschau") $id .= '_'.$artnum;

         $shop_widget_html = CosmoshopWidget::get_cosmoshopwidget_html($url, $id);
      } // if
 
      $parameter = ""; // wird im Template benötigt
      if (get_option('cosmoshop-widget-einbindung') == "iframe") {
         if ($widget == "artikelvorschau")   $parameter =  '?widget='.get_option('cosmoshop-widget-script-param-vorschau').'&artnum='.$artnum;
         if ($widget == "bestseller")        $parameter =  '?widget='.get_option('cosmoshop-widget-script-param-bestseller');
         if ($widget == "highlight")         $parameter =  '?widget='.get_option('cosmoshop-widget-script-param-highlight');
      } // if
 
      echo $before_widget;

      if (!empty($title))
         echo $before_title . $title . $after_title;

      include($template_file);
      echo $widget_html;

      echo $after_widget;
   } // function

   function update($new_instance, $old_instance ) {
      $instance = array();

      $instance['title']         = strip_tags($new_instance['title']);
      $instance['widget_typ']    = $new_instance['widget_typ'];
      $instance['artnum']        = $new_instance['artnum'];

      return $instance;
   } // function

   function form($instance) {
      $defaults = array(
         'title'        => '',
         'widget_typ'   => 'bestseller',
         'artnum'       => ''
      );
      $instance = wp_parse_args((array)$instance, $defaults);

      $title      = $instance['title'];
      $widget_typ = $instance['widget_typ'];
      $artnum     = $instance['artnum'];
      
      $style_artnum = ($widget_typ == 'artikelvorschau')   ? 'style="display:inline;"' : 'style="display: none;"';

      ?>
      <p>
         <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Titel:'; ?></label>
         <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
       <p>
         <input type="radio" onchange="admin_change_widget('<?php echo $this->get_field_id('widget')?>');" id="<?php echo $this->get_field_id('widget').'-artikelvorschau' ?>" name="<?php echo $this->get_field_name('widget_typ') ?>" value="artikelvorschau" <?php echo checked($widget_typ == 'artikelvorschau', true, false) ?>>
         <label for="<?php echo $this->get_field_id('widget-artikelvorschau'); ?>"><?php echo 'Widget: Artikelvorschau'; ?></label><br>
         <input type="radio" onchange="admin_change_widget('<?php echo $this->get_field_id('widget')?>');" id="<?php echo $this->get_field_id('widget').'-bestseller' ?>" name="<?php echo $this->get_field_name('widget_typ') ?>" value="bestseller" <?php echo checked($widget_typ == 'bestseller', true, false) ?>>
         <label for="<?php echo $this->get_field_id('widget-bestseller'); ?>"><?php echo 'Widget: Bestseller'; ?></label><br>
         <input type="radio" onchange="admin_change_widget('<?php echo $this->get_field_id('widget')?>');" id="<?php echo $this->get_field_id('widget').'-highlight' ?>" name="<?php echo $this->get_field_name('widget_typ') ?>" value="highlight" <?php echo checked($widget_typ == 'highlight', true, false) ?>>
         <label for="<?php echo $this->get_field_id('widget-highlight'); ?>"><?php echo 'Widget: Highlight'; ?></label><br>
      </p>
      <p <?php echo $style_artnum?> id="<?php echo $this->get_field_id('widget-artnum-p'); ?>">
         <label for="<?php echo $this->get_field_id('artnum'); ?>"><?php echo 'Artikelnummer:'; ?></label>
         <input class="widefat" id="<?php echo $this->get_field_id('artnum'); ?>" name="<?php echo $this->get_field_name('artnum'); ?>" type="text" value="<?php echo esc_attr($artnum); ?>" />
      </p>
      
      <script language="JavaScript" type="text/javascript"> 
         function admin_change_widget(id) {
            if (document.getElementById(id + "-artikelvorschau").checked == true) {
               document.getElementById(id + "-artnum-p").style="display:inline;";
            } else {
               document.getElementById(id + "-artnum-p").style="display:none;";
            } // else
         } // function
      </script>
      <?php
   } // function
}
?>
