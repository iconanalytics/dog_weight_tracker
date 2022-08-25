<?php
 require_once('bookmark_fns.php');
 session_start();


 if ($_SESSION['user_priv'] == '2'){ #use two as a placeholder for now
   echo 'Only users that are paid subscibers can register a dog';
   exit;
 }
  //create short variable name
  $dog_name = $_POST['dog_name'];
  $dog_breed = $_POST['dog_breed'];
  $dog_dob = $_POST['dog_dob'];
  $dog_gender = $_POST['dog_gender'];

  do_html_header('Registering dog');

  try {
    check_valid_user();
    if (!filled_out($_POST)) {
      throw new Exception('Form not completely filled out.');
    }
    /*
    // check URL format
    if (strstr($new_url, 'http://') === false) {
       $new_url = 'http://'.$new_url;
    }
 
    // check URL is valid
    if (!is_float($new_weight) or !is_int($new_weight)) {
      throw new Exception('Not a valid weight.');
    }
   */
    // try to add bm
    $added_dog_id = add_dog($dog_name,$dog_breed,$dog_dob,$dog_gender);
    $_SESSION['dog_id'] = $added_dog_id;
    echo 'Your dog has been registered with id:'.$_SESSION['dog_id'];
  

    if ($_SERVER['SERVER_NAME']==='localhost'){
      ?>
      <script type="text/javascript">
      window.location.href = 'http://localhost/sandbox/wp-content/apps/users/add_weight_form.php';
      </script>
      <?php
    }
    else{
      ?>
      <script type="text/javascript">
      window.location.href = 'https://petcalculator.com/wp-content/apps/users/add_weight_form.php';
      </script>
      <?php
    }

    // get the bookmarks this user has saved
    if ($url_array = get_user_urls($_SESSION['valid_user'])) {
      display_user_urls($url_array);
    }
  }
  catch (Exception $e) {
    echo $e->getMessage();
  }
  display_user_menu();
  do_html_footer();
?>
