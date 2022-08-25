<?php
require_once('db_fns.php');

function get_user_urls($username) {
  //extract from the database all the URLs this user has stored

  $conn = db_connect();
  $result = $conn->query("select bm_URL
                          from bookmark
                          where username = '".$username."'");
  if (!$result) {
    return false;
  }

  //create an array of the URLs
  $url_array = array();
  for ($count = 1; $row = $result->fetch_row(); ++$count) {
    $url_array[$count] = $row[0];
  }
  return $url_array;
}

function add_weight($new_weight,$date_weighed) {
  // Add new bookmark to the database

  echo "Attempting to add ".htmlspecialchars($new_weight)."<br />";
  $valid_user = $_SESSION['valid_user'];



  $conn = db_connect();

  $query = "SELECT `user_id` FROM `user_email_id` WHERE `email` = '%s';";
  $query = sprintf($query,$valid_user);
  $result = $conn->query($query);
  $user_id = $result->fetch_assoc();
  $user_id = $user_id['user_id'];
  /*
  $query = "SELECT `dog_id` FROM `dog` WHERE `user_id` = '%s';";
  $query = sprintf($query,$user_id);
  $result = $conn->query($query);
  $dog_id = $result->fetch_assoc();
  $dog_id = $dog_id['dog_id'];
  */
  $dog_id = $_SESSION['dog_id'];

  $query = "INSERT INTO `dog_weight` (`dog_id`, `weight`, `date_weighed`) VALUES ('%s', '%s', '%s');";
  $query = sprintf($query,$dog_id,$new_weight,$date_weighed);
  if (!$conn->query($query) )
     
    {
    throw new Exception('Weight could not be inserted.');
    }

  return true;
}

function add_dog($dog_name,$dog_breed,$dog_dob,$dog_gender){
    $valid_user = $_SESSION['valid_user'];

    $conn = db_connect();

    $query = "SELECT `user_id` FROM `user_email_id` WHERE `email` = '%s';";
    $query = sprintf($query,$valid_user);
    $result = $conn->query($query);
    $user_id = $result->fetch_assoc();
    $user_id = $user_id['user_id'];

    $query = "INSERT INTO `dog` (`user_id`,`dog_name`, `dog_breed`, `dog_dob`,`dog_gender`) VALUES ('%s','%s', '%s', '%s', '%s');";
    $query = sprintf($query,$user_id,$dog_name,$dog_breed,$dog_dob,$dog_gender);

    
    if (!$conn->query($query) )
      
      {
      throw new Exception('Your dog could not be registered.');
      }

    $query = "SELECT `dog_id` FROM `dog` WHERE `dog_name` = '%s' AND `user_id` = '%s';"; #consequence of this stmt: each user must have unique dog names
    $query = sprintf($query,$dog_name,$user_id);
    $result = $conn->query($query);
    $dog_id = $result->fetch_assoc();
    $dog_id = $dog_id['dog_id'];
 

    return $dog_id;
    }

function delete_bm($user, $url) {
  // delete one URL from the database
  $conn = db_connect();

  // delete the bookmark
  if (!$conn->query("delete from bookmark where
                     username='".$user."' 
                    and bm_url='".$url."'")) {
     throw new Exception('Bookmark could not be deleted');
  }
  return true;
}

function recommend_urls($valid_user, $popularity = 1) {
  // We will provide semi intelligent recomendations to people
  // If they have an URL in common with other users, they may like
  // other URLs that these people like
  $conn = db_connect();

  // find other matching users
  // with an url the same as you
  // as a simple way of excluding people's private pages, and
  // increasing the chance of recommending appealing URLs, we
  // specify a minimum popularity level
  // if $popularity = 1, then more than one person must have
  // an URL before we will recomend it

  $query = "select bm_URL
	          from bookmark
	          where username in
	   	        (select distinct(b2.username)
              from bookmark b1, bookmark b2
		          where b1.username='".$valid_user."'
                and b1.username != b2.username
                and b1.bm_URL = b2.bm_URL)
	            and bm_URL not in
 		            (select bm_URL
				        from bookmark
				        where username='".$valid_user."')
            group by bm_url
            having count(bm_url)>".$popularity;

  if (!($result = $conn->query($query))) {
     throw new Exception('Could not find any bookmarks to recommend.');
  }

  if ($result->num_rows==0) {
     throw new Exception('Could not find any bookmarks to recommend.');
  }

  $urls = array();
  // build an array of the relevant urls
  for ($count=0; $row = $result->fetch_object(); $count++) {
     $urls[$count] = $row->bm_URL;
  }

  return $urls;
}
?>
