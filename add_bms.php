<?php
 require_once('bookmark_fns.php');
 session_start();

  //create short variable name
  $new_weight = $_POST['new_weight'];
  $date_weighed = $_POST['date_weighed'];
  $weight_unit = $_POST['weight_unit_option'];

  do_html_header('Adding Pet Weight');

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
    if ($weight_unit == 'lbs'){
      add_weight($new_weight,$date_weighed);
    }
    if ($weight_unit == 'kg'){
      $KG_TO_POUND = 2.20462;
      $new_weight_kg = strval((float)$new_weight*$KG_TO_POUND);
      add_weight($new_weight_kg,$date_weighed);
    }
      echo 'New Weight added.';

      if ($_SERVER['SERVER_NAME']==='localhost'){
        ?>
        <script type="text/javascript">
        window.location.href = 'http://localhost/sandbox/wp-content/apps/users/member.php';
        </script>
        <?php
      }
      else{
        ?>
        <script type="text/javascript">
        window.location.href = 'https://petcalculator.com/wp-content/apps/users/member.php';
        </script>
        <?php
      }

    
  }
  catch (Exception $e) {
    echo $e->getMessage();
  }
  display_user_menu();
  do_html_footer();
?>
