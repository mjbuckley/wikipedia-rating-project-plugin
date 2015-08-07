TO DO:

-Add link to reviewed Wikipedia page in admin notice (code to incorporate below.
<a href="<?php echo $lastrevid_link; ?>"><?php echo $lastrevid; ?></a>

-There are several places that need to have their post status rolled back to draft.  Additionally, rolled back posts
still show a post update message.  Figure out how to remove that.

-Add uninstall option

-Get post title and post name fully working an incorporate them (permalink and with easier way to grab title).

OLD BUT GOOD ADMIN MESSAGE DISPLAY CODE (8/2/15)

    <div class="<?php echo $class ?>">
      <p><?php echo $message ?></p>
      <?php if ( $class === 'updated' ) { ?>
        <p><a href="<?php echo $lastrevid_link; ?>">Link to the reviewed Wikipedia page</a></p>
      <?php } // end if ?>
    </div>

<?php

if ( wrp_verify_rating( $post_id) ) {
  // continue
} else {
  // roll back to draft
}

// Check if submitted rating matches one of the official ratings.  Makes sure reviewers cannot add their own rating terms.
// Returns true if rating matches an official rating, false otherwise.
function wrp_verify_rating( $post_id ) {

  // Create array ($current_ratings_array) of all current rating terms
  $current_ratings = get_terms( 'wiki_rating', array( 'hide_empty' => 0 ) );
  $current_ratings_array = array();
  foreach($current_ratings as $current_rating) {
    $current_ratings_array[] = $current_rating->name;
  }

  $submitted_rating_value = sanitize_text_field( $_POST['wiki_rating'] );

  if ( in_array( $submitted_rating_value, $current_ratings_array )) {
    return true;
  } else {
    return false;
  }
}


$post_status = get_post_status();
switch ($post_status) {
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
    return;
}

?>


    <div class="<?php echo $class ?>">
      <?php if ( $intro ) { echo $intro; } ?>
      <p><?php echo $message ?></p>
      <?php if ( $class === 'updated' ) { ?>
        <p><a href="<?php echo $lastrevid_link; ?>">Link to the reviewed Wikipedia page</a></p>
      <?php } // end if ?>
    </div>

$permalink = esc_url( get_post_permalink() );

check for WP error?

<?php if ( $post_status === 'publish' ) { ?>
<p><a href="<?php echo esc_url( get_post_permalink() ); ?>">View the review.</a></p>
<?php } ?>
<p><a href="<?php echo esc_url( get_post_permalink() ); ?>">View the review.</a></p>


<?php if ( $intro ) { ?>
  <p><?php echo $intro ?></p>
<?php } // end if ?>


<div class="<?php echo $class ?>">
  <?php if ( $intro ) { echo "<p>{$intro}</p>"; } ?>

  <p><?php echo $message ?></p>
  <?php if ( $class === 'updated' ) { ?>
    <p><a href="<?php echo $lastrevid_link; ?>">Link to the reviewed Wikipedia page</a></p>
  <?php } // end if ?>
</div>

<?php




  $permalink = get_permalink( $post->ID );
  $view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View book', 'your-plugin-textdomain' ) );
  $messages[ $post_type ][1] .= $view_link;






// Function to remove post updated message.  Add the function to the post_updated_messages filter.
// Sometimes the message pops up even when the post isn't saved because of the way I have things set up
// and this removes that confusion.
function wrp_remove_update_message( $messages )
{
  unset($messages[post][6]);
  return $messages;
}
add_filter( 'post_updated_messages', 'wrp_remove_update_message' );




// Include wrp_review post_type on author archive pages
function wrp_fix_author_archive($query) {
  if ($query->is_author) {
    $query->set( 'post_type', array('wrp_review', 'post') );
    remove_action( 'pre_get_posts', 'wrp_fix_author_archive' );
  }
add_action('pre_get_posts', 'wrp_fix_author_archive');


// CODE TO ADD that puts login/out link on primary nav menu
// Add to theme not plugin
function wrp_add_loginout( $menu ) {
    $loginout = wp_loginout($_SERVER['REQUEST_URI'], false );
    $menu .= $loginout;
    return $menu;
}
add_filter( 'wp_nav_menu_primary_items','wrp_add_loginout' );


// post_name issue

-by assigning a post_name before the draft/pending is published, this preserves that name.

if post_status is publish, set post name, else don't.  Not sure what to do about posts shifting from publish to dre

States:

- publish
- publish => draft
- pending => draft
- pending => publish




// NOTE: CURRENTLY, CONTRIBUTORS CANNOT EDIT PUBLISHED POSTS

// ALSO, NOT SURE EXACTLY WHEN CHECKS GET RUN AND STUFF GETS SAVED.  WE WANT THAT HAPPENING ONCE, NOTE
// TWICE OR ELSE THE LASTREVID COULD CHANGE.

// I THINK I CAN AVOID PRIVATE POSTS BY CREATING THE CAPABILITIES BUT GRANTING THEM TO NO ONE.

// I THINK I ULTIMATELY NEED TO DEFINE THE ALL THE CAPABILITIES AND THEN EXPLICITLY ASSIGN THEM.

// BE SURE TO ALERT USERS THAT OTHER ROLES THEY HAVE ADDED OR ADD IN THE FUTURE NEED TO HAVE THEIR CAPABILITIES ADDED


// Create custom role of "Rater"

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



// Add to wrp_reviewer post type

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



// Add capabilities for the for wrp_review type
function wrp_add_review_caps() {

  // Array of default WordPress roles as well as the custom wrp_reviewer role
  $roles = array( 'super admin', 'administrator', 'editor', 'author', 'wrp_reviewer', 'subscriber' );

  // Loop through each role and add capabilities
  foreach( $roles as $the_role ) {

    $role = get_role( $the_role );

    // All roles
    $role->add_cap( 'read_wrp_reviews' );

    // All roles above subscriber
    if( $the_role == 'super admin' || $the_role == 'administrator' || $the_role == 'editor' || $the_role == 'author' || $the_role == 'contributor' || $the_role == 'wrp_reviewer' ) {
      $role->add_cap( 'edit_wrp_reviews' );
      $role->add_cap( 'delete_wrp_reviews' );
      $role->add_cap( 'delete_published_wrp_reviews' );
      $role->add_cap( 'edit_published_wrp_reviews' );
      $role->add_cap( 'create_wrp_reviews' );
    }

    // All roles above wrp_reviewer
    if ( $the_role == 'super admin' || $the_role == 'administrator' || $the_role == 'editor' || $the_role == 'author' ) {
      $role->add_cap( 'publish_wrp_reviews' );
    }

    // All roles above author
    if ( $the_role == 'super admin' || $the_role == 'administrator' || $the_role == 'editor' ) {
      $role->add_cap( 'edit_others_wrp_reviews' );
      $role->add_cap( 'read_private_wrp_reviews' );
      $role->add_cap( 'delete_private_wrp_reviews' ); // Consider not adding this and other private cap to prevent private posts?  Not sure if that works?
      $role->add_cap( 'delete_others_wrp_reviews' );
      $role->add_cap( 'edit_private_wrp_reviews' ); // Consider not adding this and other private cap to prevent private posts?  Not sure if that works?
    }
  }
}
add_action( 'admin_init', 'wrp_add_review_caps', 999 );










add_action( 'save_post','wrp_save_disciplines' );

function wrp_save_disciplines( $post_id ) {
// do same verification for ratings
// mostly ok with sanitation/validation, but double check

  if ( empty( $_POST['disciplines'] )) {
    wp_set_object_terms( $post_id, null, 'wiki_disciplines' );
  } else {
    $submitted_disciplines = $_POST['disciplines'];
    $current_disciplines = get_terms( 'wiki_disciplines', array( 'hide_empty' => 0 ) );
    $current_disciplines_array = array();
    $to_save_disciplines = array();
    foreach($current_disciplines as $current_discipline) {
      $current_disciplines_array[] = (int) $current_discipline->term_id; // Cast as int because get_terms returns as string
    } // current_disiplines_array now contains array of all term_ids of all disciplines.
    foreach ($submitted_disciplines as $submitted_discipline) {
      $sanitized_discipline = sanitize_text_field( $submitted_discipline );
      if ( in_array( $sanitized_discipline, $current_disciplines_array )) { // Makes sure submited discipline matches on of the official disciplines
        $to_save_disciplines[] = $submitted_discipline;
      }
    }
    if ( empty( $to_save_disciplines )) {
      wp_set_object_terms( $post_id, null, 'wiki_disciplines' );
    } else {
      wp_set_object_terms( $post_id, $to_save_disciplines, 'wiki_disciplines' );
    }
  }
}


// Add custom meta box adding review info
function wrp_add_disciplines_meta_boxes() {
  add_meta_box(
    'wrp_wiki_displines_metabox',
    'Disciplines',
    'wrp_create_wiki_disciplines_metabox',
    'wrp_review',
    'normal',
    'default'
  );
}

add_action( 'add_meta_boxes', 'wrp_add_disciplines_meta_boxes' );


// Display code for meta box in admin screen
function wrp_create_wiki_disciplines_metabox( $post ) {
  $current_disciplines = get_terms( 'wiki_disciplines', array( 'hide_empty' => 0 ) ); // Array of objects of all disciplines, not just those associated with post.
  $saved_disciplines = get_the_terms( $post->ID, 'wiki_disciplines' ); // Array of objects of all disciplines currently associated with this post_id.
  $saved_disciplines_array = array();

  if ( $saved_disciplines && !is_wp_error( $saved_disciplines )) { // Create array of term_ids of of discipline terms currently associated with post.
    foreach ($saved_disciplines as $saved_discipline) {
      $saved_disciplines_array[] = $saved_discipline->id;
    }
  } ?>

<!-- not sure if I should use fieldset or div with way wordpress handles forms in admin.  Either way, be consistent -->
<div>
  <fieldset>
    <legend>Title here if WordPress doesn't handle it</legend>
    <ul>
    <?php foreach($current_disciplines as $current_discipline) { // Loop through all disciplies and output as a checkbox
    $discipline_name = esc_attr( $current_discipline->name );
    $disipline_term_id = (int) $current_discipline->id; // This would be a string if I didn't change it.  Done correctly/need to change in first place?
    $discipline_css_id = "discipline_" . $disipline_term_id; // Create unique id for each form checkbox.  Ex: discipline_123 for a discipline with term_id 123.
    ?>
      <li>
        <input type="checkbox" name="discipllines[]" value="<?php echo $discipline_term_id; ?>" id="<?php echo $discipline_css_id; ?>"<?php if ( in_array( $discipline_term_id, $saved_disciplines_array )) { echo " checked"; } ?> />
        <label for="<?php echo $discipline_css_id; ?>"><?php echo $discipline_name; ?></label>
      </li>
    <?php } ?>
    </ul>
  </fieldset>
</div>

      $saved_titles = get_the_terms( $post->ID, 'wiki_title');
      $saved_title = $saved_titles ? array_pop($saved_titles) : false;
      $to_save_title = $saved_title->name




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
















$message_value = $_GET['my_message'];



$message_check = "success";

// grab title, lastrevid, and link if save was a success
if (strpos($message_value, $message_check) !== false) {
  $title = get_the_title();
  $encode_title = rawurlencode($title);
  $lastrevid = get_post_meta( $post->ID, 'lastrevid', true );
  $lastrevid_link = 'http://en.wikipedia.org/w/index.php?title=' . $encode_title . '&oldid=' . $lastrevid_value;
}





title access

$saved_titles = get_the_terms( $post->ID, 'wiki_title');
$saved_title = $saved_titles ? array_pop($saved_titles) : false;
$new_title = title here
if ($saved_title) {echo esc_attr( $saved_title->name );}



The link permalink to the reviewed Wikipedia article

link










add_filter( 'redirect_post_location', 'my_redirect_post_location_filter', 99);

function my_redirect_post_location_filter($location) {
  remove_filter('redirect_post_location', __FUNCTION__, 99);
  $location = add_query_arg( 'my_query_var' => 'num' ), $location );
  return $location;
}


OR

add_filter('redirect_post_location', 'my_message');

function my_message($loc) {
 return add_query_arg( 'my_message', 123, $loc );
}



add_action( 'admin_notices', my_notices );

function my_notices() {
   if ( ! isset( $_GET['my_message'] ) ) {
     return;
   }
   ?>
   
  <div class="updated">
    <p>IT WORKED!</p>
  </div>

<?php
}

?>

old admin notice (removed)

function wrp_admin_error_notice() { ?>
  <div class="error">
    <p>There was something wrong.</p>
  </div>
<?php
}

function wrp_admin_notice() { ?>
  <div class="updated">
    <p>Updated</p>
  </div>
<?php
}



function my_message($loc) {
  return add_query_arg( 'my_message', 123, $loc );
}


// Pass along message to user
  $message = $wiki_info['message'];
  
  add_filter('redirect_post_location', 'my_message');





// Create a Closure
$greeting = function() use ($user) {
  echo "Hello $user";
};

add_filter( 'redirect_post_location', function($loc) use ($message) { return add_query_arg( 'my_message', $message, $loc ); } );



function my_notices() {
   if ( ! isset( $_GET['my_message'] ) ) {
     return;
   } else {

   //escape this
   $message_num = $_GET['my_message'];

   // suppose I label messages m1-m6


   switch($message_num) {
    case 'm1':
      $message = "message 1";
      break;
    case 'm2':
      $message = "message 2";
      break;
   }
   
  <div class="updated">
    <p><?php $message ?></p>
  </div>

<?php
}


// Remove comments menu from admin screen for all but admins.
function wrp_remove_comments_menu() {
  $user = wp_get_current_user();
  if ( ! $user->has_cap( 'manage_options' ) ) {
    remove_menu_page( 'edit-comments.php' );
  }
}
add_action( 'admin_menu', 'wrp_remove_comments_menu' );


// Only show posts editable by the user in the admin screen.
function wrp_only_author_posts( $wp_query ) {
  global $current_user;
  if( is_admin() && !current_user_can('edit_others_posts') ) {
    $wp_query->set( 'author', $current_user->ID );
  }
}
add_action('pre_get_posts', 'wrp_only_author_posts' );




// Clean up dashboard for non-admins
function wrp_clean_dashboard() {
  $user = wp_get_current_user();
  if ( ! $user->has_cap( 'manage_options' ) ) {
    remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
    remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
  }
}
add_action( 'wp_dashboard_setup', 'wrp_clean_dashboard' );



// Removes the wrp_reviewer role and all wrp_review relatatd caps from all others.
function wrp_remove_review_caps() {

  remove_role( 'wrp_reviewer' );

  // Array of default WordPress roles as well as the custom wrp_reviewer role.
  $roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

  // Loop through each role and add capabilities
  foreach ( $roles as $the_role ) {

    $role = get_role( $the_role );

    if ( ! is_null( $role ) ) { // Checks in cases one of the default roles has been removed

      // All roles
      $role->remove_cap( 'read_wrp_reviews' );

      // All roles above subscriber
      if ( $the_role == 'administrator' || $the_role == 'editor' || $the_role == 'author' || $the_role == 'contributor' ) {
        $role->remove_cap( 'edit_wrp_reviews' );
        $role->remove_cap( 'delete_wrp_reviews' );
        $role->remove_cap( 'delete_published_wrp_reviews' );
        $role->remove_cap( 'edit_published_wrp_reviews' );
        $role->remove_cap( 'create_wrp_reviews' );
      }

      // All roles above contributor
      if ( $the_role == 'administrator' || $the_role == 'editor' || $the_role == 'author' ) {
        $role->remove_cap( 'publish_wrp_reviews' );
      }

      // All roles above author
      if ( $the_role == 'administrator' || $the_role == 'editor' ) {
        $role->remove_cap( 'edit_others_wrp_reviews' );
        $role->remove_cap( 'read_private_wrp_reviews' );
        $role->remove_cap( 'delete_private_wrp_reviews' );
        $role->remove_cap( 'delete_others_wrp_reviews' );
        $role->remove_cap( 'edit_private_wrp_reviews' );
      }
    }
  }
}


