<?php

// Function for validating a user supplied Wikipedia article title ($test_title) and lastrevid ($test_lastrevid).  It does the following:

// -Ensure given title exists.
// -Verify that the title Wikipedia associates with the given lastrevid matches the given title.
// -If no lastrevid is provided (it's empty), then the lastrevid of the current edit of the given title is grabbed.
// -Check for the possibility that the page is a disambiguation page
// -Fix capitalization errors.
// -If given title is a redirect page, try to grab the title of the target of the redirect.
// -Get pageid number.
// -Return an array with this infomation, or notice of an error, as well as a message key for displaying results to the user.

// NOTE ADDITIONAL ERRORS ADDED.  GIVE THEM A NEW MESSAGE #

function wrp_wiki_test( $test_title, $test_lastrevid ) { // Expects unslashed data (no magic quotes)

  if ( empty( $test_lastrevid ) ) { // START OF TEST IF LASTREVID EMPTY

    $title_base = 'http://en.wikipedia.org/w/api.php?format=json&action=query&titles=';
    $title_ending = '&prop=info|categories&clcategories=category:%20disambiguation%20pages&redirects';
    $encode_title = rawurlencode( $test_title );
    $title_url = $title_base . $encode_title . $title_ending; // Wikipedia API request
    $title_response = wp_remote_get( $title_url ); // Raw response from Wikipedia API

    if ( is_wp_error( $title_response ) || ! is_array( $title_response )  ) {

      // Response is a WP Error or in an unexpected form.
      $message = 'error2';
      return array( 'error' => true, 'message' => $message );


    } else {

      $body = $title_response['body']; // Strip response header
      $title_decoded = json_decode( $body, true ); // Convert JSON to be useable in PHP
      $pre_info = $title_decoded['query']['pages']; 


      if ( ! isset( $pre_info ) ) {

        // We have an unexpected response from Wikipedia
        $message = 'error6';
        return array( 'error' => true, 'message' => $message );


      } else { // At this point we can be near certain we have the response type we expect

        $pre_pages_value = array_keys( $pre_info );
        $pages_value = $pre_pages_value[0];
        
        if ( $pages_value == -1 ) {

          // A $pages_value of -1 means there is no Wikipedia article with the given title.
          $message = 'error1';
          return array( 'error' => true, 'message' => $message );


        } else { // User supplied title exists.

          $lastrevid = $pre_info[ $pages_value ]['lastrevid'];

          // 'redirect' and 'normalized' keys will be part of the $redirect_normalization_test array if the page has been normalized or is
          // a redirect.  The API will grab the target of redirect, but will want to alert user that tile has been fixed.
          $redirect_normalization_test = $title_decoded['query'];
          $redirect = array_key_exists( 'redirects', $redirect_normalization_test ) ? true : false;
          $normalization = array_key_exists( 'normalized', $redirect_normalization_test ) ? true : false;

          // 'categories' key will be part of $disambiguation_test array if the 'Disambiguation pages' category is associated with the title
          // because the only category we check for is 'Disambiguation pages.
          $disambiguation_test = $pre_info[ $pages_value ];
          $disambiguation = array_key_exists( 'categories', $disambiguation_test ) ? true : false;

          $pageid = $pre_info[ $pages_value ]['pageid'];
          $new_title = $pre_info[ $pages_value ]['title'];

          if ( ! is_int( $lastrevid ) ) {

            // A final check to be certain of no errors.  A failure here would likely be because an array key we expected to be there was not
            // present, and all subsequent variables depending on it (including $lastrevid) would be set to null.  In any case, we
            // have an unexpected response from Wikipedia.
            $message = 'error6';
            return array( 'error' => true, 'message' => $message );


          } elseif ( $disambiguation && ( $redirect || $normalization ) ) {

            // Could be a disambiguation page.  Also, title fixed as a result of normalization or redirect.  Save but alert user.        
            $message = 'success1';
            return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );


          } elseif ( $redirect || $normalization ) {

            // Title changed as a result of normalization or redirect.
            $message = 'success2';
            return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );


          } elseif ( $disambiguation ) {

            // Could be a disambiguation page.  Save but alert user.
            $message = 'success3';
            return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );

            
          } else {

            // Everything checks out perfectly.
            $message = 'success4';
            return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );
          }
        }
      }
    } // END OF TEST IF LASTREVID EMPTY


  } else { // START OF TEST IF LASTREVID GIVEN

    $lastrevid_base = 'http://en.wikipedia.org/w/api.php?action=query&format=json&revids=';
    $lastrevid_ending = '&prop=info|categories&clcategories=category:%20disambiguation%20pages';
    $encode_lastrevid = rawurlencode( $test_lastrevid );
    $lastrevid_url = $lastrevid_base . $encode_lastrevid . $lastrevid_ending; // Wikipedia API request
    $lastrevid_response = wp_remote_get( $lastrevid_url ); // Raw response from Wikipedia API

    if ( is_wp_error( $lastrevid_response ) || ! is_array( $lastrevid_response ) ) {

      // Response is a WP Error or in an unexpected form.  Prompt user to try again later.
      $message = 'error6';
      return array( 'error' => true, 'message' => $message );


    } else {

      $body = $lastrevid_response['body']; // Strip response header
      $lastrevid_decoded = json_decode( $body, true ); // Convert to a PHP useable JSON form
      $query_array = $lastrevid_decoded['query'];  

      if ( ! array_key_exists( 'pages', $query_array ) ) {

        // Lastrevid doesn't exist because a bad lastrevid has no 'pages' key.
        $message = 'error3';
        return array( 'error' => true, 'message' => $message );


      } else {

        // Lastrevid exists
        $pre_info = $query_array['pages'];
        // current() returns the value of the array element that's currently being pointed to by the internal pointer.
        // Since responses should only have one 1 value here, this works to grabs the array that we need.
        $info = current( $pre_info) ;
        $title = $info['title'];

        // Verify that $test_title matches the title that the API has associated with the lastrevid
        if ( strtolower( $test_title) !== strtolower( $title ) ) {

          // Title for the lastrevid does not equal title supplied by user
          $message = 'error4';
          return array( 'error' => true, 'message' => $message );


        } elseif ( array_key_exists( 'redirect', $info ) ) {

          // Lastrevid points to a redirect page.  Redirect page's lastrevids do not change their lastrevid to mirror changes in their target page.
          // So there is no way to know what edit the user intends to being reviewing.
          $message = 'error5';
          return array( 'error' => true, 'message' => $message );


        } else {

          // All is basically good.  Grab pageid, check for differences in capitalization, and the possiblitiy of being a disambiguation page.

          $pageid = $info['pageid'];

          if ( $test_title !== $title ) {

            // Titles match but differ in capitalization as a result of redirect of normalization.  Change title to correct title and
            // alert user of change.

            if ( array_key_exists( 'categories', $info ) ) {

              // Probaly a disambiguation page.  'Categories' key will only exist if one of the checked for categories is present, and
              // the only category checked for was 'disambiguation pages'.  Save, but alert user.
              $message = 'success5';
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );


            } else {

              // Titles differ in capitalization, but all else good.  Save, but alert user.
              $message = 'success6';
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );
            }


          } else { // Titles are a perfect match.

            if ( array_key_exists( 'categories', $info ) ) {
              // Probably a disambiguation page.  Save, but alert user.
              $message = 'success7';
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );


            } else {

              // Everything matches up perfectly.
              $message = 'success8';
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );
            }
          }
        }
      }
    }
  } // END OF TEST IF LASTREVID GIVEN
}
?>