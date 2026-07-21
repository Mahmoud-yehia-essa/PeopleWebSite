<?php
// Define DB
define('DB_HOST', "82.29.180.63");
define('DB_USER', 'wise');
define('DB_PASS', 'tag.amc.2013');
define('DB_NAME', 'wise_db');
define('DB_PORT', '5011');

class Database
{
  private $conn;

  // Constructor of class, here we call function connect()
  public function __construct()
  {
    $this->connect();
  }

  // private function connect() {
  //     $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

  //     if (!$this->conn) {
  //         die('Could not connect: ' . mysqli_connect_error());
  //     }

  //     // Set connection charset to UTF-8
  //     $this->conn->set_charset("utf8");
  // }

  // here we start connection
  private function connect()
  {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8";
    try {
      $this->conn = new PDO($dsn, DB_USER, DB_PASS);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      die("Database connection failed: " . $e->getMessage());
    }
  }

  // getConnection to use in index
  public function getConnection()
  {
    return $this->conn;
  }

  // Sanitize user input
  public function sanitize($input)
  {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    // $input = mysqli_real_escape_string($this->conn, $input);
    $input = $this->conn->quote($input);
    $input = trim($input, "'");
    return $input;
  }

  // Executes a query and returns the result
  public function execute($query)
  {
    return $this->conn->query($query);
  }

  // Executes a query and returns the last inserted ID
  public function lastIdInsert($query)
  {
    $this->conn->query($query);
    return $this->conn->insert_id;
  }

  // Returns an array of results for a query
  public function getResults($query)
  {
    $rows = array();
    $result = $this->conn->query($query);

    if ($result) {
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
      }
    }
    return $rows;
  }

  // Check if a user exists in the users table
  public function checkUserExists($user_id)
  {
    $query = "SELECT id FROM `users` WHERE id = '$user_id' AND is_active = 1";
    $result = $this->getResults($query);

    if (empty($result)) {
      $response = [
        'success' => false,
        'message' => "User doesn't exist."
      ];
      echo json_encode($response);
      exit;
    }
  }

  public function checkPostExists($post_id)
  {
    $query = "SELECT id FROM `posts` WHERE id = '$post_id' AND is_active = 1";
    $result = $this->getResults($query);

    if (empty($result)) {
      $response = [
        'success' => false,
        'message' => "Post doesn't exist or has been deleted."
      ];
      echo json_encode($response);
      exit;
    }
  }
  public function checkCommentExists($comment_id)
  {
    $query = "SELECT id FROM `comments` WHERE id = '$comment_id' AND is_active = 1";
    $result = $this->getResults($query);

    if (empty($result)) {
      $response = [
        'success' => false,
        'message' => "Comment doesn't exist or has been deleted."
      ];
      echo json_encode($response);
      exit;
    }
  }

  // Returns a human-readable time difference (e.g., "2 days ago")
  public function timeAgo($datetime)
  {
    $timestamp = strtotime($datetime);
    $currentTime = time();
    $timeDiff = $currentTime - $timestamp;

    $seconds = $timeDiff;
    $minutes = round($seconds / 60);
    $hours   = round($seconds / 3600);
    $days    = round($seconds / 86400);
    $weeks   = round($seconds / 604800);
    $months  = round($seconds / 2629800); // Approximate value
    $years   = round($seconds / 31557600); // Approximate value

    if ($seconds < 60) {
      return "Just now";
    } elseif ($minutes < 60) {
      return "$minutes minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($hours < 24) {
      return "$hours hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($days < 7) {
      return "$days day" . ($days > 1 ? "s" : "") . " ago";
    } elseif ($weeks < 4) {
      return "$weeks week" . ($weeks > 1 ? "s" : "") . " ago";
    } elseif ($months < 12) {
      return "$months month" . ($months > 1 ? "s" : "") . " ago";
    } else {
      return "$years year" . ($years > 1 ? "s" : "") . " ago";
    }
  }
}
