<?php
/**
 * Sidebar template
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

if ( bp_sidebar_item_nav_loader()->is_avatar_in_sidebar() ) :

	$group = groups_get_current_group(); ?>

	<div id="item-header-avatar">
		<a href="<?php bp_group_permalink( $group ); ?>" title="<?php bp_group_name( $group ); ?>">

			<?php bp_sidebar_item_nav_loader()->the_group_avatar( $group ); ?>

		</a>
	</div><!-- #item-header-avatar -->

<?php endif ; ?>

<?php if ( bp_sidebar_item_nav_loader()->widget_title ) :

	bp_sidebar_item_nav_loader()->the_widget_title();

endif ;?>

<div id="item-nav">
	<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
		<ul>

			<?php bp_get_options_nav(); ?>

			<?php do_action( 'bp_group_options_nav' ); ?>

		</ul>
	</div>
</div>
