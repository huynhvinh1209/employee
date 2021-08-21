<?php
/**
 * BuddyPress - Users Cover Image Header
 *
 * @since 3.0.0
 * @version 3.0.0
 */
?>

<div id="cover-image-container">
	<div id="header-cover-image"></div>

	<div id="item-header-cover-image">
		<div id="item-header-avatar">
			<a href="<?php bp_displayed_user_link(); ?>">

				<?php bp_displayed_user_avatar( 'type=full' ); ?>

			</a>
		</div><!-- #item-header-avatar -->

		<div id="item-header-content">

			<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
				<h2 class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></h2>
			<?php endif; ?>

			<?php
			if ( function_exists( 'bp_nouveau_member_header_buttons' ) ) {
				bp_nouveau_member_header_buttons(
					array(
						'container'         => 'ul',
						'button_element'    => 'button',
						'container_classes' => array( 'member-header-actions' ),
					)
				);
			}

?>

			<?php if ( function_exists( 'bp_nouveau_member_header_buttons' ) )  bp_nouveau_member_hook( 'before', 'header_meta' ); ?>
			<?php if ( function_exists( 'bp_nouveau_member_has_meta' ) ) {?>
			<?php if ( bp_nouveau_member_has_meta() ) : ?>
				<div class="item-meta">

					<?php if ( function_exists( 'bp_nouveau_member_meta' ) ) bp_nouveau_member_meta(); ?>

				</div><!-- #item-meta -->
			<?php endif; }?>

		</div><!-- #item-header-content -->

        <?php
        if (videopro_current_user_can('channel.create')) { ?>
            <a href="#" data-toggle="modal" data-target="#videopro_user_create_channel_popup"
               class="btn-user-create-channel btn btn-default ct-gradient bt-action metadata-font font-size-1 elms-right">
                <?php echo esc_html__('Create Channel', 'videopro'); ?>
            </a>
            <?php
        } else {
            // show limit message if any
            do_action('videopro_membership_check_limited_action', get_current_user_id(), 'channel.create');
        } ?>
	</div><!-- #item-header-cover-image -->
</div><!-- #cover-image-container -->
