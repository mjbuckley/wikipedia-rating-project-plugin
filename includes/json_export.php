<?php

// Functions to return a post's rating and pageid.  This is just a test implementation,
// not a production ready set of code (need to check for and deal with errors, etc).
// Eventually use these functions in more places than just here.

function wrp_get_rating( $post_id ) {
  $rating_values = get_the_terms( $post_id, 'wiki_rating');
  $rating_value = array_pop($rating_values);
  return $rating_value->name;
}

function wrp_get_pageid( $post_id ) {
  $pageid_values = get_the_terms( $post_id, 'wiki_pageid');
  $pageid_value = array_pop($pageid_values);
  return $pageid_value->name;
}



// Function to get return all of a user's posts as a JSON file.
function wrp_json_export( $user_id ) {
  $args = array(
      'post_type' => 'wrp_review',
      'author' => $user_id,
      'post_status' => 'publish',
      'nopaging' => true // Turns off pagination, may need to add posts_per_page = -1
  );

  $query = new WP_Query( $args );
  $output = array();

  while( $query->have_posts() ) : $query->the_post();

    $output[] = array(
      'title' => get_the_title(),
      'lastrevid' => get_post_meta( get_the_ID(), 'lastrevid', true ), // Not sure why using get_the_ID()
      'pageid' => wrp_get_pageid( $post->ID ),
      'rating' => wrp_get_rating( $post->ID ),
      'review' => get_the_content(),
    );

  endwhile;

  wp_reset_query(); // Needed?  Correct one?

return json_encode( $output, JSON_PRETTY_PRINT );
}


// Was going to use below with template_redirect, but was having trouble making
// that work.  Keeping around in case I want to go that route again.  Also, it users
// more header options that I do, and uses exit.  Look at those things.
// function wrp_template_redirect() {
//   if ($_SERVER['REQUEST_URI']=='/data.json') {
//     header("Content-type: application/json",true,200);
//     header("Content-Disposition: attachment; filename=data.json");
//     header("Pragma: no-cache");
//     header("Expires: 0");
//     $content = wrp_json_export();
//     echo $content;
//     exit();
//   }
// }
?>
