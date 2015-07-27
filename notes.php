<?php

// NOTES:

// useful for debugging when you cannont print to screen: error_log(print_r($tvariable_name, TRUE)).  Then run
// "tail php_error.log" from the MAMP folder in the terminal.

// Need to sanitize results from wiki api?

// Learn and conform to WP coding style guidelines

// ADD An uninstall option to the plugin.

// Improve error/success messages.  Remove default WP message where needed.

// NOTE: false is the same as an empty string.  Remember this when testing for things and setting variable to false.

// way to include files:
// require_once( plugin_dir_path( __FILE__ ) . 'folder/filename.php') or include( plugin_dir_path(__FILE__) . '/includes/wrp_wiki_test.php' );
//

// Better understand query_var/figure out pagination (probably related topics)

// I believe sanitize_title() is the right choice for clening title for post_name, but consider sanitize_title_with_dashes()
// if something doesn't work properly.

// With wikipedia changing to https only, do I need to change anything with the api requiest address?

// Right now the wiki_check is skipped on autosave, which basically makes sense, but I could set up a smart one that never changes a title,
// but which does save stuff if nothing has changed.

// I use an anonymous function I found and modified to add a query arg that contains key to a custom admin message.  The function takes
// a $loc argument.  However, I never pass one to the function, and I don't think it gets passed any other way.  I think it is just an
// optional redirect location that I don't use.  Make sure my understanding is correct, and if so, remove it.

// There should probably be a check on the wiki test to make sure at least a title is included.

// do same verification for ratings as I did for disciplines

// CHANGING POST UPDATED MESSAGE: I tried the below code.  Two weird things.  First, when checking the logs $post_type is being set at "post"
// for both posts and wrp_review.  In the db things are correct though.  Not sure if it's an issue with the function I'm using, or how I'm
// checking the id, but something is off.  Also, even though $post_type != wrp_review, the if statement still gets evaluated and the message
// removed.  Not sure about this.  Should I be using something different to compare strings (like string compare)?

add_filter( 'post_updated_messages', 'wrp_post_published' );
function wrp_post_published( $messages ) {
  $post_id = get_the_ID();
  $post_type = get_post_type( $post_id );
  if ( $post_type === 'wrp_review' ) {
    unset($messages['post'][6]);
    return $messages;
  }
  error_log(print_r($post_type, TRUE));
}


// OLD display code for title section of meta box
  <!-- get_the_terms() returns false if post doesn't exists or doesn't contain the term, otherwise returns an array of term objects.
  There should only be one title, but array pop ensures this. -->
  <div>
    <?php
      $saved_titles = get_the_terms( $post->ID, 'wiki_title');
      $saved_title = $saved_titles ? array_pop($saved_titles) : false;
    ?>
    <label for="meta_box_titile">Wikipedia Article Title</label>
    <br />
    <input type="text" name="wiki_title" id="meta_box_title" value="<?php if ($saved_title){echo esc_attr( $saved_title->name );} ?>" />
  </div>

?>