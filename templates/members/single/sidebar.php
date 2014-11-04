<?php
/**
 * Sidebar template
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

if ( bp_sidebar_item_nav_loader()->is_avatar_in_sidebar() ) : ?>

	<div id="item-header-avatar">
		<a href="<?php bp_displayed_user_link(); ?>">

			<?php bp_displayed_user_avatar( 'type=full' ); ?>

		</a>
	</div><!-- #item-header-avatar -->

	<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
		<h2 class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></h2>
	<?php endif; ?>

<?php endif ; ?>

<?php if ( bp_sidebar_item_nav_loader()->widget_title ) :

	bp_sidebar_item_nav_loader()->the_widget_title();

endif ;?>

<div id="item-nav">
	<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
		<ul>

			<?php  bp_get_displayed_user_nav(); ?>

			<?php do_action( 'bp_member_options_nav' ); ?>

		</ul>
	</div>
</div>
