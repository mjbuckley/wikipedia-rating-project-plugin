<?php
// A function for checking the validity of Wikipedia page titles and lastrevids.
// Returns are array.

// NOTES:
// ADD A FINAL link to be returned to the user
// SHOULD PROBABLy just use new_title for all title values returned in array.

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
        $message = "There is no Wikipedia article with the title that you entered.  Please check the title and try again.";
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
          $message = "Title has been changed from '{$test_title}' to '{$new_title}.'  Lastrevid is: {$lastrevid}.  Also, This looks like it might be a disambiguation page.";
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );
        }
        elseif ($redirect || $normalization) {
          // Title changed as a result of normalization or redirect.
          $new_title = $disambiguation_test['title'];
          $message = "Title has been changed from '{$test_title}' to '{$new_title}.'  Lastrevid is: {$lastrevid}.";
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $new_title, 'pageid' => $pageid, 'message' => $message );
        }
        elseif ($disambiguation) {
          // Could be a disambiguation page.  Save but alert user.
          $message = "This looks like it might be a disambiguation page.  Lastrevid is: {$lastrevid}.";
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
          
        }
        else {
          // Everything checks out perfectly.
          $message = "Lastrevid is: {$lastrevid}.";
          return array( 'error' => false, 'lastrevid' => $lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
        }
      }
    } else {
      // Unexpected type of response from Wikipedia API.  Prompt user to try again later.
      $message = "There was an error communicating with Wikipedia.  The text of your review has been saved as a draft.  Please trying submitting again later.";
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
        $message = "The lastrevid that you entered does not exist.  Please recheck the number and try again.";
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
          $message = "The title associated with the lastrevid does not match the given title";
          return array( 'error' => true, 'message' => $message );
        }

        // Lastrevid points to a redirect page.  Redirect page's lastrevids do not change their lastrevid to mirror changes in their target page.
        // So there is no way to know what edit the user intends to being reviewing.
        elseif (array_key_exists("redirect", $info)) {
          $message = "The lastrevid points to a redirect page and cannot be saved.";
          return array( 'error' => true, 'message' => $message );
        }

        // All is basically good.  Check for differences in capitalization, the possiblitiy of being a disambiguation page, and grab pageid.
        else {
          $pageid = $info["pageid"];

          // Titles match but differ in capitalization 
          if ($test_title !== $title) { 

            // Probaly a disambiguation page.  Title capitalization has been changed.  Save, but alert user.
            if (array_key_exists("categories", $info)) {
              $message = "This looks like it might be a disambiguation page.  Title has been changed to from '{$test_title}' to '{$title}'.";
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );
            }

            // Titles differ in capitalization, but all else good.  Save, but alert user.
            else {
              $message = "Title has been changed from '{$test_title}' to '{$title}'.";
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $title, 'pageid' => $pageid, 'message' => $message );
            }
          }

          // Titles are a perfect match.
          else {

            // Probably a disambiguation page.  Save, but alert user.
            if (array_key_exists("categories", $info)) {
              $message = "This might be a disambiguation page.";
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
            }

            // Everything matches up perfectly.
            else {
              $message = "Everything's good";
              return array( 'error' => false, 'lastrevid' => $test_lastrevid, 'title' => $test_title, 'pageid' => $pageid, 'message' => $message );
            }
          }
        }
      }
    } else {
      // Unexpected type of response from Wikipedia API.  Prompt user to try again later.
      $message = "There was an error communicating with Wikipedia.  The text of your review has been saved as a draft.  Please trying submitting again later.";
      return array( 'error' => true, 'message' => $message );
    }
  }
}


// Notes:

// Testing for disambiguation pages:
// This is imperfect, but the best thing I've found is to search for the Disambiguation pages category
// and if found, return that info to the user for them to correct.  Adding:
// "prop=categories&clcategories=Category:%20Disambiguation%20pages" will return a categories key pointing to an
// array that (among other things) will contain that category name if the page is in the disambiguation category. If not belonging
// to that category then the category key will not be present.  We should continue with save even if the category exists because the
// wikipedia disambiguation category is inconsistant.  It's a clue but not proof.  But do report possibility to user.

// There appears to be a redirect mechanism other than the one used by the api.  For example, "charles f. warwick" entered
// into the api returns no page, but the same thing in wikipedia brings you to the Charles F. Warwick page.  But no redirect page
// exists in wikipedia.  Not sure how to test for this or what is going on?

// The testing I did for redirect and normalization status is probably unneccesary (when give title only) as we could just grab the
// title returned by the api no matter what and still get the same result, but I figured we might want that info it at some point,
// so I have it there.

// I believe that wp_remote_get adds a user agent header for us by default, but check in to this and make sure it works as I want.

// Is it necessary to escape values returned by wiki api?


// TO DO

// Clarify variable names and reuse where possible.
// Simplify structure of code.
// Make sure error handling is covered in case of a failed response from the wiki api or if it returns something unexpected.  Some
// is taken care of now, but not all possible situations.
// Test for existance of array key before using it?
// if (isset($someArray['someKey'])) {
//     $myVar = $someArray['someKey'];
// }
// Also, this link has good info on shortening above if used a lot:
// http://stackoverflow.com/questions/9869150/illegal-string-offset-warning-php
// Consider possibility of circular redirects.
?>
