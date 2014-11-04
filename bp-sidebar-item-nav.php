<?php
/**
 * BP Sidebar Item Nav is a widget to move BuddyPress item navs in the sidebar.
 *
 * @package   BP Sidebar Item Nav
 * @author    imath
 * @license   GPL-2.0+
 * @link      http://imathi.eu
 *
 * @buddypress-plugin
 * Plugin Name:       BP Sidebar Item Nav
 * Plugin URI:        http://imathi.eu/tag/bp-sidebar-item-nav
 * Description:       Move BuddyPress item navs into the sidebar
 * Version:           1.0.0-alpha
 * Author:            imath
 * Author URI:        http://imathi.eu/
 * Text Domain:       bp-sidebar-item-nav
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/imath/bp-sidebar-item-nav
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;


if ( ! class_exists( 'BP_Sidebar_Item_Nav_Loader' ) ) :
/**
 * BP Sidebar Item Nav Loader Class
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Sidebar_Item_Nav_Loader {
	/**
	 * Instance of this class.
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @var     object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 */
	private function setup_globals() {
		/** BP Sidebar Item Nav globals ****************************************************/
		$this->version      = '1.0.0-alpha';

		$this->domain       = 'bp-sidebar-item-nav';
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );
		$this->lang_dir     = trailingslashit( $this->plugin_dir   . 'languages' );
		$this->includes_dir = trailingslashit( $this->plugin_dir   . 'includes'  );
		$this->tpl_dir      = trailingslashit( $this->plugin_dir ) . 'templates'  ;
		$this->tpl_url      = trailingslashit( $this->plugin_url   . 'templates' );

		$this->widget_settings  = $this->get_widget_settings();
		$this->is_widget_active = is_active_widget( false, false, 'bp_sidebar_item_nav_widget', true );
		$this->widget_title     = false;

		/** BuddyPress specific globals ***********************************************/
		$this->bp_version      = '2.1';
		$this->is_root_site    = bp_is_root_blog();
	}

	/**
	 * Checks BuddyPress version
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 */
	public function version_check() {
		// taking no risk
		if ( ! defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, $this->bp_version, '>=' );
	}

	/**
	 * Includes the needed file
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 */
	public function includes() {
		if ( ! $this->version_check() || ! $this->is_root_site ) {
			return;
		}

		require( $this->includes_dir . 'widget.php' );
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 */
	private function setup_hooks() {
		// Do things only if required BuddyPress version match and we're on root blog
		if ( $this->version_check() && $this->is_root_site ) {
			add_action( 'bp_register_theme_directory', array( $this,                        'register_template_dir' ) );
			add_action( 'bp_widgets_init',             array( 'BP_Sidebar_Item_Nav_Widget', 'register_widget'       ) );
			add_action( 'bp_enqueue_scripts',          array( $this,                        'enqueue_style'         ) );

		} else {
			add_action( 'admin_notices', array( $this, 'admin_warning' ) );
		}

		// loads the languages..
		add_action( 'bp_loaded', array( $this, 'load_textdomain' ) );

	}

	/**
	 * Get the first widget found and returns its settings
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return array the widget settings
	 */
	public function get_widget_settings() {
		$widget_settings = bp_get_option( 'widget_bp_sidebar_item_nav_widget' );

		if ( ! is_array( $widget_settings ) ) {
			return false;
		}

		$settings = array();

		foreach ( $widget_settings as $key => $setting ) {
			if ( ! is_numeric( $key ) ) {
				continue;
			}

			// get the first found
			if ( empty( $settings ) ) {
				$settings = (array) $setting;

			// then break
			} else {
				break;
			}
		}

		return $settings;
	}

	/**
	 * Add plugin's template dir to BuddyPress template stack
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 */
	public function register_template_dir() {
		// Insert it after parent theme & child theme, but before BP Legacy
		bp_register_template_stack( array( $this, 'template_dir' ),  13 );
	}

	/**
	 * Returns the plugin's templates dir or let BuddyPress deals with it
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return mixed false|string the template dir of the plugin or false if not needed
	 */
	public function template_dir() {
		// Don't change anything if widget is not active or not in a desired BuddyPress area
		if ( ! bp_is_user() && ! bp_is_group() && ! $this->is_widget_active && bp_is_group_create() ) {
			return false;
		}

		// Don't change anything if the sidebar is not set
		if ( ! is_active_sidebar( $this->is_widget_active ) ) {
			return false;
		}

		// Don't change anything if widget settings are not set
		if ( empty( $this->widget_settings ) ) {
			return false;
		}

		// Don't change anything if admin don't want the member's nav to be in sidebar
		if ( ! $this->is_member_nav_in_sidebar() && bp_is_user() ) {
			return false;
		}

		// Don't change anything if admin don't want the group's nav to be in sidebar
		if ( ! $this->is_group_nav_in_sidebar() && bp_is_group() ) {
			return false;
		}

		return apply_filters( 'bp_sidebar_item_nav_template_dir', $this->tpl_dir );
	}

	/**
	 * Output a warning in case required BuddyPress version does not match.
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return string HTML Output
	 */
	public function admin_warning() {
		if ( $this->version_check() ) {
			return;
		}
		?>
		<div id="message" class="error fade">
			<p>
				<?php printf( esc_html__( 'Ouch!! Please upgrade to BuddyPress %s to use this widget!', 'bp-sidebar-item-nav' ), $this->bp_version ) ;?>
			</p>
		</div>
		<?php
	}

	/**
	 * Loads the translation files
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_plugin_textdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		$test = load_plugin_textdomain( $this->domain, false, dirname( $this->basename ) . '/languages' );
	}

	/**
	 * Load some style if on targetted areas
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @uses wp_enqueue_style
	 */
	public function enqueue_style() {
		if ( ! bp_is_user() && ! bp_is_group() && ! $this->is_widget_active && bp_is_group_create() ) {
			return false;
		}

		// Use this filter to override my style!
		$style = apply_filters( 'bp_sidebar_item_nav_style', $this->tpl_url . 'item-nav.css' );

		wp_enqueue_style( 'bp-sidebar-item-nav-style', $style, array(), $this->version );
	}

	/** Template functions ********************************************************/

	/**
	 * Avatar in sidebar ?
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return bool wether the admin choose to move the avatar in sidebar or not
	 */
	public function is_avatar_in_sidebar() {
		return (bool) apply_filters( 'bp_sidebar_item_nav_is_avatar_in_sidebar', ! empty( $this->widget_settings['bpsbin_avatar'] ) );
	}

	/**
	 * Member's primary nav in sidebar ?
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return bool wether the admin choose to move the member's nav in sidebar or not
	 */
	public function is_member_nav_in_sidebar() {
		return (bool) apply_filters( 'bp_sidebar_item_nav_is_member_nav_in_sidebar', ! empty( $this->widget_settings['bpsbin_member'] ) );
	}

	/**
	 * Group's primary nav in sidebar ?
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return bool wether the admin choose to move the group's nav in sidebar or not
	 */
	public function is_group_nav_in_sidebar() {
		return (bool) apply_filters( 'bp_sidebar_item_nav_is_group_nav_in_sidebar', ! empty( $this->widget_settings['bpsbin_group'] ) );
	}

	/**
	 * Output the widget's title
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return string the widget title
	 */
	public function the_widget_title() {
		echo apply_filters( 'bp_sidebar_item_nav_the_widget_title', $this->widget_title );
	}

	/**
	 * Output the group's avatar
	 *
	 * This is to avoid touching the groups template global
	 *
	 * @package BP Sidebar Item Nav
	 * @since   1.0
	 *
	 * @return string the group's avatar
	 */
	public function the_group_avatar( $group = null ) {
		if ( ! is_a( $group, 'BP_GROUPS_GROUP' ) ) {
			return false;
		}

		// This avoids to manipulate groups_template global
		$group_avatar = bp_core_fetch_avatar( array(
			'item_id'    => $group->id,
			'title'      => $group->name,
			'avatar_dir' => 'group-avatars',
			'object'     => 'group',
			'type'       => 'full',
			'alt'        => sprintf( __( 'Group logo of %s', 'bp-sidebar-item-nav' ), $group->name ),
			'class'      => 'avatar',
		) );

		if ( ! empty( $group_avatar ) ) {
			echo $group_avatar;
		}
	}
}

// Let's start !
function bp_sidebar_item_nav_loader() {
	return BP_Sidebar_Item_Nav_Loader::start();
}
add_action( 'bp_include', 'bp_sidebar_item_nav_loader', 10 );

endif;
