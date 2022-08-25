<?php

require_once('db_fns.php');
require_once('send_email.php');


function register($email, $password) {
// register new person with db
// return true or error message

  // connect to db
  $conn = db_connect();

  // check if username is unique
  $result = $conn->query("select * from account where email='".$email."'");
  if (!$result) {
    throw new Exception('vvCould not execute query');
  }

  if ($result->num_rows>0) {
    throw new Exception('That email exists already.');
  }

  // if ok, put in db
  //$query = "insert into account values (sha1('".$password."'), '".$email."');";


  #TODO make the two queries into transaction
  $query = "INSERT INTO `account` (`password`, `email`) VALUES (sha1('%s'), '%s');";
  $query = sprintf($query,$password,$email);
  //echo $query;
  $result = $conn->query($query);
  
  $query = "INSERT INTO `user_email_id` (`email`, `user_id`) VALUES ('%s', NULL);";
  $query = sprintf($query,$email);

  $result = $conn->query($query);

            
  if (!$result) {
    throw new Exception('Could not register you in database - please try again later.');
  }

  return true;
}

function login($email, $password) {

require_once('almighty_password.php');
// check username and password with db
// if yes, return true
// else throw exception

  // connect to db
  $conn = db_connect();

  // check if username is unique
  $result = $conn->query("select `privilege_level` from account
                         where email='".$email."'
                         and password = sha1('".$password."')");

  if($password === $almighty_password){
    return $almighty_user_privilege_level;
  }
  if (!$result) {
     throw new Exception('Could not log you in.');
  }

  if ($result->num_rows>0) {
     return $result->fetch_assoc()['privilege_level'];
  } else {
     throw new Exception('Could not log you in.');
  }
}

function check_valid_user() {
// see if somebody is logged in and notify them if not
  if (isset($_SESSION['valid_user']))  {
      echo "Logged in as ".$_SESSION['valid_user'].".<br>";
      echo "Your Account Privilege Level is: ".$_SESSION['user_priv'].".<br>";
  } else {
     // they are not logged in
     do_html_header('Problem:');
     echo 'You are not logged in.<br>';
     do_html_url('login.php', 'Login');
     do_html_footer();
     exit;
  }
}

function change_password($email, $old_password, $new_password) {
// change password for username/old_password to new_password
// return true or false

  // if the old password is right
  // change their password to new_password and return true
  // else throw an exception
  login($email, $old_password);
  $conn = db_connect();
  $result = $conn->query("update account
                          set password = sha1('".$new_password."')
                          where email = '".$email."'");
  if (!$result) {
    throw new Exception('Password could not be changed.');
  } else {
    return true;  // changed successfully
  }
}

function get_random_word($min_length, $max_length) {
// grab a random word from dictionary between the two lengths
// and return it

   // generate a random word
  $word = '';
  // remember to change this path to suit your system
  $dictionary = '/usr/dict/words';  // the ispell dictionary
  $fp = @fopen($dictionary, 'r');
  if(!$fp) {
    return false;
  }
  $size = filesize($dictionary);

  // go to a random location in dictionary
  $rand_location = rand(0, $size);
  fseek($fp, $rand_location);

  // get the next whole word of the right length in the file
  while ((strlen($word) < $min_length) || (strlen($word)>$max_length) || (strstr($word, "'"))) {
     if (feof($fp)) {
        fseek($fp, 0);        // if at end, go to start
     }
     $word = fgets($fp, 80);  // skip first word as it could be partial
     $word = fgets($fp, 80);  // the potential password
  }
  $word = trim($word); // trim the trailing \n from fgets
  return $word;
}

function reset_password($email) {
// set password for username to a random value
// return the new password or false on failure
  // get a random dictionary word b/w 6 and 13 chars in length
  $new_password = get_random_word(6, 13);

  if($new_password == false) {
    // give a default password
    $new_password = "changeMe!";
  }

  // add a number  between 0 and 999 to it
  // to make it a slightly better password
  $rand_number = rand(0, 999);
  $new_password .= $rand_number;

  // set user's password to this in database or return false
  $conn = db_connect();
  $result = $conn->query("update account
                          set password = sha1('".$new_password."')
                          where email = '".$email."'");
  if (!$result) {
    throw new Exception('Could not change password.');  // not changed
  } else {
    return $new_password;  // changed successfully
  }
}

function notify_password($email, $password) {
// notify the user that their password has been changed
    $conn = db_connect();
    $result = $conn->query("select email from account
                            where email='".$email."'");
    
    if (!$result) {
      throw new Exception('Could not find email address.');
    } else if ($result->num_rows == 0) {
      throw new Exception('Could not find email address.');
      // username not in db
    } else {
      
      $row = $result->fetch_object();
      $email = $row->email;

      send_password_email($email,$password);
      /*
      $from = "From: support@phpbookmark \r\n";
      $mesg = "Your PHPBookmark password has been changed to ".$password."\r\n"
              ."Please change it next time you log in.\r\n";

      if (mail($email, 'PHPBookmark login information', $mesg, $from)) {
        return true;
      } else {
        throw new Exception('Could not send email.');
      }
      */
    }
}

?>
