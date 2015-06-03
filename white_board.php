TO DO:

-Add link to reviewed Wikipedia page in admin notice (code to incorporate below.
<a href="<?php echo $lastrevid_link; ?>"><?php echo $lastrevid; ?></a>

-There are several places that need to have their post status rolled back to draft.  Additionally, rolled back posts
still show a post update message.  Figure out how to remove that.

-Add uninstall option

-Get post title and post name fully working an incorporate them (permalink and with easier way to grab title).



<?php


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



