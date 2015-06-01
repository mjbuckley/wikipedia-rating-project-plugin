<?php
/*
Plugin Name: Wikipedia Review Plugin
Plugin URI:
Description: A plugin to rate Wikipedia pages in WordPress.
Version: 0.1
Author:
Author URI:
License: GPLv2
*/

/*
GPLv2 info goes here.
*/


// NOTES:
// ADD An uninstall option to the plugin.


// Register custom post type for reviews
function wrp_review_create_post_type() {
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
      'revisions',
    ),
    'taxonomies' => array(
      'wiki_title',
      'wiki_rating',
      'wiki_disciplines',
      'wiki_pageid',
    ), 
  );
  register_post_type( 'wrp_review', $args );
} 

add_action( 'init', 'wrp_review_create_post_type' );



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
    'labels' => $labels,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'disciplines' ),
    'show_admin_column' => true,
    )
  );

  // wiki_pageid taxonomy (set to private, for possible future internal use, will be added in background, not by user)
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
  wp_insert_term('A', 'wiki_rating');
  wp_insert_term('B', 'wiki_rating');
  wp_insert_term('C', 'wiki_rating');
  wp_insert_term('Start', 'wiki_rating');
  wp_insert_term('Stub', 'wiki_rating');
}

add_action( 'init', 'wrp_create_taxonomies', 0 );



// Add custom meta box adding review info
add_action( 'add_meta_boxes', 'wrp_add_meta_boxes' );
function wrp_add_meta_boxes() {
  add_meta_box(
    'wrp_wiki_rating_metabox',
    'Wikipedia Article Information:',
    'wrp_create_wiki_rating_metabox',
    'wrp_review',
    'normal',
    'high'
  );
}

// Display code for adding review info (title, rating, lastrevid)
function wrp_create_wiki_rating_metabox( $post ) {
  
  // add nonce for security
  wp_nonce_field( 'wrp_meta_box', 'wrp_meta_box_nonce' ); ?>

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


  <!-- The true parameter in get_post_meta() means that only the first value is returned (although there
    should only be one), and it returns value as a string instead of as an array. If key (lastrevid) does
    not exist, then an empty string will be returned. -->
  <div>
    <?php $saved_lastrevid = get_post_meta( $post->ID, 'lastrevid', true ); ?>
    <label for="meta_box_lastrevid">Lastrevid (leave blank to grab current)</label>
    <br />
    <input type="text" name="lastrevid" id="meta_box_lastrevid" value="<?php echo esc_attr( $saved_lastrevid ); ?>" />
  </div>

  <div>
    <label for="meta_box_rating">Rating</label>
    <select name="wiki_rating" id="meta_box_rating">
    <?php
    // hide_empty set to 0 ensures that ratings are shown even if they haven't been used yet.
    $rating_terms = get_terms( 'wiki_rating', array( 'hide_empty' => 0 ) );

    foreach($rating_terms as $rating_term) { ?>

    <option value="<?php echo esc_attr( $rating_term->name ); ?>"<?php if ( has_term($rating_term->name, 'wiki_rating') ){echo " selected";} ?>><?php echo esc_html( $rating_term->name ); ?></option>
    <?php } ?> <!-- end foreach -->
    </select>
  </div>
<?php }


// Admin notice functions.

add_action( 'admin_notices', 'my_notices' );

// Checks for presence of custom message and displays according
function my_notices() {
  if ( ! isset( $_GET['my_message'] ) ) {
    return;
  } else {

    //sanitize this?
    $message_value = $_GET['my_message'];

    switch($message_value) {
      case "error1":
        $message = "There is no Wikipedia article with the title that you entered.  Please check the title and try again.";
        break;
      case "error2":
        $message = "There was an error communicating with Wikipedia.  The text of your review has been saved as a draft.  Please trying submitting again later.";
        break;
      case "error3":
        $message = "The lastrevid that you entered does not exist.  Please recheck the number and try again.";
        break;
      case "error4":
        $message = "The title associated with the lastrevid does not match the given title";
        break;
      case "error5":
        $message = "The lastrevid points to a redirect page and cannot be saved.";
        break;
      case "error6":
      // Same as error2, but brought about by different situation.  Keep separate for debugging purposes.
        $message = "There was an error communicating with Wikipedia.  The text of your review has been saved as a draft.  Please trying submitting again later.";
        break;
      case "error7":
        $message = "The Wikipedia article title and/or the rating are not filled out.  Please complete those fields and try again.";
        break;    
      case "success1":
        //$message = "Title has been changed from '{$test_title}' to '{$new_title}.'  Lastrevid is: {$lastrevid}.  Also, This looks like it might be a disambiguation page.";
        $message = "temp message";
        break;
      case "success2":
        //$message = "Title has been changed from '{$test_title}' to '{$new_title}.'  Lastrevid is: {$lastrevid}.";
        $message = "temp message";
        break;
      case "success3":
        //$message = "This looks like it might be a disambiguation page.  Lastrevid is: {$lastrevid}.";
        $message = "temp message";
        break;
      case "success4":
        //$message = "Lastrevid is: {$lastrevid}.";
        $message = "temp message";
        break;
      case "success5":
        //$message = "This looks like it might be a disambiguation page.  Title has been changed to from '{$test_title}' to '{$title}'.";
        $message = "temp message";
        break;
      case "success6":
        //$message = "Title has been changed from '{$test_title}' to '{$title}'.";
        $message = "temp message";
        break;
      case "success7":
        $message = "This might be a disambiguation page.";
        break;
      case "success8":
        $message = "Everything's good";
        break;
    } // End switch     
    ?>

    <div class="updated">
      <p><?php echo $message; ?></p>
    </div>

    <?php
  }
} // End my_notices()


// NOTE! Do I need to (can I even in this context) escape the add_query_arg.  Important given recent security issue around this.
// Also, adapted from found code.  Uncertain about use of $loc (but works).

// function my_message($loc) {
//   return add_query_arg( 'my_message', 123, $loc );
// }


// START WRP_WIKI_TEST EVENTUALLY PUT IN INCLUDED SECTION

function wrp_wiki_test( $test_title, $test_lastrevid ) {

  // No lastrevid given so grab the current one

  if (empty($test_lastrevid)) {
    $title_base = 'http://en.wikipedia.org/w/api.php?format=json&action=query&titles=';
    $title_ending = '&prop=info|categories&clcategories=category:%20disambiguation%20pages&redirects';
    $encode_title = rawurlencode($test_title);
    $title_url = $title_base . $encode_title . $title_ending; // Wikipedia API request
    $title_response = wp_remote_get($title_url); // Raw response from Wikipedia API
    if( is_array($title_response) ) {  // Verify response is in form we expect
      $body = $title_response['body']; // Strip response header
      $title_decoded = json_decode($body, true); // Convert to a PHP useable JSON form
      $pre_info = $title_decoded["query"]["pages"];
      $pre_pages_value = array_keys($pre_info);
      $pages_value = $pre_pages_value[0];

      // Make sure that the user supplied title exists.  A $pages_value of -1 means there is no Wikipedia article with that title.

      if ($pages_value == -1) {
        $message = "error1";
        // "There is no Wikipedia article with the title that you entered.  Please check the title and try again."
        return array( 'error' => true, 'message' => $message );
      }

      // User supplied title exists.  Now check if title is a disambiguation or redirect page, or if it needs to be normalized.
      // Also grab lastrevid.

      else {
        $lastrevid = $pre_info[$pages_value]["lastrevid"];
        $redirect_normalization_test = $title_decoded['query'];
        // Checks if given title is a redirect page. API will grab target of redirect, but will want to fix title.
        $redirect = array_key_exists('redirects', $redirect_normalization_test) ? true : false;
        $disambiguation_test = $pre_info[$pages_value];
        // Here is how the disambiguation test works: The API request is set to check if the page belongs to the category
        // 'Disambiguation pages'.  If it does, the categories key will be present in the response, otherwise it won't.
        $disambiguation = array_key_exists('categories', $disambiguation_test) ? true : false;
        $normalization = array_key_exists('normalized', $redirect_normalization_test) ? true : false; // Check if page title has been normalized.
        $pageid = $pre_info[$pages_value]["pageid"];

        if ($disambiguation && ($redirect || $normalization)) {
          // Could be a disambiguation page.  Title changed as a result of normalization or redirect.  Save but alert user.
          $new_title = $disambiguation_test['title'];
          $message = "success1";
          // "Title has been changed from '{$test_title}' to '{$new_title}.'  Lastrevid is: {$lastrevid}.  Also, This looks like it might be a disambiguation page."
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );
        }
        elseif ($redirect || $normalization) {
          // Title changed as a result of normalization or redirect.
          $new_title = $disambiguation_test['title'];
          $message = "success2";
          // "Title has been changed from '{$test_title}' to '{$new_title}.'  Lastrevid is: {$lastrevid}."
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );
        }
        elseif ($disambiguation) {
          // Could be a disambiguation page.  Save but alert user.
          $message = "success3";
          // "This looks like it might be a disambiguation page.  Lastrevid is: {$lastrevid}."
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
          
        }
        else {
          // Everything checks out perfectly.
          $message = "success4";
          // "Lastrevid is: {$lastrevid}."
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
        }
      }
    } else {
      // Unexpected type of response from Wikipedia API.  Prompt user to try again later.
      $message = "error2";
      // "There was an error communicating with Wikipedia.  The text of your review has been saved as a draft.  Please trying submitting again later."
      return array( 'error' => true, 'message' => $message );
    }


  // Lastrevid has been given, so check that it is valid and that it matches the given title.
 
  } else {
    $lastrevid_base = 'http://en.wikipedia.org/w/api.php?action=query&format=json&revids=';
    $lastrevid_ending = '&prop=info|categories&clcategories=category:%20disambiguation%20pages';
    $encode_lastrevid = rawurlencode($test_lastrevid);
    $lastrevid_url = $lastrevid_base . $encode_lastrevid . $lastrevid_ending;
    $lastrevid_response = wp_remote_get($lastrevid_url);
    if( is_array($lastrevid_response) ) {  // Verify response is in form we expect
      $body = $lastrevid_response['body']; // Strip response header
      $lastrevid_decoded = json_decode($body, true); // Convert to a PHP useable JSON form
      $query_array = $lastrevid_decoded["query"];

      // Make sure lastrevid is real.  A bad lastrevid has no "pages" key.

      if (!array_key_exists("pages", $query_array)) { // Lastrevid doesn't exist
        $message = "error3";
        // "The lastrevid that you entered does not exist.  Please recheck the number and try again."
        return array( 'error' => true, 'message' => $message );
      }

      // Lastrevid exists

      else {
        $pre_info = $query_array["pages"];
        // current() returns the value of the array element that's currently being pointed to by the internal pointer.
        // Since responses should only have one 1 value here, this works to grabs the array that we need.
        $info = current($pre_info);
        $title = $info["title"];

        // Verify that entered title matches the title the API has associated with the lastrevid
        if (strtolower($test_title) !== strtolower($title)) {

          // Title for the lastrevid does not equal title supplied by user
          $message = "error4";
          // "The title associated with the lastrevid does not match the given title"
          return array( 'error' => true, 'message' => $message );
        }

        // Lastrevid points to a redirect page.  Redirect page's lastrevids do not change their lastrevid to mirror changes in their target page.
        // So there is no way to know what edit the user intends to being reviewing.
        elseif (array_key_exists("redirect", $info)) {
          $message = "error5";
          // "The lastrevid points to a redirect page and cannot be saved."
          return array( 'error' => true, 'message' => $message );
        }

        // All is basically good.  Check for differences in capitalization, the possiblitiy of being a disambiguation page, and grab pageid.
        else {
          $pageid = $info["pageid"];

          // Titles match but differ in capitalization 
          if ($test_title !== $title) { 

            // Probaly a disambiguation page.  Title capitalization has been changed.  Save, but alert user.
            if (array_key_exists("categories", $info)) {
              $message = "success5";
              // "This looks like it might be a disambiguation page.  Title has been changed to from '{$test_title}' to '{$title}'."
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );
            }

            // Titles differ in capitalization, but all else good.  Save, but alert user.
            else {
              $message = "success6";
              // "Title has been changed from '{$test_title}' to '{$title}'."
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );
            }
          }

          // Titles are a perfect match.
          else {

            // Probably a disambiguation page.  Save, but alert user.
            if (array_key_exists("categories", $info)) {
              $message = "success7";
              // "This might be a disambiguation page."
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
            }

            // Everything matches up perfectly.
            else {
              $message = "success8";
              // "Everything's good"
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
            }
          }
        }
      }
    } else {
      // Unexpected type of response from Wikipedia API.  Prompt user to try again later.
      $message = "error6";
      // "There was an error communicating with Wikipedia.  The text of your review has been saved as a draft.  Please trying submitting again later."
      return array( 'error' => true, 'message' => $message );
    }
  }
} // End of wrp_wiki_test()


// save the meta box data


// NOTES:
// -Not sure that user permissions are set up correctly.  Probably not to reference the cutom post type somewhere.
// Although I don't think what I have would prevent people from editing posts.  It just might be too permissive.
// -I think chceking for presence of wrp_meta_box_nonce at the start is enough to make this not run on other types
// of posts/pages, but be sure.
// Function doesn't run on autosave, but what about drafts/other?
// DEAL WITH INFINITE LOOP issue with wp_update_post
// BE SURE TO incorporate code I have in array_test.php file.
// DON'T FORGET about wiki_disciplines and how they are/should be handled.
// RETURN A LINK TO the wikipedia page in the admin notice after initial save.
// FIGURE OUT HOW to get wiki_info['message'] passed into admin notice function.  Use anonymous function?
// ADD ADMIN notice of success to wiki_rating save at end of code?


// Function Info:
// wrp_save_rating is a function that hooks on to the save post action and runs immediately after a post of the wrp_review post type
// has been saved.  It runs validation on the custom meta data associated with the post.  It does the following:
// -Makes sure required fields are filled in.
// -Verifie that the required information is correct and grabs any needed information by running the wrp_wiki_check function if needed.
// -Passes on any messages/errors to the user
// -Saves meta data if information is good.
// -Does not save meta data if information is bad.  Rolls back post_status to draft, but keeps the text content of the review.

add_action( 'save_post','wrp_save_rating' );

function wrp_save_rating( $post_id ) {

  // Basic security/capability checks

  // Check if nonce is set and valid
  if( !isset( $_POST['wrp_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['wrp_meta_box_nonce'], 'wrp_meta_box' ) ) return;

  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  // Check the user's permissions.
  if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) ) {
      return;
    }
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
      return;
    }
  }


  // Sanity check that wiki_rating, wiki_title, and wiki_lastrevid are all part of submitted form (even if they aren't set).
  // Make sure wiki_rating or wiki_title values on submitted form are not empty.

  if ( !isset($_POST['wiki_rating']) || empty($_POST['wiki_rating']) || !isset($_POST['wiki_title']) || empty($_POST['wiki_title']) ) {
    // If true than either something is wrong with form or a required field is missing.  Return error to user.
    
    $message = "error7";
    // "The Wikipedia article title and/or the rating are not filled out.  Please complete those fields and try again."
    
    add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );


  // wiki_rating or wiki_title are not empty.  Now see if wiki_title and wiki_lastrevid have a currently saved value.
  // Save current values to $current_title and $current_lastrevid if the values exist, otherwise set them to false.

  } else {
    $pre_old_title_value = get_the_terms( $post_id, 'wiki_title');
    if ($pre_old_title_value) {
      $old_title_value = array_pop($pre_old_title_value);
      $current_title = $old_title_value->name;
    } else {
      $current_title = false;
    }

    $pre_current_lastrevid = get_post_meta( $post_id, 'lastrevid', true );
    if (empty($pre_current_lastrevid)) {
      $current_lastrevid = false;
    } else {
      $current_lastrevid = $pre_current_lastrevid;
    }


    // Now get the wiki_title and wiki_lastrevid values submitted in the form

    $new_title_value = sanitize_text_field( $_POST['wiki_title'] );
    $new_lastrevid = sanitize_text_field( $_POST['lastrevid'] );


    // Use old and new values to see if wrp_wiki_test needs to be run.  It should be run if this is the initial save, or if the new values have
    // changed from old values.  

    if (empty($new_lastrevid) || ($new_lastrevid !== $current_lastrevid) || ($new_title_value !== $current_title)) {
      // If true, this is either a new review, or the title or lastrevid has been changed, so we need to run wrp_wiki_test.
      


      // include( plugin_dir_path(__FILE__) . '/includes/wrp_wiki_test.php' );





      $wiki_info = wrp_wiki_test( $new_title_value, $new_lastrevid );
      

      if ($wiki_info['error'] === true) {
        // If error key has value of true then something is wrong with the new values.
        // Don't save the new values.  Change post status from 'published' to 'draft'.  Alert user to the errors.
        
        $message = $wiki_info['message'];

        add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );

        // Change post status to draft in case of validation failure
        global $wpdb;
        $wpdb->update( $wpdb->posts, array("post_status" => "draft"), array("ID" => $post_id), array("%s"), array("%d") );

        
      } else {
        // Submitted info was basically good.  There may be some minor issues to pass on to user, but save new (possibly fixed) values.
        // Grab values from array and then save them, pass on messages to user, save wiki_title as page title.


        $to_save_title = $wiki_info['title'];
        $to_save_lastrevid = $wiki_info['lastrevid'];
        $pre_to_save_pageid = $wiki_info['pageid'];
        // page_id set to string because wp_set_object_terms treats an integer term as a tag id refernce #, not as an int itself.
        $to_save_pageid = "{$pre_to_save_pageid}";


        // Save values 
        wp_set_object_terms( $post_id, $to_save_title, 'wiki_title' );
        wp_set_object_terms( $post_id, $to_save_pageid, 'wiki_pageid' );
        update_post_meta( $post_id ,'lastrevid' , $to_save_lastrevid );
        $new_rating_value = sanitize_text_field( $_POST['wiki_rating'] ); // May not have changed, but no harm in resaving if it hasn't.
        wp_set_object_terms( $post_id, $new_rating_value, 'wiki_rating' );

        // Set title to same title as wiki_title
        global $wpdb;
        $wpdb->update( $wpdb->posts, array("post_title" => $to_save_title), array("ID" => $post_id), array("%s"), array("%d") );

        // Pass along message to user
        $message = $wiki_info['message'];
        
        // add_filter('redirect_post_location', 'my_message');

        // Uses anonymous function to pass $message to add_query_arg
        add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );
      }


    } else {
    // wiki_title and wiki_lastrevid were not changed in the form.  The save was must have been triggered by either a change in
    // the review text, disciplines, rating, or by pushing the save button.  Review text and disciplines are handled by WordPress (nothing custom),
    // so go ahead and save wiki_rating.  Not bothering to check if it has changed because no real harm in unneccesarily saving
    // unchanged rating again and is simpler this way.
    // ADD UPDATE MESSAGE?
      $new_rating_value = sanitize_text_field( $_POST['wiki_rating'] );
      wp_set_object_terms( $post_id, $new_rating_value, 'wiki_rating' );
    }
  }
}  


// Need to keep in mind that entering a blank on lastrevid is ok.
// save_post hook runs after post saved.  Strategy is to change post_status to draft if validation fails.
// Also to do: set title to wiki_title
// grab pageid
// give users a link to page that has been grabbed
// NOTE: false is the same as an empty string.  Remember this when testing for things and setting variable to false.

?>
