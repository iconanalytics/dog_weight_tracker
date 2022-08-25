<?php

function do_html_header($title) {
  // print an HTML header
?>
<!doctype html>
  <html>
  <head>
  <!-- jQuery library -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  <!-- jQuery UI library -->
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
      
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <style>
      body { font-family: Arial, Helvetica, sans-serif; font-size: 13px }
      li, td { font-family: Arial, Helvetica, sans-serif; font-size: 13px }
      hr { color: #3333cc;}
      a { color: #000 }
      div.formblock
         { background: #ccc; width: 300px; padding: 6px; border: 1px solid #000;}
    </style>
    <?php
    echo '<script src="https://cdn.plot.ly/plotly-1.2.0.min.js"></script>';
    ?>
  </head>
  <body>
  
  <div>
    <img src="petcalculator-logo-small.jpg" alt="Pet Calculator logo" height="55" width="57" style="float: left; padding-right: 6px;" />
      <h1>Pet Weight Tracker and Weight Predictor</h1>
  </div>
  <hr />
<?php
  if($title) {
    do_html_heading($title);
  }
}

function do_html_footer() {
  // print an HTML footer
?>
  </body>
  </html>
<?php
}

function do_html_heading($heading) {
  // print heading
?>
  <h2><?php echo $heading;?></h2>
<?php
}

function do_html_URL($url, $name) {
  // output URL as link and br
?>
  <br><a href="<?php echo $url;?>"><?php echo $name;?></a><br>
<?php
}

function display_site_info() {
  // display some marketing info
?>
  <ul>
  <li>Let us help you monitor your pet's growth!</li>
  <li>Our smart algorithm will alert your if it notices any unusual pattern in your pet's growth!</li>
  <li>We can help predict how big your pet will grow!</li>
  </ul>
<?php
}

function display_login_form() {
?>
  <p><a href="register_form.php">Not a member? Click Here to Create an Account</a></p>
  <form method="post" action="member.php">

  <div class="formblock">
    <h2>Members Log In Here</h2>

    <p><label for="email">Email:</label><br/>
    <input type="text" name="email" id="email" /></p>

    <p><label for="passwd">Password:</label><br/>
    <input type="password" name="passwd" id="passwd" /></p>

    <button type="submit">Log In</button>

    <p><a href="forgot_form.php">Forgot your password?</a></p>
  </div>

 </form>
<?php
}

function display_registration_form() {
?>
 <form method="post" action="register_new.php">

 <div class="formblock">
    <h2>Register Now</h2>

    <p><label for="email">Email Address:</label><br/>
    <input type="email" name="email" id="email" 
      size="30" maxlength="100" required /></p>

    <p><label for="passwd">Password <br>(between 6 and 16 chars):</label><br/>
    <input type="password" name="passwd" id="passwd" 
      size="16" maxlength="16" required /></p>

    <p><label for="passwd2">Confirm Password:</label><br/>
    <input type="password" name="passwd2" id="passwd2" 
      size="16" maxlength="16" required /></p>


    <button type="submit">Register</button>

   </div>

  </form>
<?php

}

function display_user_urls($url_array) {
  // display the table of URLs

  // set global variable, so we can test later if this is on the page
  global $bm_table;
  $bm_table = true;
?>
  <br>
  <form name="bm_table" action="delete_bms.php" method="post">
  <table width="300" cellpadding="2" cellspacing="0">
  <?php
  $color = "#cccccc";
  echo "<tr bgcolor=\"".$color."\"><td><strong>Bookmark</strong></td>";
  echo "<td><strong>Delete?</strong></td></tr>";
  if ((is_array($url_array)) && (count($url_array) > 0)) {
    foreach ($url_array as $url)  {
      if ($color == "#cccccc") {
        $color = "#ffffff";
      } else {
        $color = "#cccccc";
      }
      //remember to call htmlspecialchars() when we are displaying user data
      echo "<tr bgcolor=\"".$color."\"><td><a href=\"".$url."\">".htmlspecialchars($url)."</a></td>
            <td><input type=\"checkbox\" name=\"del_me[]\"
                value=\"".$url."\"></td>
            </tr>";
    }
  } else {
    echo "<tr><td>No bookmarks on record</td></tr>";
  }
?>
  </table>
  </form>
<?php
}

function display_user_menu() {
  // display the menu options on this page
?>
<hr>
<!-- <a href="member.php">Home</a> &nbsp;|&nbsp; -->
<!--<a href="add_weight_form.php">Record Weight</a> &nbsp;|&nbsp;-->
<?php
  // only offer the delete option if bookmark table is on this page
  global $bm_table;
  /*
  if ($bm_table == true) {
    echo "<a href=\"#\" onClick=\"bm_table.submit();\">Delete BM</a> &nbsp;|&nbsp;";
  } else {
    echo "<span style=\"color: #cccccc\">Delete BM</span> &nbsp;|&nbsp;";
  }
  */
?>
<a href="change_passwd_form.php">Change password</a><br>
<a href="add_dog_form.php">Register a New Dog</a> &nbsp;|&nbsp;
<a href="member.php">Home</a>
<a href="logout.php">Logout</a>
<hr>

<?php
}

function display_add_bm_form() {
  // display the form for people to ener a new bookmark in
?>
<form name="bm_table" action="add_bms.php" method="post">

 <div class="formblock">
    <h2>New Weight</h2>

    <p>
    <input type="text" name="new_weight" id="new_weight" placeholder="enter the most recent weight" 
      size="40"  maxlength="155" value="" required /></p>

    <input type="radio" id="lbs" name="weight_unit_option" checked value="lbs" />
    <label for="lbs">Pounds</label><br>
    <input type="radio" id="kg" name="weight_unit_option" value="kg" />
    <label for="kg">Kilograms</label><br>
    <br>
    <br>
      <input type="date" name="date_weighed" id="date_weighed" placeholder="enter the weighing date"
      size="40"  maxlength="155" value="" required /></p>
    
    <button type="submit">Add Weight</button>

   </div>

</form>
<?php
}

function display_add_dog_form() {
  // display the form for people to ener a new bookmark in
?>
<form name="dog_table" action="add_dog.php" method="post">

 <div class="formblock">
    <h2>Register a Dog</h2>

    <p>
    <input type="text" name="dog_name" id="dog_name" placeholder="enter the name of your dog" 
      size="40"  maxlength="155" value="" required /></p>


      <label for="dog_breed">select the breed of your dog</label>

      <select id="dog_breed" name="dog_breed" required>
      <?php
        require 'breed_info.php';
        ksort($breed_info);
        foreach ($breed_info as $breed=>$info){
          echo '<option value="'.$info[0].'">'.$breed.'</option>';
        }
      ?>
      </select>

      <br>
      <br>
      <label for="dog_dob">enter your dog's date of birth</label><br>
      <input type="date" name="dog_dob" id="dog_dob" placeholder="enter your dog's date of birth"
      size="40"  maxlength="155" value="" required /></p>

      
      <label for="dog_gender">Gender</label>

      <select id="dog_gender" name="dog_gender" required>
        <option value="1">Female</option>
        <option value="2">Male</option>
      </select>
      <br>
      <br>
    
    <button type="submit">Register Dog</button>

   </div>

</form>
<?php
}

function display_password_form() {
  // display html change password form
?>
   <br>
   <form action="change_passwd.php" method="post">

 <div class="formblock">
    <h2>Change Password</h2>

    <p><label for="old_passwd">Old Password:</label><br/>
    <input type="password" name="old_passwd" id="old_passwd" 
      size="16" maxlength="16" required /></p>

    <p><label for="passwd2">New Password:</label><br/>
    <input type="password" name="new_passwd" id="new_passwd" 
      size="16" maxlength="16" required /></p>

    <p><label for="passwd2">Repeat New Password:</label><br/>
    <input type="password" name="new_passwd2" id="new_passwd2" 
      size="16" maxlength="16" required /></p>


    <button type="submit">Change Password</button>

   </div>
   <br>
<?php
}

function display_forgot_form() {
  // display HTML form to reset and email password
?>
   <br>
   <form action="forgot_passwd.php" method="post">

 <div class="formblock">
    <h2>Forgot Your Password?</h2>

    <p><label for="username">Enter the email associated with your account :</label><br/>
    <input type="text" name="username" id="username" 
      size="16" maxlength="160" required /></p>

    <button type="submit">Change Password</button>

   </div>
   <br>
<?php
}

function display_recommended_urls($url_array) {
  // similar output to display_user_urls
  // instead of displaying the users bookmarks, display recomendation
?>
  <br>
  <table width="300" cellpadding="2" cellspacing="0">
<?php
  $color = "#cccccc";
  echo "<tr bgcolor=\"".$color."\">
        <td><strong>Recommendations</strong></td></tr>";
  if ((is_array($url_array)) && (count($url_array)>0)) {
    foreach ($url_array as $url) {
      if ($color == "#cccccc") {
        $color = "#ffffff";
      } else {
        $color = "#cccccc";
      }
      echo "<tr bgcolor=\"".$color."\">
            <td><a href=\"".$url."\">".htmlspecialchars($url)."</a></td></tr>";
    }
  } else {
    echo "<tr><td>No recommendations for you today.</td></tr>";
  }
?>
  </table>
<?php
}

?>
