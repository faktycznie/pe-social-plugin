<?php
/**
 * Plugin Name: PE_Social_Icons
 * Plugin URI:  https://pixelemu.com
 * Description: Widget
 * Version:     1.00
 * Author:      artur.kaczmarek@pixelemu.com
 * Author URI:  https://www.pixelemu.com
 * Text Domain: pe-social
 * License:     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if( !class_exists('PE_Social_Icons') ) {
	class PE_Social_Icons extends WP_Widget {

		function __construct() {
			$widget_ops = array( 'classname' => 'pe-widget-social', 'description' => __('Displays Social Icons.','pe-social') );
			parent::__construct( 'PE_Social_Icons', __('PE Social','pe-social'), $widget_ops );
			add_action( 'widgets_init', array( $this, 'addWidgets' ) );
		}

		// ---------------------------------------------------------------
		// Widget
		// ---------------------------------------------------------------

		public function widget($args,  $setup) {
			extract($args);

			$title = apply_filters('widget_title', $setup['title']);
			if ( empty($title) ) $title = false;

			$widget_id = $this->number;
			$widget_name = $this->id;

			$target = ( !empty($setup['target']) ) ? $setup['target'] : '_blank';

			// before widget
			echo $before_widget;

			// title
			if($title):
				echo $before_title;
				echo $title;
				echo $after_title;
			endif;

			$dataArray = ( !empty($setup['data']) ) ? json_decode($setup['data']) : false;

			if( $dataArray ) {
				$output = array();
				foreach($dataArray as  $items => $fields) {
					$itemArr = array();
					foreach($fields as $field => $item ) {
						$key = $item->name;
						$value = $item->value;
						$itemArr[$key]= $value;
					}
					$output[] = $itemArr;
				}
			}

			$items = count($output);
			$i = 0;

			if( empty($items) ) {
				echo '<p class="pe-alert"> ' . __('No items', 'pe-social') . ' </p>';
				return;
			}

			?>

			<ul class="pe-social-icons">

			<?php foreach( $output as $item ) :

				$i++;

				$item_class = ( !empty($item['name']) ) ? $item['name'] : '';
				$icon_class = ( !empty($item['icon']) ) ? $item['icon'] : ''

			?>

				<?php if( !empty($item['url']) && !empty($item['icon']) ) : ?>
				<li class="item item-<?php echo $i; ?>">
					<a class="link <?php echo $item_class; ?>" href="<?php echo esc_url($item['url']); ?>" target="<?php echo $target; ?>"><span class="<?php echo $icon_class; ?>" aria-hidden="true"></span>
						<?php if( !empty($item['name']) ) : ?>
						<span class="sr-only"><?php echo esc_html($item['name']); ?></span>
						<?php endif; ?>
					</a>
				</li>
				<?php endif; ?>

			<?php endforeach; ?>

			</ul>

			<?php
			// after widget
			echo $after_widget;

		}

		// ---------------------------------------------------------------
		// WIDGET FORM
		// ---------------------------------------------------------------

		public function form($setup) {
			$setup = wp_parse_args( (array) $setup, array(
				'title'    => '',
				'data'     => '',
				'target'   => '',
			) );

			$title = $setup['title'];

			$data = ( !empty($setup['data']) ) ? $setup['data'] : '';

			$target = ( !empty($setup['target']) ) ? $setup['target'] : '';

			?>
			<div id="<?php echo $this->id; ?>" class="multi-fields pe-widget-container">

				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'pe-social'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</p>

				<?php

				$dataArray = ( !empty($data) ) ? json_decode($data, true) : false;

				if( $dataArray ) {
					$count = 0;
					echo '<div class="pe-items"><ol>';
					foreach($dataArray as  $item => $val) {
						$name = ( !empty($val[0]['value']) ) ? esc_html($val[0]['value']) : __('Item', 'pe-social') . ' #' . $count;
						echo '<li>' . $name . ' - <a href="#' . $count . '" data-item="' . $count . '" class="edit-item">' . __('Edit', 'pe-social') . '</a> | <a href="#' . $count . '" data-item="' . $count . '" class="remove-item">' . __('Remove', 'pe-social') . '</a> </li>';
						$count++;
					}
					echo '</ol></div>';
				}

				?>

				<!-- fields group -->

				<a class="add-new button button-primary purple" href="#"><?php _e('Add new item', 'pe-social'); ?></a>

				<div class="pe-fields-group" style="display: none;">

					<p>
						<label for="name"><?php _e('Name:', 'pe-social'); ?></label>
						<input class="widefat" id="name" name="name" type="text" value="" />
					</p>

					<p>
						<label for="icon"><?php _e('Icon:', 'pe-social'); ?></label>
						<input class="widefat" id="icon" name="icon" type="text" value="" />
					</p>

					<p>
						<label for="url"><?php _e('URL:', 'pe-social'); ?></label>
						<input class="widefat" id="url" name="url" type="text" value="" />
					</p>

					<a class="save-item button button-primary green" href="#"><?php _e('Save item', 'pe-social'); ?></a>
					<a class="cancel-item button button-primary red" href="#"><?php _e('Cancel', 'pe-social'); ?></a>

				</div>
				<!-- fields group -->

				<p>
					<label for="<?php echo $this->get_field_id( 'target' ); ?>"><?php _e( 'Target', 'pe-social' ); ?></label>
					<select class="widefat" id="<?php echo $this->get_field_id( 'target' ); ?>" name="<?php echo $this->get_field_name( 'target' ); ?>" style="width:100%;">
						<option value='_blank'<?php echo ( $target == '_blank' ) ? 'selected' : ''; ?>><?php _e( '_blank', 'pe-social' ); ?></option>
						<option value='_self'<?php echo ( $target == '_self' ) ? 'selected' : ''; ?>><?php _e( '_self', 'pe-social' ); ?></option>
					</select>
				</p>

				<!-- data holder -->
				<input class="data-holder" id="<?php echo $this->get_field_id('data'); ?>" name="<?php echo $this->get_field_name('data'); ?>" type="hidden" value='<?php echo $data; ?>' />

				<?php
					$saved_id = ( $this->number == '__i__' ) ? 'false' : '"' . $this->id . '"';
				?>

				<script>
					jQuery(document).ready(function() {
						var init_widget = false;
						var widget_id = <?php echo $saved_id; ?>;

						if( widget_id ) {
							init_widget = new PEwidget('#' + widget_id);
						}

						jQuery(document).on('widget-added', function(event, widget){
								widget_id = jQuery(widget).find('.multi-fields').attr('id');
								if( widget_id && !jQuery(widget).hasClass('latest-added') ) {
									init_widget = new PEwidget('#' + widget_id);
									jQuery('#widgets-right .widget').removeClass('latest-added');
									jQuery(widget).addClass('latest-added');
								}
						});

						jQuery(document).on('widget-updated', function(event, widget){
							if( init_widget ) {
								init_widget.showtime();
							}
						});
					});
				</script>

			</div>
			<?php
		}

		// ---------------------------------------------------------------
		// WIDGET UPDATE
		// ---------------------------------------------------------------

		public function update($new_setup, $old_setup) {
			$setup = $old_setup;

			$setup['title'] = strip_tags($new_setup['title']);
			$setup['data'] = strip_tags($new_setup['data']);
			$setup['target'] = strip_tags($new_setup['target']);
			return $setup;
		}

	}
}

add_action( 'admin_enqueue_scripts', 'pe_social_enqueueAdmin' );
function pe_social_enqueueAdmin( $hook ) {
	if( $hook != 'widgets.php' ) return;
	wp_enqueue_script( 'pe-multifields-admin', plugin_dir_url( __FILE__ ) . 'assets/multifields-admin.js', array('jquery'), false );
}

add_action( 'widgets_init', 'pe_social_init' );
function pe_social_init() {
	register_widget( 'PE_Social_Icons' );
}

?>
