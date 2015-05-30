<?php

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

  if (!isset($_POST['wiki_rating']) || empty($_POST['wiki_rating']) || !isset($_POST['wiki_title']) || empty($_POST['wiki_title']) || (!isset($_POST['wiki_lastrevid']))) {
    // If true than either something is wrong with form or a required field is missing.  Return error to user.
    
    $message = "The Wikipedia article title and/or the rating are not filled out.  Please complete those fields and try again.";
    
    function wrp_admin_error_notice() { ?>
      <div class="error">
        <p>The Wikipedia article title and/or the rating are not filled out.  Please complete those fields and try again.></p>
      </div>
    <?php
    }
    add_action( 'admin_notices', 'wrp_admin_error_notice' );


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
      include( plugin_dir_path(__FILE__) . '/includes/wrp_wiki_test.php' );
      $wiki_info = wrp_wiki_test( $new_title_value, $new_lastrevid );
      

      if ($wiki_info['error'] === true) {
        // If error key has value of true then something is wrong with the new values.
        // Don't save the new values.  Change post status from 'published' to 'draft'.  Alert user to the errors.
        
        $message = $wiki_info['message'];

        function wrp_admin_error_notice() { ?>
          <div class="error">
            <p>There was something wrong.</p>
          </div>
        <?php
        }
        add_action( 'admin_notices', 'wrp_admin_error_notice' );

        // Change post status to draft in case of validation failure
        $wpdb->update( $wpdb->posts, array("post_status" => "draft"), array("ID" => $post_id), array("%s"), array("%d") );

        
      } else {
        // Submitted info was basically good.  There may be some minor issues to pass on to user, but save new (possibly fixed) values.
        // Grab values from array and then save them, pass on messages to user, save wiki_title as page title.


        $to_save_title = $wiki_info['title'];
        $to_save_lastrevid = $wiki_info['lastrevid'];
        $to_save_pageid = $wiki_info['pageid'];


        // Save values 
        wp_set_object_terms( $post_id, $to_save_title, 'wiki_title' );
        wp_set_object_terms( $post_id, $to_save_pageid, 'wiki_pageid' );
        update_post_meta( $post_id ,'lastrevid' , $to_save_lastrevid );
        $new_rating_value = sanitize_text_field( $_POST['wiki_rating'] ); // May not have changed, but no harm in resaving if it hasn't.
        wp_set_object_terms( $post_id, $new_rating_value, 'wiki_rating' );

        // Set title to same title as wiki_title
        $wpdb->update( $wpdb->posts, array("post_title" => $to_save_title), array("ID" => $post_id), array("%s"), array("%d") );

        // Pass along message to user
        $message = $wiki_info['message'];
        function wrp_admin_notice() { ?>
          <div class="updated">
            <p>Updated</p>
          </div>
        <?php
        }
        add_action( 'admin_notices', 'wrp_admin_notice' );
      }


    } else {
    // wiki_title and wiki_lastrevid were not changed in the form.  The save was must have been triggered by either a change in
    // the review text, disciplines, rating, or by pushing the save button.  Review text and disciplines are handled by WordPress (nothing custom),
    // so go ahead and save wiki_rating.  Not bothering to check if it has changed because no real harm in unneccesarily saving
    // unchanged rating again and is simpler this way.
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


