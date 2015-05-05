<?php
// A test for checking and verifying page titles and lastrevids

// NOTE add a final link to be returned to the user

echo "Enter Wikipedia article title: ";
$response = fopen ("php://stdin","r");
// trim() is needed otherwise a %0A is added at the end of line. I don't think this will be an issue in wordpress.
$pre_title = trim(fgets($response));

echo "Enter lastrevid, or leave empty to use current edit: ";
$response2 = fopen ("php://stdin","r");
$pre_id = trim(fgets($response2));

// Determine if lastrevid has been given
if (empty($pre_id)) {
  $title_base = 'http://en.wikipedia.org/w/api.php?format=json&action=query&titles=';
  $title_ending = '&prop=info|categories&clcategories=category:%20disambiguation%20pages&redirects';
  $encode_title = rawurlencode($pre_title);
  $title_url = $title_base . $encode_title . $title_ending;
  $title_response = file_get_contents($title_url);
  $title_decoded = json_decode($title_response, true);
  $pre_info = $title_decoded["query"]["pages"];
  $pre_pages_value = array_keys($pre_info);
  $pages_value = $pre_pages_value[0];

  // Make sure that the user supplied page exists.  A $pages_value of -1 means a non-existent title.
  if ($pages_value == -1) {
    echo "The title you entered does not exist.";
    // Do Not Save.  Return error to user to fix.
  }
  // User supplied title exists, so run some more test then grab lastrevid.
  else {
    $lastrevid = $pre_info[$pages_value]["lastrevid"];
    $redirect_normalization_test = $title_decoded['query'];
    $redirect = array_key_exists('redirects', $redirect_normalization_test) ? true : false;
    $disambiguation_test = $pre_info[$pages_value];
    $disambiguation = array_key_exists('categories', $disambiguation_test) ? true : false;
    $normalization = array_key_exists('normalized', $redirect_normalization_test) ? true : false;
    $pageid = $pre_info[$pages_value]["pageid"];
    if ($disambiguation && ($redirect || $normalization)) {
      $new_title = $disambiguation_test['title'];
      echo "Title has been changed to: {$new_title}.  Lastrevid is: {$lastrevid}.  Pageid is: {$pageid}.  Also, This looks like it might be a disambiguation page.";
      // In WordPress we would save the $new_title, $pageid, and $lastrevid values to the database in addition to alerting user that the
      // title has been fixed and that they might have entered a disambiguation page.
    }
    elseif ($redirect || $normalization) {
      $new_title = $disambiguation_test['title'];
      echo "Title has been changed to: {$new_title}.  Lastrevid is: {$lastrevid}.  Pageid is: {$pageid}.";
      // In WordPress we would save the $new_title, $pageid, and $lastrevid values to the database in addition to alerting user that the
      // title has been fixed.
    }
    elseif ($disambiguation) {
      echo "This looks like it might be a disambiguation page.  Lastrevid is: {$lastrevid}.  Pageid is: {$pageid}.";
      // In WordPress we would save the $pre_title, $pageid, and $lastrevid values to the database in addition to alerting user that
      // they might have entered a disambiguation page.
    }
    else {
      echo "Lastrevid is: {$lastrevid}.  Pageid is: {$pageid}.";
      // In WordPress we would save the $pre_title, $pageid, and $lastrevid values to the database.
    }
  }
}
else {
  // // Lastrevid has been given, so check that it is valid and that it matches the given title.
  $lastrevid_base = 'http://en.wikipedia.org/w/api.php?action=query&format=json&revids=';
  $lastrevid_ending = '&prop=info|categories&clcategories=category:%20disambiguation%20pages';
  $encode_lastrevid = rawurlencode($pre_id);
  $lastrevid_url = $lastrevid_base . $encode_lastrevid . $lastrevid_ending;
  $lastrevid_response = file_get_contents($lastrevid_url);
  $lastrevid_decoded = json_decode($lastrevid_response, true);
  $query_array = $lastrevid_decoded["query"];

  // Make sure lastrevid is real.  A bad lastrevid has no "pages" key.
  if (!array_key_exists("pages", $query_array)) {
    // Lastrevid number does not exist.
    echo "That lastrevid does not exist";
    // Do Not Save.  Retrun error to user to fix.
  }
  else {
    $pre_info = $query_array["pages"];
    // current() returns the value of the array element that's currently being pointed to by the internal pointer.
    // Since responses should only have one 1 value here, this works to grabs the array that we need.
    $info = current($pre_info);
    $title = $info["title"];
    if (strtolower($pre_title) !== strtolower($title)) {
    // Title for the lastrevid does not equal title supplied by user
    echo "The title associated with the lastrevid does not match the supplied title";
    // Do Not Save.  Retrun error to user to fix.
    }
    elseif (array_key_exists("redirect", $info)) {
      echo "The lastrevid points to a redirect page and cannot be saved";
      // Do Not Save.  Return error to user to fix.
      // Redirect page's lastrevids do not change their lastrevid to mirror changes in their target page. So there is no way to know
      // what edit the user intends to being reviewing.
    }
    else {
      // All is basically good.  Check for differences in capitalization, the possiblitiy of being a disambiguation page, and grab pageid.
      $pageid = $info["pageid"];
      if ($pre_title !== $title) {
      // Titles match but differ in capitalization  
        if (array_key_exists("categories", $info)) {
        echo "Lastrevid is good.  Title has been changed to {$title}.  Pageid is: {$pageid}.  This might be a disambiguation page.";
        // Probaly a disambiguation page, but save anyways since we can't be sure.  However, give the warning so the user
        // can double check.  Give alert that title has been changed.  Save $title, $pageid, and $pre_id.
        }
        else {
          echo "Lastrevid is good.  Title has been changed to {$title}.  Pageid is: {$pageid}";
          // Give alret that title has been changed.  Save $title, $pageid, and $pre_id.
        }
      }
      else {
      // Titles are a perfect match.
        if (array_key_exists("categories", $info)) {
        echo "Lastrevid is good.  Pageid is: {$pageid}.  This might be a disambiguation page.";
        // Probaly a disambiguation page, but save anyways since we can't be sure.  However, give the warning so the user
        // can double check.  Save $title, $pageid, and $pre_id.
        }
        else {
          echo "Lastrevid is good.  Pageid is: {$pageid}";
          // Save $title, $pageid, and $pre_id.
        }
      }
    }
  }
}

// Notes:

// Testing for disambiguation pages:
// This is imperfect, but the best thing I've found is to search for the Disambiguation pages category
// and if found, return that info to the user for them to correct.  Adding:
// "prop=categories&clcategories=Category:%20Disambiguation%20pages" will return a categories key pointing to an
// array that (among other things) will contain that category name if the page is in the disambiguation category. If not belonging
// to that category then the category key will not be present.

// There appears to be a redirect mechanism other than the one used by the api.  For example, "charles f. warwick" entered
// into the api returns no page, but the same thing in wikipedia brings you to the Charles F. Warwick page.  But no redirect page
// exists in wikipedia.  Not sure how to test for this or what is going on?

// The testing I did for redirect and normalization status is probably unneccesary (when give title only) as we could just grab the
// title returned by the api no matter what and still get the same result, but I figured we might want that info it at some point,
// so I have it there.


// TO DO

// Clarify variable names and reuse where possible.
// Make sure error handling is covered in case of a failed response from the wiki api or if it returns something unexpected.
// Simplify structure of code.
// Escape info.
// Test for existance of array key before using it?
// if (isset($someArray['someKey'])) {
//     $myVar = $someArray['someKey'];
// }
// Also, this link has good info on shortening above if used a lot:
// http://stackoverflow.com/questions/9869150/illegal-string-offset-warning-php
// User agent header for actual plugin?
// Consider possibility of circular redirects.
?>
