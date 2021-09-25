<?php
 
 include('helpers.php');
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
  }
  
  # Run script
  $path = "data/data.csv";
  $lines = getData($path);

  $commas = array();
  foreach ($lines as $val) {
    $comma = explode(",", $val);
    $commas[] = $comma;
  }

  $labels = array();
  foreach ($commas as $line) {
    $labels[] = $line[0];
  }
  $values = array();
  $downs = array();
  foreach ($commas as $line) {
    $val = intval($line[1]);
    $values[] = $val;
    if ($val < 500) {
        $downs[] = $val;
    }
  }

  $avg = averageOf($values);
  $max_value = max($values);
  $min_value = min($values);
  $last_val = end($values);
  
  $downCount = count($downs);
  $totalDownSeconds = (60 * $downCount) / 60;
  $uptime = ((2520 - $totalDownSeconds) / 2520) * 100;
  #$splits = array_chunk($values, 21);
  #$twoHourArray = $splits[21];

  $prev_status = serverWasOnline($min_value);
  $cur_status = serverIsOnline($values);
  $status = calculateStatus($cur_status);


  // check if already voted in the last hour
  $ip_address = $_SERVER['REMOTE_ADDR'];
  $hasVoted = false;
  
  $check_ip = "SELECT ip_address, exp_time FROM temp_votes WHERE ip_address='$ip_address';";
  $check_ip = mysqli_query($conn, $check_ip);
  if (!$check_ip) {
    // Error casting Vote
  } else {
    if (mysqli_num_rows($check_ip) >= 1) {
      $row = mysqli_fetch_row($check_ip);
      if ($row[0] != $ip_address) {
        $hasVoted = false;
      } else {
        if ($row[1] < time()) {
          $hasVoted = false;
          $delete_vote = "DELETE FROM temp_votes WHERE ip_address='$ip_address';";
          $delete_vote = mysqli_query($conn, $delete_vote);
        } else {
          $hasVoted = true;
        }
      }
    }
  }
  
  // check how many people voted in the last hour
  $votingCount = 0;
  
  $all_entries = "SELECT COUNT(*) FROM temp_votes;";
  $all_entries = mysqli_query($conn, $all_entries);
  if (!$all_entries) {
    // Error casting Vote
  } else {
    if (mysqli_num_rows($all_entries) >= 1) {
      $row = mysqli_fetch_row($all_entries);
      $votingCount = $row[0];
    }
  }
  
  // btn_yes function
  if (array_key_exists('btn_yes', $_POST)) {
    $fetch = file_get_contents("votingsys/vote?creds=".$ip_address);
    header("location: /");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-type" content="text/html" charset="UTF-8" />
  <meta name="description" content="A detailed Growtopia game statistics for online player count, also a down detector for the game.">
  <meta name="keywords" content="growtopia, game, statistics, growstats">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#584C68" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <title>Growstats - Latest statistics of online player count</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
  <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
  <div class="title-header">
    <h1 style="text-align:center;font-family:Verdana;">
      :: Growtopia Statistics ::
      <p style="text-align:center;font-weight:normal;">Data according to the last 42 hours (New York Time, UTC-4)</p>
    </h1>
  </div><br>

  <div style="justify-content:center;display:flex;flex-direction:row-reverse;">
    <div class="ui label" style="margin:6px;">
      <i class="github icon"></i>
      <a target="_blank" href="https://github.com/hackershaven/Growstats" class="detail">View Github Repo</a>
    </div>
    <div id="modal-btn" class="ui label" style="margin:6px;">
      <i class="file code outline icon"></i>
      <a class="detail">View Tech Stack</a>
    </div>
  </div><br>

  <div class="mobile-msg">
    <div class="ui container">
      <div class="ui negative message">
        <div class="header">
          View this website on a Desktop computer for better experience
        </div>
        <p>Some elements such as the real-time chart might be missing in mobile view.</p>
      </div>
    </div>
  </div>

  <?php
    if ($hasVoted != true) {
      echo '<br><div class="ui container">
        <div class="ui message">
        <i class="close icon"></i>
        <div class="header">
            Is Growtopia down for you?
        </div>
        <p>Help others know for sure by casting your vote. You can only vote once an hour.</p>
        <div class="ui buttons">
            <form method="POST">
            <input class="ui positive button" type="submit" name="btn_yes" value="Yes, It\'s down for me" />
            </form>
        </div>
        </div>
    </div>';
    }
  ?><br>
  
  <div class="ui container">
    <div class="ui icon message">
      <i class="info circle icon"></i>
      <div class="content">
        <div class="header">Server status:</div>
        <b><?php echo $status ?></b>
      </div>
    </div>
  </div><br>
  
  <div class="ui container">
    <div class="ui icon message">
      <i class="ticket alternate icon"></i>
      <div class="content">
        <div class="header"><?php echo $votingCount ?> people voted Growtopia went down in the last hour</div>
      </div>
    </div>
  </div><br>

  <div class="boxes">
    <div class="parent">
      <div class="child">
        <b class="head">Latest Count</b>
        <hr>
        <b class="par"><?php echo $last_val ?></b>
        <p>(as of <?php echo $labels[count($labels) - 1] ?>)</p>
      </div>
      <div class="child">
        <b class="head">Lowest Count</b>
        <hr>
        <b class="par"><?php echo $min_value ?></b>
      </div>
      <div class="child">
        <b class="head">Highest Count</b>
        <hr>
        <b class="par"><?php echo $max_value ?></b>
      </div>
      <div class="child">
        <b class="head">Average Count</b>
        <hr>
        <b class="par"><?php echo round($avg) ?></b>
      </div>
      <div class="child">
        <b class="head">Down Counts</b>
        <hr>
        <b class="par"><?php echo $downCount ?></b>
      </div>
      <div class="child">
        <b class="head">Uptime</b>
        <hr>
        <b class="par"><?php echo strval(round($uptime, 3) . "%") ?></b>
      </div>
    </div>
  </div><br>

  <center>
    <div class="chart">
      <canvas id="barChart" width="900" height="380"></canvas>
    </div>
  </center>
  
  <?php include('stack.modal') ?>

  <script>
    // modal
    $('#modal-btn').on('click', function () {
        $('.ui.modal').modal('show');
    });
    
    // voting box
    $('.message .close').on('click', function () {
        $(this).closest('.message').transition('fade');
    });
    $('.message .button').on('click', function () {
        $(this).closest('.message').transition('fade');
    });

    // chart
    var ctx = document.getElementById("barChart").getContext("2d");
    var barChart = new Chart(ctx, {
      type: "bar",
      data: {
      labels: <?php echo json_encode($labels) ?>,
      datasets: [{
        label: "Online count",
        data: <?php echo json_encode($values) ?>,
        fill: true,
        borderColor: "rgb(75, 192, 192)",
        backgroundColor: "rgb(66, 135, 245)",
        lineTension: 0.1
      },
    ]
  },
  options: {
    responsive: true,
    scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true,
            fontColor: "white"
          }
        }],
        xAxes: [{
          ticks: {
            fontColor: "white"
          }
        }]
      },
      legend: {
        labels: {
          fontColor: "white"
        }
      }
    }
  })
  </script>
</body>
</html>
