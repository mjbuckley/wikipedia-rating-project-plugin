TO DO:

-Add link to reviewed Wikipedia page in admin notice (code to incorporate below.
<a href="<?php echo $lastrevid_link; ?>"><?php echo $lastrevid; ?></a>

-There are several places that need to have their post status rolled back to draft.  Additionally, rolled back posts
still show a post update message.  Figure out how to remove that.

-Add uninstall option

-Get post title and post name fully working an incorporate them (permalink and with easier way to grab title).



<?php



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



