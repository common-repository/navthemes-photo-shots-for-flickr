<?php
/*----------------------------------------------------------------------------------------

	Plugin Name: NavThemes Photoshots for Flickr
	Plugin URI: http://www.navthemes.com/flickr-shots-by-navThemes
	Description: A very lightweight plugin to display your flickr photos shots on your site.
	Author: NavThemes
	Author URI: http://www.navthemes.com
	Version: 1.1.1
	Text Domain: navthemes-photoshots-for-flickr

----------------------------------------------------------------------------------------*/

// Add function to the widgets_init hook to load the widget
add_action('widgets_init', 'widget_navthemes_photoshots_for_flickr');

// Register Widget
function widget_navthemes_photoshots_for_flickr() {
	register_widget('Widget_Navthemes_Photoshots_For_Flickr');
}

// Widget Class
class Widget_Navthemes_Photoshots_For_Flickr extends WP_Widget {

/**----------------------------------------------------------
 	Sets up the widgets name etc
/**----------------------------------------------------------*/ 

function __construct() {
 		
		parent::__construct(
			'widget_navthemes_photoshots_for_flickr', // Base ID
			__( 'NavThemes Photoshots for Flickr', 'navthemes-photoshots-for-flickr' ), // Name
			array( 'description' => __('A very lightweight plugin to display your flickr photos shots on your site.', 'navthemes-photoshots-for-flickr')) // Args
		);
		
	 	//Include CSS file.
		function navthemes_photoshots_for_flickr_scripts() {
			wp_register_style( 'navthemes-photoshots-for-flickr-style',  plugin_dir_url( __FILE__ ) . 'assets/flickr-shots.css' );
			wp_enqueue_style( 'navthemes-photoshots-for-flickr-style' );
		}
		add_action( 'wp_enqueue_scripts', 'navthemes_photoshots_for_flickr_scripts' ); 
		
	}

/**----------------------------------------------------------
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
/**----------------------------------------------------------*/ 

	function widget($args, $instance) {
		
		extract($args);

		// Widget settings by the user
		$title = apply_filters('widget_title', $instance['title']);
		
		$flickr_id = $instance['flickr_id'];
		$number_photos = $instance['number_photos'];
		$open_new_window = $instance['open_new_window'];
					
		// Window 
		$open_new_window = $open_new_window ==1 ? 'target = _blank' : '';
		
		// Before widget - defined by theme
		echo $before_widget;

		// Title of widget - defined by theme - $before_title and $after_title
		if ($title)
			echo $before_title . $title . $after_title;

		// Display widget content
		?>
		<ul id="flickr-photos-<?php echo $args['widget_id']; ?>" class="flickr-photos clearboth">

			<?php
				$regx = "/<img(.+)\/>/";
				$photos = array();

				// Fetch flickr feed
				$rss = 'http://api.flickr.com/services/feeds/photos_public.gne?ids=' . $flickr_id . '&lang=en-us&format=rss_200';
				$flickr_rss = simplexml_load_file($rss);
 				
				// Now we extract images from feed
				foreach($flickr_rss->channel->item as $item) {
					preg_match($regx, $item->description, $matches);
					
					// Now Photos in array
					$photos[] = array(							  
						'title' => $item->title,
						'link' => $item->link,
						'thumb' => $matches[0]
					);
				}

				$i = 0;
				
				// lets run loop
				foreach($photos as $photo) {
					if ($i < $number_photos) {
						$photo_thumb = str_replace("_m", "_s", $photo['thumb']); 
						echo '<li><a ' . $open_new_window . ' href="' . $photo['link'] . '" title="' . $photo['title'] . '">' .  $photo_thumb . '</a></li>';
						
						$i++;
					}
				}
			?>				
		</ul>

		<?php
	 	// After widget - defined by theme
		echo $after_widget;
		
	}

/**--------------------------------------------------------------------------------------
	* Outputs the options form on admin
	*
	* @param array $instance The widget options															*/
/**--------------------------------------------------------------------------------------*/	
	
	function form($instance) {

		// Set up the default settings
		$defaults = array(
			'title' => 'Flickr Shots',
			'flickr_id' => '',
			'number_photos' => '6',
			'open_new_window' => 1
		);
		
		$instance = wp_parse_args((array) $instance, $defaults); ?>

		<!-- Title -->
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'navthemes-photoshots-for-flickr'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>

		<!-- Flickr ID -->
		<p>
			<label for="<?php echo $this->get_field_id('flickr_id'); ?>"><?php _e('Flickr ID:', 'navthemes-photoshots-for-flickr'); ?> <a href="http://idgettr.com/" target="_blank">&#40;Find your ID&#41;</a></label>
			<input type="text" id="<?php echo $this->get_field_id('flickr_id'); ?>" name="<?php echo $this->get_field_name('flickr_id'); ?>" value="<?php echo $instance['flickr_id']; ?>" class="widefat" />
		</p>
		
		<!-- Number of Photos -->
		<p>
			<label for="<?php echo $this->get_field_id('number_photos'); ?>"><?php _e('Number of Photos:', 'navthemes-photoshots-for-flickr'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id('number_photos'); ?>" name="<?php echo $this->get_field_name('number_photos'); ?>" value="<?php echo $instance['number_photos']; ?>" class="widefat" />
		</p>
        		
		<!-- Open New Window -->
		<p>
			<label for="<?php echo $this->get_field_id('open_new_window'); ?>"><?php _e('Open Link in new Window ?', 'navthemes-photoshots-for-flickr'); ?></label>
			<input value="1" type="checkbox" id="<?php echo $this->get_field_id('open_new_window'); ?>" name="<?php echo $this->get_field_name('open_new_window'); ?>"
            <?php if($instance['open_new_window']) echo " checked=checked"; ?>
            
             />
		</p>

	<?php
	}
	

/**--------------------------------------------------------------------------------------
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
/**--------------------------------------------------------------------------------------*/	
	
	function update($new_instance, $old_instance) {
		
		// processes widget options to be saved
		$update['title'] = strip_tags($new_instance['title']);
		$update['flickr_id'] = strip_tags($new_instance['flickr_id']);
		$update['number_photos'] = strip_tags($new_instance['number_photos']);
		
		$update['open_new_window'] = strip_tags($new_instance['open_new_window']);

		return $update;

	}
	

}
?>