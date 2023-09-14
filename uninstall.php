<?php
/**
 * Fired when the plugin is uninstalled.
 * 
 * @package Digital Impact
 */

// If uninstall is not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit;
}

// Delete the custom meta data associated with each post
$args = array(
  'post_type' => 'any',
  'posts_per_page' => -1,
  'post_status' => 'any',
  'meta_query' => array(
    array(
      'key' => '_digital_impact_analysis_results',
    ),
  ),
);

$posts = get_posts( $args );

foreach ( $posts as $post ) {
  delete_post_meta( $post->ID, '_digital_impact_analysis_results' );
}

// Delete any additional data associated with the plugin
// ...

// Delete any options or settings stored by the plugin
// ...

// Delete any database tables created by the plugin
// ...

// Delete any additional files or directories created by the plugin
// ...

?>
