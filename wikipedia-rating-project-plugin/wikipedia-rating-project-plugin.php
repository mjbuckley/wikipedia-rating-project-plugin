<?php
/*
Plugin Name: Wikipedia Rating Project Plugin
Plugin URI:
Description: A plugin to rate Wikipedia pages in WordPress.
Version: 0.1
Author: Michael Buckley
Author URI:
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
GPLv2 info goes here.
*/


require_once( plugin_dir_path( __FILE__ ) . 'includes/wrp_wiki_test.php' );


// Register custom post type for reviews
function wrp_create_review_post_type() {
  $labels = array( 
    'name' => 'Reviews',
    'singular_name' => 'Review',
    'add_new' => 'New Review',
    'add_new_item' => 'Add New Review',
    'edit_item' => 'Edit Review',
    'new_item' => 'New Review',
    'view_item' => 'View Review',
    'search_items' => 'Search Reviews',
    'not_found' =>  'No Reviews Found',
    'not_found_in_trash' => 'No Reviews Found In Trash',
  );
  $args = array(
    'labels' => $labels,
    'has_archive' => true,
    'public' => true,
    'query_var' => 'wiki_reviews',
    'rewrite' => array( 'slug' => 'reviews', ),
    'supports' => array(
      'editor',
      'author',
    ),
    'taxonomies' => array(
      'wiki_title',
      'wiki_rating',
      'wiki_disciplines',
      'wiki_pageid',
    ),
    // Below is needed for the custom wiki_reviewer role
    'capability_type' => '',
    'capabilities' => array(
      'edit_post' => 'edit_wrp_review',
      'read_post' => 'read_wrp_review',
      'delete_post' => 'delete_wrp_review',
      'edit_posts' => 'edit_wrp_reviews',
      'edit_others_posts' => 'edit_others_wrp_reviews',
      'publish_posts' => 'publish_wrp_reviews',
      'read_private_posts' => 'read_private_wrp_reviews',
      'read' => 'read_wrp_reviews',
      'delete_posts' => 'delete_wrp_reviews',
      'delete_private_posts' => 'delete_private_wrp_reviews',
      'delete_published_posts' => 'delete_published_wrp_reviews',
      'delete_others_posts' => 'delete_others_wrp_reviews',
      'edit_private_posts' => 'edit_private_wrp_reviews',
      'edit_published_posts' => 'edit_published_wrp_reviews',
      'create_posts' => 'create_wrp_reviews',
    ),
    'map_meta_cap' => true,
  );
  register_post_type( 'wrp_review', $args );
}
add_action( 'init', 'wrp_create_review_post_type' );


// Register custom taxonomies
function wrp_create_taxonomies() {

  // wiki_title taxonomy
  $labels = array(
    'name' => 'Wikipedia Article Title',
    'singular_name' => 'Wikipedia Article Title',
    'search_items' => 'Search Wikipedia Article Titles',
    'all_items' => 'All Wikipedia Article Titles',
    'parent_item' => 'Parent Wikipedia Article Title',
    'parent_item_colon' => 'Parent Wikipedia Article Title:',
    'edit_item'  => 'Edit Wikipedia Article Title', 
    'update_item' => 'Update Wikipedia Article Title',
    'add_new_item' => 'Add New Wikipedia Article Title',
    'new_item_name' => 'New Wikipedia Article Title',
    'separate_items_with_commas' => 'Separate titles with commas',
    'menu_name' => 'Wikipedia Article Title',
  );
  register_taxonomy( 'wiki_title', 'wrp_review', array(
    'hierarchical' => false,
    'meta_box_cb' => false,
    'labels' => $labels,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'titles' ),
    'show_admin_column' => true,
    )
  );

  // wiki_rating taxonomy
  $labels = array(
    'name' => 'Rating',
    'singular_name' => 'Rating',
    'search_items' => 'Search Ratings',
    'all_items' => 'All Ratings',
    'parent_item' => 'Parent Rating',
    'parent_item_colon' => 'Parent Rating:',
    'edit_item'  => 'Edit Rating', 
    'update_item' => 'Update Rating',
    'add_new_item' => 'Add New Rating',
    'new_item_name' => 'New Rating',
    'separate_items_with_commas' => 'Separate ratings with commas',
    'menu_name' => 'Rating',
  );
  register_taxonomy( 'wiki_rating', 'wrp_review', array(
    'hierarchical' => false,
    'meta_box_cb' => false,
    'labels' => $labels,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'ratings' ),
    'show_admin_column' => true,
    ) 
  );

  // wiki_disciplines taxonomy
  $labels = array(
    'name' => 'Disciplines',
    'singular_name' => 'Discipline',
    'search_items' => 'Search Disciplines',
    'all_items' => 'All Disciplines',
    'parent_item' => 'Parent Discipline',
    'parent_item_colon' => 'Parent Discipline:',
    'edit_item'  => 'Edit Discipline', 
    'update_item' => 'Update Discipline',
    'add_new_item' => 'Add New Discipline',
    'new_item_name' => 'New Discipline',
    'separate_items_with_commas' => 'Separate Disciplines with commas',
    'menu_name' => 'Disciplines',
  );
  register_taxonomy( 'wiki_disciplines', 'wrp_review', array(
    'hierarchical' => false,
    'meta_box_cb' => false,
    'labels' => $labels,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'disciplines' ),
    'show_admin_column' => true,
    )
  );

  // wiki_pageid taxonomy: currently not in use but can imagine wanting in the future.  Will be added in the background
  // by a save_post hook, not by the user.
  register_taxonomy(
  'wiki_pageid',
  'wrp_review',
  array(
    'label' => 'wiki_pageid',
    'public' => false,
    'rewrite' => false,
    'hierarchical' => false,
    )
  );

  // Prepopulate wiki_rating taxonomy with rating terms.
  wp_insert_term( 'A', 'wiki_rating' );
  wp_insert_term( 'B', 'wiki_rating' );
  wp_insert_term( 'C', 'wiki_rating' );
  wp_insert_term( 'Start', 'wiki_rating' );
  wp_insert_term( 'Stub', 'wiki_rating' );
}
add_action( 'init', 'wrp_create_taxonomies', 0 );


// Add custom meta box for adding wiki_title, wiki_lastrevid, and wiki_rating
function wrp_add_page_info_meta_box() {
  add_meta_box(
    'wrp_wiki_rating_meta_box',
    'Wikipedia Article Information:',
    'wrp_create_page_info_meta_box',
    'wrp_review',
    'normal',
    'high'
  );
}
add_action( 'add_meta_boxes', 'wrp_add_page_info_meta_box' );


// Display code for the Wikipedia page info meta box (page title, lastrevid, and rating)
function wrp_create_page_info_meta_box( $post ) {

  // Get currently saved values, if they exist

  // wiki_title and post_title should be the same, but it might be possible that in rare instances they could be slightly different or that
  // post_title could be empty (reverted back to draft and resaved), so I wiki_title.
  $saved_titles = get_the_terms( $post->ID, 'wiki_title' ); // returns false if post doesn't exists or doesn't contain the term
  $saved_title = $saved_titles ? array_pop( $saved_titles ) : false;

  // The true parameter in get_post_meta() means that only the first value is returned (although there
  // should only be one), and it returns value as a string instead of as an array. If key (lastrevid) does
  // not exist, then an empty string will be returned.
  $saved_lastrevid = get_post_meta( $post->ID, 'lastrevid', true );

  // hide_empty set to 0 ensures that ratings are shown even if they haven't been used yet.
  $rating_terms = get_terms( 'wiki_rating', array( 'hide_empty' => 0 ) );


  // add nonce for security
  wp_nonce_field( 'wrp_meta_box', 'wrp_meta_box_nonce' ); ?>

  <div>
    <label for="meta_box_titile">Wikipedia Article Title</label>
    <br />
    <input type="text" name="wiki_title" id="meta_box_title" value="<?php if ( $saved_title ){ echo esc_attr( $saved_title->name ); } ?>" />
  </div>

  <div>
    <label for="meta_box_lastrevid">Lastrevid (leave blank to grab current)</label>
    <br />
    <input type="text" name="lastrevid" id="meta_box_lastrevid" value="<?php echo esc_attr( $saved_lastrevid ); ?>" />
  </div>

  <div>
    <label for="meta_box_rating">Rating</label>
    <select name="wiki_rating" id="meta_box_rating">
    <?php foreach ($rating_terms as $rating_term) { // loop through each rating term and add to drop down list ?>
      <option value="<?php echo esc_attr( $rating_term->name ); ?>"<?php if ( has_term( $rating_term->name, 'wiki_rating' ) ){ echo " selected"; } ?>><?php echo esc_html( $rating_term->name ); ?></option>
    <?php } ?> <!-- end foreach -->
    </select>
  </div>

<?php } // End wrp_create_page_info_meta_box()


// Add custom meta box for adding wiki_disciplines
function wrp_add_disciplines_meta_boxes() {
  add_meta_box(
    'wrp_wiki_displines_meta_box',
    'Disciplines',
    'wrp_create_wiki_disciplines_meta_box',
    'wrp_review',
    'normal',
    'default'
  );
}
add_action( 'add_meta_boxes', 'wrp_add_disciplines_meta_boxes' );


// Display code for displaying discipline meta box
function wrp_create_wiki_disciplines_meta_box( $post ) {

  // Code below is used to create an array ($save_disciplines_array) of term ids for each wiki_disciplines currently associated with the post.
  $saved_disciplines = get_the_terms( $post->ID, 'wiki_disciplines' ); // Array of objects of each wiki_disciplines currently associated with this post_id.
  $saved_disciplines_array = array();
  if ( $saved_disciplines && ! is_wp_error( $saved_disciplines ) ) {
    foreach ( $saved_disciplines as $saved_discipline ) {
      $saved_disciplines_array[] = $saved_discipline->term_id;
    }
  } ?>

  <div>
    <fieldset>
      <ul>
        <?php
        // Below code is used to loop through an array of all wiki_disciplines term objects, outputting a checkbox for each term, giving each
        // check box a unique css id, a value equal to the term's term_id, and checking the box if it is currently associtated with the post.
        $current_disciplines = get_terms( 'wiki_disciplines', array( 'hide_empty' => 0 ) ); // Array of objects of all wiki_disciplines
        foreach ( $current_disciplines as $current_discipline ) { // Loop through all disciplies and output as a checkbox
          $discipline_name = esc_attr( $current_discipline->name );
          $discipline_term_id = (int) $current_discipline->term_id; // Was outputting as string without (int)
          $discipline_css_id = 'discipline_' . $discipline_term_id;
          ?>
          <li>
            <input type="checkbox" name="disciplines[]" value="<?php echo $discipline_term_id; ?>" id="<?php echo $discipline_css_id; ?>"<?php if ( in_array( $discipline_term_id, $saved_disciplines_array ) ) { echo " checked"; } ?> />
            <label for="<?php echo $discipline_css_id; ?>"><?php echo $discipline_name; ?></label>
          </li>
        <?php } ?> <!-- End foreach -->
      </ul>
    </fieldset>
  </div>

<?php } // End wrp_create_wiki_disciplines_meta_box()


// Admin notice functions.

add_action( 'admin_notices', 'wrp_custom_notices' );

// Checks for presence of the my_message query arg, and if present displays the message associated with it.
function wrp_custom_notices() {
  if ( ! isset( $_GET['my_message'] ) ) {
    return;
  } else {
    $message_value = $_GET['my_message'];
    $message_check = 'success';

    // If the message contains 'success', create a link to the rated Wikipedia page
    if ( strpos( $message_value, $message_check ) !== false ) {

      $title = get_the_title();
      $encode_title = rawurlencode( $title );
      $lastrevid = get_post_meta( get_the_ID(), 'lastrevid', true );
      $lastrevid_link = 'http://en.wikipedia.org/w/index.php?title=' . $encode_title . '&oldid=' . $lastrevid;

      // Check post_status and create custom intro message depending on whether review is draft, pending, or published.
      $post_status = get_post_status();
      switch ( $post_status ) {
        case 'publish':
          $permalink = get_post_permalink();
          $intro = sprintf( '<p>Your review has been published.<a href="%s">%s</a></p>', esc_url( $permalink ), '  View the review.' );
          break;

        case 'pending':
          $intro = '<p>Your review has been submitted for review.</p>';
          break;

        case 'draft':
          $intro = '<p>Your review has been saved as a draft.</p>';
          break;
        
        default:
          $intro = false;
          return;
      }
    }

    // Determine custom error/success message
    // Some cases have the same message, but they are brought about by different situations.  Kept seperate for debugging purposes.
    switch ( $message_value ) {
      case 'error1':
        $message = 'There is no Wikipedia article with the title that you entered.  Please double check the spelling and capitalization and try again.';
        $class = 'error';
        break;
      case 'error2':
        $message = 'There was an error communicating with Wikipedia.  Please trying submitting again later.';
        $class = 'error';
        break;
      case 'error3':
        $message = 'The lastrevid that you entered does not exist.  Please recheck the number and try again.';
        $class = 'error';
        break;
      case 'error4':
        $message = 'The lastrevid and title that you entered do not match.  Please recheck the number and title and try again.';
        $class = 'error';
        break;
      case 'error5':
        $message = 'The lastrevid that you entered points to a redirect page and cannot be saved.';
        $class = 'error';
        break;
      case 'error6':
        $message = 'There was an error communicating with Wikipedia.  Please trying submitting again later.';
        $class = 'error';
        break;
      case 'error7':
        $message = 'The Wikipedia article title and/or the rating is not filled out.  Please be sure both fields are complete and try again.';
        $class = 'error';
        break;
      case 'error8':
        $message = 'The rating you submited is invalid.  Please select from one of the official ratings.';
        $class = 'error';
        break;
      case 'success1':
        $message = "The title has been changed to '{$title}.'  The lastrevid is: {$lastrevid}.  Also, this looks like it might be a disambiguation page.  Please click on the link to the reviewed Wikipedia page and verify that this is the page that you intended to review.";
        $class = 'updated';
        break;
      case 'success2':
        $message = "The title has been changed to '{$title}.'  The lastrevid is: {$lastrevid}.";
        $class = 'updated';
        break;
      case 'success3':
        $message = "The title has been saved as '{$title}.'  The lastrevid is: {$lastrevid}.  Also, this looks like it might be a disambiguation page.  Please click on the link to the reviewed Wikipedia page and verify that this is the page that you intended to review.";
        $class = 'updated';
        break;
      case 'success4':
        $message = "The title has been saved as '{$title}.'  The lastrevid is: {$lastrevid}.";
        $class = 'updated';
        break;
      case 'success5':
        $message = "The title has been changed to '{$title}.'  The lastrevid is: {$lastrevid}.  Also, this looks like it might be a disambiguation page.  Please click on the link to the reviewed Wikipedia page and verify that this is the page that you intended to review.";
        $class = 'updated';
        break;
      case 'success6':
        $message = "The title has been changed to '{$title}.'  The lastrevid is: {$lastrevid}.";
        $class = 'updated';
        break;
      case 'success7':
        $message = "The title has been saved as '{$title}.'  The lastrevid is: {$lastrevid}.  Also, this looks like it might be a disambiguation page.  Please click on the link to the reviewed Wikipedia page and verify that this is the page that you intended to review.";
        $class = 'updated';
        break;
      case 'success8':
        $message = "The title has been saved as '{$title}.'  The lastrevid is: {$lastrevid}.";
        $class = 'updated';
        break;
      default:
        $message = '' // In case something unexpected somehow got through
        break;
    } // End switch     
    ?>


    <!-- Display code for custom error message -->

    <div class="<?php echo $class ?>">
      <?php if ( $intro ) { echo $intro; } ?>
      <p><?php echo $message ?></p>
      <?php if ( $class === 'updated' ) { ?>
        <p><a href="<?php echo $lastrevid_link; ?>">Link to the reviewed Wikipedia page</a></p>
      <?php } // end if ?>
    </div>

    <?php
  }
} // End wrp_custom_notices()


// Save wiki_disciplines from disciplines meta box.  Called from within wrp_save_rating().
function wrp_save_disciplines( $post_id ) {

  if ( empty( $_POST['disciplines'] ) ) {
    wp_set_object_terms( $post_id, null, 'wiki_disciplines' ); // Clears previously saved values, if they exist.
  } else {

    // Create array ($current_disciplines_array) of all term_ids of all disciplines
    $current_disciplines = get_terms( 'wiki_disciplines', array( 'hide_empty' => 0 ) );
    $current_disciplines_array = array();
    foreach ( $current_disciplines as $current_discipline ) {
      $current_disciplines_array[] = (int) $current_discipline->term_id; // Cast as int because get_terms returns as string
    }

    // Create array ($to_save_disciplines) of all term_ids for disciplines submitted in meta box.  Verifies that each value matches a
    // current discipline before adding to array.  Prevents reviewers from adding their own disciplines.
    $submitted_disciplines = $_POST['disciplines'];
    $to_save_disciplines = array();
    foreach ( $submitted_disciplines as $submitted_discipline ) {
      $sanitized_discipline = sanitize_text_field( $submitted_discipline );
      if ( in_array( $sanitized_discipline, $current_disciplines_array ) ) { // Makes sure submited discipline matches one of the official disciplines
        $to_save_disciplines[] = (int) $submitted_discipline;
      }
    }

    // Save disciplines
    if ( empty( $to_save_disciplines ) ) {
      wp_set_object_terms( $post_id, null, 'wiki_disciplines' );
    } else {
      wp_set_object_terms( $post_id, $to_save_disciplines, 'wiki_disciplines' );
    }
  }
}


// Check if submitted rating matches one of the official ratings.  Makes sure reviewers cannot add their own rating terms.
// Returns true if rating matches an official rating, false otherwise.
function wrp_verify_rating( $post_id ) {

  // Create array ($current_ratings_array) of all current rating terms
  $current_ratings = get_terms( 'wiki_rating', array( 'hide_empty' => 0 ) );
  $current_ratings_array = array();
  foreach ( $current_ratings as $current_rating ) {
    $current_ratings_array[] = $current_rating->name;
  }

  $submitted_rating_value = sanitize_text_field( $_POST['wiki_rating'] );

  if ( in_array( $submitted_rating_value, $current_ratings_array ) ) {
    return true;
  } else {
    return false;
  }
}


// wrp_save_rating() overview:
//
// It runs validation on the custom meta data associated with the post and ensures the folowing:
// -Makes sure required fields are filled in.
// -Verifies that the required information is correct and grabs any needed information by running the wrp_wiki_check function if needed.
// -Passes on any messages/errors to the user
// -Saves meta data if information is good.
// -Does not save meta data if information is bad.  Rolls back post_status to draft (but keeps the text content of the review).
// -Sets wiki_title as post_title and also genrates a unique post_name slug based on post_title.

add_action( 'save_post','wrp_save_rating' );

function wrp_save_rating( $post_id ) {

  // Check if nonce is set and valid
  if ( ! isset( $_POST['wrp_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wrp_meta_box_nonce'], 'wrp_meta_box' ) ) {
    return;
  }


  // Don't do anything if this is an autosave
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }


  // Check the user's permissions.
  if ( ! current_user_can( 'edit_wrp_reviews', $post_id ) ) {
    return;
  }


  // Make sure that required fields (wiki_rating and wiki_title) are not empty.
  if ( ! isset( $_POST['wiki_rating'] ) || empty( $_POST['wiki_rating'] ) || ! isset( $_POST['wiki_title'] ) || empty( $_POST['wiki_title'] ) ) {

    // Required fields are missing so roll back post to draft status
    remove_action( 'save_post', 'wrp_save_rating' );
    wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
    add_action( 'save_post', 'wrp_save_rating' );

    // Pass error message to user:
    $message = 'error7';
    add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );
    


  // wiki_rating and wiki_title are not empty, so continue.
  } else {

    // Ensure submitted rating is a legit rating
    if ( ! wrp_verify_rating( $post_id ) ) {

      // Rating term is bogus.  Roll back post to draft status.
      remove_action( 'save_post', 'wrp_save_rating' );
      wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
      add_action( 'save_post', 'wrp_save_rating' );

      // Pass error message to user:
      $message = 'error8';
      add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );


    // Rating term legit, so continue validation process
    } else {

      // Compare submitted value with currently saved values (if they exist).  Save current values to $current_title and
      // $current_lastrevid (if the values exist, otherwise set them to false).
      $pre_old_title_value = get_the_terms( $post_id, 'wiki_title' );
      if ( $pre_old_title_value ) {
        $old_title_value = array_pop( $pre_old_title_value );
        $current_title = $old_title_value->name;
      } else {
        $current_title = false;
      }

      $pre_current_lastrevid = get_post_meta( $post_id, 'lastrevid', true );
      if ( empty( $pre_current_lastrevid ) ) {
        $current_lastrevid = false;
      } else {
        $current_lastrevid = $pre_current_lastrevid;
      }

      // Get submitted values.  Due to a weird WordPress quirk, magic quotes are added to $_POST values (and many other things).  This means
      // that Fool's gets changed to Fool\'s.  Remove slashes with wp_unlash() or else the wikipedia api could get the wrong info.
      // Could also mess up comparison with currently saved values.  BE SURE TO ADD BACK SLASHES WHERE WP EXPECTS THEM!
      $slashed_new_title_value = sanitize_text_field( $_POST['wiki_title'] );
      $new_title_value = wp_unslash( $slashed_new_title_value );
      $new_lastrevid = sanitize_text_field( $_POST['lastrevid'] );


      // Check if this is a new review or if new values differ from saved values.
      if ( empty( $new_lastrevid ) || ( $new_lastrevid !== $current_lastrevid ) || ( $new_title_value !== $current_title ) ) {

        // Submitted values are new or changed.  Send to Wikipedia to see if they are valid/grab additional info.
        $wiki_info = wrp_wiki_test( $new_title_value, $new_lastrevid );

        
        // Check if there was an error with wrp_wiki_test().
        if ( $wiki_info['error'] === true ) {

          
          // The user submitted bogus info, or there was an error communicating with Wikipedia.  Roll post back to draft status.
          remove_action( 'save_post', 'wrp_save_rating' );
          wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
          add_action( 'save_post', 'wrp_save_rating' );
          
          // Alert user of the specific error.
          $message = $wiki_info['message'];
          add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );


        // At this point, everything is basically good.  There may be some minor issues to pass on to user, but initiate save process.
        } else {

          // Get values to save.
          $pre_to_save_title = wp_slash( $wiki_info['title'] );
          $to_save_title = sanitize_text_field( $pre_to_save_title );
          $to_save_lastrevid = sanitize_text_field( $wiki_info['lastrevid'] );
          $pre_to_save_pageid = sanitize_text_field( $wiki_info['pageid'] );
          $to_save_pageid = "{$pre_to_save_pageid}"; // Set to string otherwise it would be considered a term id #, not a term itself
          $to_save_rating_value = sanitize_text_field( $_POST['wiki_rating'] ); // May not have changed, but no harm in resaving if it hasn't.


          // Save values.  NOTE: magic quote slashes added back because that's the expected format.
          wp_set_object_terms( $post_id, $to_save_title, 'wiki_title' );
          wp_set_object_terms( $post_id, $to_save_pageid, 'wiki_pageid' );
          wp_set_object_terms( $post_id, $to_save_rating_value, 'wiki_rating' );
          update_post_meta( $post_id ,'lastrevid' , $to_save_lastrevid );


          if ( isset( $_POST['disciplines'] ) && ! empty( $_POST['disciplines'] ) ) { // Run discipline check and save if disciplines submitted
            wrp_save_disciplines( $post_id );
          }




          // wp_update_post() calls wp_insert_post() and that ends up setting the post_name to an empty stiring if post_status is anything but
          // 'publish.'  If post_status is publish, it will run the given title through wp_unique_post_slug(), which will give it a unique
          // post_name.  I was running post_name through wp_unique_post_slug() before sending it on, but that ended up sending it through
          // the function twice and I was getting weird results.  I do need to send something, because it will likely already have a post_name
          // like auto-draft-30.


          remove_action( 'save_post', 'wrp_save_rating' );
          wp_update_post( array( 'ID' => $post_id, 'post_title' => $to_save_title, 'post_name' => $to_save_title, ) );
          add_action( 'save_post', 'wrp_save_rating' );

          // Pass along message to user and remove default WP message (it is added before the save_post hook and often doesn't
          // match what has actually happened).
          $message = $wiki_info['message'];
          add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );
          add_filter( 'redirect_post_location', function($loc) { return remove_query_arg( 'message', $loc ); } );
        }


      // submitted values the same as saved values.  Save disciplines and rating.  May have been no change, but easier to just save than
      // check for chage, and no harm in resaving old values.  Could run a check in the future.
      } else {
      
        $to_save_rating_value = sanitize_text_field( $_POST['wiki_rating'] );
        wp_set_object_terms( $post_id, $to_save_rating_value, 'wiki_rating' );

        if ( isset( $_POST['disciplines'] ) && ! empty( $_POST['disciplines'] ) ) { // Run discipline check and save is disciplines submitted
          wrp_save_disciplines( $post_id );
        }
      }
    }
  }
}


// Create new wrp_reviewer role. wrp_review capabilities are added in a seperate function ( wrp_add_review_caps() ).  NOTE: I believe that the
// register_activation_hook used for adding this role must come before the register_activation_hook adding wrp_review caps.
function wrp_add_reviewer_role() {
  remove_role( 'wrp_reviewer' );
  add_role( 'wrp_reviewer', 'Reviewer', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
    'publish_posts' => false,
    )
  );
}
register_activation_hook( __FILE__, 'wrp_add_reviewer_role' );


// Add capabilities for the for wrp_review type.  Default WP roles get the same wrp_review capabilities as their default posts
// editing capabilities.  wrp_reviewer gets wrp_review capabilities equal to contributors (but they have no post editing caps ).
function wrp_add_review_caps() {

  // Array of default WordPress roles as well as the custom wrp_reviewer role.
  $roles = array( 'administrator', 'editor', 'author', 'contributor', 'wrp_reviewer', 'subscriber' );

  // Loop through each role and add capabilities
  foreach ( $roles as $the_role ) {

    $role = get_role( $the_role );

    if ( ! is_null( $role ) ) { // Checks in cases one of the default roles has been removed

      // All roles
      $role->add_cap( 'read_wrp_reviews' );

      // All roles above subscriber
      if ( $the_role == 'administrator' || $the_role == 'editor' || $the_role == 'author' || $the_role == 'contributor' || $the_role == 'wrp_reviewer' ) {
        $role->add_cap( 'edit_wrp_reviews' );
        $role->add_cap( 'delete_wrp_reviews' );
        $role->add_cap( 'delete_published_wrp_reviews' );
        $role->add_cap( 'edit_published_wrp_reviews' );
        $role->add_cap( 'create_wrp_reviews' );
      }

      // All roles above wrp_reviewer/contributor
      if ( $the_role == 'administrator' || $the_role == 'editor' || $the_role == 'author' ) {
        $role->add_cap( 'publish_wrp_reviews' );
      }

      // All roles above author
      if ( $the_role == 'administrator' || $the_role == 'editor' ) {
        $role->add_cap( 'edit_others_wrp_reviews' );
        $role->add_cap( 'read_private_wrp_reviews' );
        $role->add_cap( 'delete_private_wrp_reviews' );
        $role->add_cap( 'delete_others_wrp_reviews' );
        $role->add_cap( 'edit_private_wrp_reviews' );
      }
    }
  }
}
register_activation_hook( __FILE__, 'wrp_add_review_caps' );


// Functions below are used to clean up the appearance of the admin menu for everyone with the 'wrp_reviewer' role.  Everything left
// untouched for other roles.  Admitedly these functions blur the line between function and content and could go in a theme's functions.php,
// but I think they make most sense here since the purpose of the plugin is to provide an incredibly simple experience for reviewers.

// If user has wrp_reviewer role, only show posts editable by that user in the admin screen.
function wrp_only_author_posts( $wp_query ) {
  $user = wp_get_current_user();
  if ( is_admin() && current_user_can( 'wrp_reviewer' ) ) {
    $wp_query->set( 'author', $user->ID );
  }
}
add_action( 'pre_get_posts', 'wrp_only_author_posts' );


// Posts that's a wrp_reviewer cannot edit were removed from their admin menu with the previous function, but the the post number counts do not
// reflect this change.  This function sets the display to 'none' for css that displays those numbers.  Also, sets display to none for the
// 'mine' tab, because in this case 'mine' and 'all' refer to same thing.  Only affects wrp_reviewer role.
function wrp_improve_reviews_tabs() {
  if ( current_user_can( 'wrp_reviewer' ) ) {
    $css  = '<style>.subsubsub a .count { display: none; }</style>';
    $css2 = '<style>.subsubsub .mine { display: none; }</style>';
    echo $css;
    echo $css2;
  }
}
add_action( 'admin_head', 'wrp_improve_reviews_tabs' );


// Remove dashboard widgets for those with wrp_reviewer role
function wrp_clean_dashboard() {
  if ( current_user_can( 'wrp_reviewer' ) ) {
    remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
  }
}
add_action( 'wp_dashboard_setup', 'wrp_clean_dashboard' );

?>