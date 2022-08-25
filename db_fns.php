<?php

function db_connect() {
   if ($_SERVER['SERVER_NAME'] == 'localhost0'){ //localhost0 used to force excecution of else on 'localhost'
    $result = new mysqli('localhost', 'root', '', 'dog_weight_tracker');
   }
   else{
    $result = new mysqli('162.241.24.68', 'mystand6_freelancer', '!Freelancer1234!', 'mystand6_dog_weight_tracker');
   }
   if (!$result) {
     throw new Exception('Could not connect to database server');
   } else {
     return $result;
   }
}

?>
