<?php
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Headers: Content-Type, Accept');

  // database connection init
  $server_name = "";
  $server_username = "";
  $server_password = "";
  $database_name = "";

  $conn = mysqli_connect(
    $server_name, 
    $server_username, 
    $server_password, 
    $database_name
  );

  if (!$conn) {
    die("Internal Server Error occurred.<br>Please try again later.");
  } else {
    $time_now = time();
    $query = "DELETE from temp_votes WHERE exp_time < '$time_now';";
    $result = mysqli_query($conn, $query);
  }
?>