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
  'rewrite' => true,
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
  'rewrite' => true,
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
  'rewrite' => true,
  'show_admin_column' => true,
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



// Add custom meta box for lastrevid
add_action( 'add_meta_boxes', 'wrp_add_meta_boxes' );
function wrp_add_meta_boxes() {
 add_meta_box(
  'wrp_wiki_rating_metabox',
  'Wikipedia Article Information:',
  'wrp_create_wiki_rating_metabox',
  'wrp_review',
  'normal',
  'high' );
}

// Display code for adding review info (title, rating, lastrevid)
// Change wrp_rating label/name
function wrp_create_wiki_rating_metabox( $post ) { ?>
  <?php // add nonce for security, below works but is temporary
  wp_nonce_field( 'wrp_metabox_nonce', 'wrp_nonce' ); ?>

  <!-- get_the_terms() returns false if post doesn't exists or doesn't contain the term, otherwise returns an array of term objects.
  There should only be one title, but array pop ensures this. -->
  <div>
    <?php
      $saved_titles = get_the_terms( $post->ID, 'wiki_title');
      $saved_title = $saved_titles ? array_pop($saved_titles) : false;
    ?>
    <label for="test">Wikipedia Article Title</label>
    <br />
    <input type="text" name="wiki_title" value="<?php if ($saved_title){echo $saved_title->name;} ?>" />
  </div>


  <!-- The true parameter in get_post_meta() means that only the first value is returned (although there
    should only be one), and it returns value as a string instead of as an array. If key (lastrevid) does
    not exist, then an empty string will be returned. -->
  <div>
    <?php $saved_lastrevid = get_post_meta( $post->ID, 'lastrevid', true ); ?>
    <label for="test2">Lastrevid (leave blank to grab current)</label>
    <br />
    <input type="text" name="lastrevid" value="<?php echo $saved_lastrevid ?>" />
  </div>

  <div>
    <label for="wrp_rating">Rating</label>
    <select name="wrp_rating">
  </div>
  <?php
    // hide_empty set to 0 ensures that ratings are shown even if they haven't been used yet.
    $rating_terms = get_terms( 'wiki_rating', array( 'hide_empty' => 0 ) );

    foreach($rating_terms as $rating_term) { ?>

      <option value="<?php echo $rating_term->name; ?>"<?php if ( has_term($rating_term->name, 'wiki_rating') ){echo " selected";} ?>><?php echo $rating_term->name; ?></option>
      <?php
    }
  ?>
  </select>
  <?php
}


// save the meta box data
add_action( 'save_post','wrp_save_rating' );
function wrp_save_rating( $post_id ) {
  // if( !isset( $_POST['wrp_metabox_nonce'] ) || !wp_verify_nonce( $_POST['wrp_nonce'], 'wrp_metabox_nonce' ) ) return;
  if ( isset( $_POST['wrp_rating'] ) ) {
    $new_rating_value = $_POST['wrp_rating'];
    wp_set_object_terms( $post_id, $new_rating_value, 'wiki_rating' );
  }

  if ( isset( $_POST['wiki_title'] ) ) {
    $new_title_value = $_POST['wiki_title'];
    wp_set_object_terms( $post_id, $new_title_value, 'wiki_title' );
  }

  if ( isset( $_POST['lastrevid'])) {
    $new_lastrevid = $_POST['lastrevid'];
    update_post_meta( $post_id ,'lastrevid' , $new_lastrevid );
  }
}

?>
