<?php
// include function files for this application
require_once('bookmark_fns.php');
session_start();

// start output html
do_html_header('Register Your Dog');

check_valid_user();
display_add_dog_form();

display_user_menu();
do_html_footer();

?>
