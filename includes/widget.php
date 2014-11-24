<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or die;

if ( ! class_exists( 'BP_Sidebar_Item_Nav_Widget' ) ) :
/**
 * BP Sidebar Item Nav_Widget
 *
 * Adds a widget to move avatar/item nav into the sidebar
 *
 * @since  1.0
 *
 * @uses   WP_Widget
 */
class BP_Sidebar_Item_Nav_Widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @since  1.0
	 *
	 * @uses   WP_Widget::__construct() to init the widget
	 */
	public function __construct() {

		$widget_ops = array(
			'description' => __( 'Displays BuddyPress item primary nav & avatar in the sidebar of your site.', 'bp-sidebar-item-nav' ),
			'classname'   => 'widget_nav_menu buddypress_item_nav'
		);

		parent::__construct(
			'bp_sidebar_item_nav_widget',
			__( '(BuddyPress) Sidebar item nav', 'bp-sidebar-item-nav' ),
			$widget_ops
		);
	}

	/**
	 * Register the widget
	 *
	 * @since  1.0
	 *
	 * @uses   register_widget() to register the widget
	 */
	public static function register_widget() {
		register_widget( 'BP_Sidebar_Item_Nav_Widget' );
	}

	/**
	 * Displays the output, the button to post new support topics
	 *
	 * @since  1.0
	 *
	 * @param  mixed $args Arguments
	 * @return string html output
	 */
	public function widget( $args, $instance ) {
		$bp_sidebar_item_nav = bp_sidebar_item_nav_loader();

		if ( ! bp_is_user() && ! bp_is_group() && bp_is_group_create() ) {
			return;
		}

		$item_nav_args = wp_parse_args( $instance, apply_filters( 'bp_sidebar_item_nav_widget_args', array(
			'bpsbin_title'  => '',
			'bpsbin_member' => true,
			'bpsbin_group'  => true,
		) ) );

		$title = '';

		if ( ! empty( $item_nav_args[ 'bpsbin_title' ] ) ) {
			if ( ! empty( $item_nav_args[ 'bpsbin_member' ] ) && bp_is_user() ) {
				$title = sprintf( esc_html_x( '%1$s&#39;s %2$s', 'Member widget title', 'bp-sidebar-item-nav' ),
					esc_html__( 'Member', 'bp-sidebar-item-nav' ),
					esc_html( $item_nav_args[ 'bpsbin_title' ] )
				);
			} else if ( ! empty( $item_nav_args[ 'bpsbin_group' ] ) && bp_is_group() ) {
				$title = sprintf( esc_html_x( '%1$s&#39;s %2$s', 'Group widget title', 'bp-sidebar-item-nav' ),
					esc_html__( 'Group', 'bp-sidebar-item-nav' ),
					esc_html( $item_nav_args[ 'bpsbin_title' ] )
				);
			}
		}

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			$bp_sidebar_item_nav->widget_title =  $args['before_title'] . $title . $args['after_title'];
		}

		if ( ! empty( $item_nav_args[ 'bpsbin_member' ] ) && bp_is_user() ) {
			// Get member's sidebar template part
			bp_get_template_part( 'members/single/sidebar' );

		} elseif ( ! empty( $item_nav_args[ 'bpsbin_group' ] ) && bp_is_group() ) {
			// Get group's sidebar template part
			bp_get_template_part( 'groups/single/sidebar' );

		}

		echo $args['after_widget'];
	}

	/**
	 * Update the new support topic widget options (title)
	 *
	 * @since  1.0
	 *
	 * @param  array $new_instance The new instance options
	 * @param  array $old_instance The old instance options
	 * @return array the instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['bpsbin_title'] = strip_tags( $new_instance['bpsbin_title'] );

		$instance['bpsbin_avatar'] = false;

		if ( isset( $new_instance['bpsbin_avatar'] ) ) {
			$instance['bpsbin_avatar'] = (bool) $new_instance['bpsbin_avatar'];
		}

		$instance['bpsbin_member'] = false;

		if ( isset( $new_instance['bpsbin_member'] ) ) {
			$instance['bpsbin_member'] = (bool) $new_instance['bpsbin_member'];
		}

		$instance['bpsbin_group'] = false;

		if ( isset( $new_instance['bpsbin_group'] ) ) {
			$instance['bpsbin_group'] = (bool) $new_instance['bpsbin_group'];
		}

		return $instance;
	}

	/**
	 * Output the new support topic widget options form
	 *
	 * @since  1.0
	 *
	 * @param  $instance Instance
	 * @return string HTML Output
	 */
	public function form( $instance ) {
		$defaults = array(
			'bpsbin_title'  => __( 'Navigation', 'bp-sidebar-item-nav' ),
			'bpsbin_avatar' => true,
			'bpsbin_member' => true,
			'bpsbin_group'  => true,
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		$bpbbpst_title = strip_tags( $instance['bpsbin_title'] );
		$bpsbin_avatar = (bool) $instance['bpsbin_avatar'];
		$bpsbin_member = (bool) $instance['bpsbin_member'];
		$bpsbin_group  = (bool) $instance['bpsbin_group'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'bpsbin_title' ); ?>"><?php esc_html_e( 'Title:', 'bp-sidebar-item-nav' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'bpsbin_title' ); ?>" name="<?php echo $this->get_field_name( 'bpsbin_title' ); ?>" type="text" value="<?php echo $bpbbpst_title; ?>" />
		</p>

		<input class="checkbox" type="checkbox" <?php checked( $bpsbin_avatar, true ) ?> id="<?php echo $this->get_field_id( 'bpsbin_avatar' ); ?>" name="<?php echo $this->get_field_name( 'bpsbin_avatar' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'bpsbin_avatar' ); ?>"><?php esc_html_e( 'Move avatar in sidebar', 'bp-sidebar-item-nav' ); ?></label><br />

		<input class="checkbox" type="checkbox" <?php checked( $bpsbin_member, true ) ?> id="<?php echo $this->get_field_id( 'bpsbin_member' ); ?>" name="<?php echo $this->get_field_name( 'bpsbin_member' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'bpsbin_member' ); ?>"><?php esc_html_e( 'Move member&#39;s nav in sidebar', 'bp-sidebar-item-nav' ); ?></label><br />

		<?php if ( bp_is_active( 'groups') ) : ?>
			<input class="checkbox" type="checkbox" <?php checked( $bpsbin_group, true ) ?> id="<?php echo $this->get_field_id( 'bpsbin_group' ); ?>" name="<?php echo $this->get_field_name( 'bpsbin_group' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bpsbin_group' ); ?>"><?php esc_html_e( 'Move group&#39;s nav in sidebar', 'bp-sidebar-item-nav' ); ?></label><br />
		<?php endif;
	}
}

endif;
