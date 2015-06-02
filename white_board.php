<?php

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



