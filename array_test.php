<?php

function test() {
  return array( 'key1' => true, 'key2' => 'words', 'key3' => 1234 );
}

$variable = test();
$key1 = $variable['key1'];
$key2 = $variable['key2'];
$key3 = $variable['key3'];

var_dump($variable, $key1, $key2, $key3);


function wrp_admin_notice($message) { ?>
  <div class="updated">
    <p><?php $message; ?></p>
  </div>
<?php
}
add_action( 'admin_notices', 'wrp_admin_notice' );

function wrp_admin_error_notice($message) { ?>
  <div class="error">
    <p><?php $message ?></p>
  </div>
<?php
}
add_action( 'admin_notices', 'wrp_admin_error_notice' ); 

?>


<?php
function wrp_update_post_status( $post_id ){
  if ( ! wp_is_post_revision( $post_id ) ){
  
    // unhook this function so it doesn't loop infinitely
    remove_action('save_post', 'wrp_update_post_status');
  
    // update the post, which calls save_post again
    wp_update_post( $my_args );

    // re-hook this function
    add_action('save_post', 'wrp_update_post_status');
  }
}
add_action('save_post', 'wrp_update_post_status');
?>


<?php



$wpdb->update( $wpdb->posts, array("post_title" => "Modified Post Title"), array("ID" => 5), array("%s"), array("%d") );

$wpdb->update( $wpdb->posts, array("post_title" => $to_save_title), array("ID" => $post_id), array("%s"), array("%d") );

$wpdb->update( $wpdb->posts, array("post_status" => "draft"), array("ID" => $post_id), array("%s"), array("%d") );

// Set post title to value of $wiki_title
  $my_post = array(
      'ID'           => $post_id,
      'post_title'   => $wiki_title
  );

// Update the post into the database
  wp_update_post( $my_post );



// Change post status to draft in case of validation failure
  $my_post = array(
      'ID'           => $post_id,
      'post_status'   => 'draft'
  );

// Update the post into the database
  wp_update_post( $my_post );


?>