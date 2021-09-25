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
    $creds = $_GET['creds'];
    if (!empty($creds)) {
      $exp_time = time() + 60 * 60;
      $query = "INSERT INTO temp_votes (ip_address, exp_time) VALUES ('$creds', '$exp_time');";
      $result = mysqli_query($conn, $query);
      if (!$result) {
        echo json_encode(array('status' => "500"));
      } else {
        echo json_encode(array('status' => "200"));
      }
    } else {
      die("Error");
    }
  }
?>