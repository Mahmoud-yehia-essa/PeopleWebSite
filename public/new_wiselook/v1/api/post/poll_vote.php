<?php
// poll/vote.php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

include_once __DIR__ . '/../db.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Required fields
$required = ['user_id', 'option_id'];
foreach ($required as $field) {
  if (empty($input[$field])) {
    http_response_code(400);
    echo json_encode([
      'success' => false,
      'message' => "Missing required field: $field"
    ]);
    exit;
  }
}

$user_id = (int)$input['user_id'];
$option_id = (int)$input['option_id'];

$db = new Database();
$conn = $db->getConnection();

try {
  // Begin transaction
  $conn->beginTransaction();

  // Check if user already voted in this poll
  $check_stmt = $conn->prepare("
        SELECT pr.id, pr.poll_option_id as previous_option_id, po.poll_id 
        FROM poll_responses pr
        JOIN poll_options po ON pr.poll_option_id = po.id
        WHERE user_id = :user_id 
        AND po.poll_id = (
            SELECT poll_id FROM poll_options WHERE id = :option_id
        )
    ");
  $check_stmt->execute([
    ':user_id' => $user_id,
    ':option_id' => $option_id
  ]);
  $existing_vote = $check_stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_vote) {
    if ($existing_vote['previous_option_id'] == $option_id) {
      // User is voting for the same option - delete the vote
      $delete_stmt = $conn->prepare("
                DELETE FROM poll_responses 
                WHERE id = :id
            ");
      $delete_stmt->execute([':id' => $existing_vote['id']]);

      // Decrement vote counts
      $update_option_stmt = $conn->prepare("
                UPDATE poll_options 
                SET vote_count = vote_count - 1 
                WHERE id = :option_id
            ");
      $update_option_stmt->execute([':option_id' => $option_id]);

      $update_poll_stmt = $conn->prepare("
                UPDATE polls 
                SET total_votes = total_votes - 1 
                WHERE id = :poll_id
            ");
      $update_poll_stmt->execute([':poll_id' => $existing_vote['poll_id']]);

      $conn->commit();

      echo json_encode([
        'success' => true,
        'message' => 'Vote removed successfully',
        'action' => 'removed'
      ]);
      exit;
    } else {
      // User is changing their vote - update the option
      $update_stmt = $conn->prepare("
                UPDATE poll_responses 
                SET poll_option_id = :new_option_id 
                WHERE id = :id
            ");
      $update_stmt->execute([
        ':new_option_id' => $option_id,
        ':id' => $existing_vote['id']
      ]);

      // Update vote counts - decrement old option, increment new option
      $decrement_option_stmt = $conn->prepare("
                UPDATE poll_options 
                SET vote_count = vote_count - 1 
                WHERE id = :old_option_id
            ");
      $decrement_option_stmt->execute([':old_option_id' => $existing_vote['previous_option_id']]);

      $increment_option_stmt = $conn->prepare("
                UPDATE poll_options 
                SET vote_count = vote_count + 1 
                WHERE id = :new_option_id
            ");
      $increment_option_stmt->execute([':new_option_id' => $option_id]);

      // No change to total_votes since it's just a vote change
      $conn->commit();

      echo json_encode([
        'success' => true,
        'message' => 'Vote updated successfully',
        'action' => 'updated'
      ]);
      exit;
    }
  } else {
    // New vote - insert the record
    $insert_stmt = $conn->prepare("
            INSERT INTO poll_responses (user_id, poll_option_id)
            VALUES (:user_id, :option_id)
        ");
    $insert_stmt->execute([
      ':user_id' => $user_id,
      ':option_id' => $option_id
    ]);

    // Get poll_id for the new option
    $poll_stmt = $conn->prepare("
            SELECT poll_id FROM poll_options WHERE id = :option_id
        ");
    $poll_stmt->execute([':option_id' => $option_id]);
    $poll = $poll_stmt->fetch(PDO::FETCH_ASSOC);

    // Increment vote counts
    $update_option_stmt = $conn->prepare("
            UPDATE poll_options 
            SET vote_count = vote_count + 1 
            WHERE id = :option_id
        ");
    $update_option_stmt->execute([':option_id' => $option_id]);

    $update_poll_stmt = $conn->prepare("
            UPDATE polls 
            SET total_votes = total_votes + 1 
            WHERE id = :poll_id
        ");
    $update_poll_stmt->execute([':poll_id' => $poll['poll_id']]);

    $conn->commit();

    echo json_encode([
      'success' => true,
      'message' => 'Vote recorded successfully',
      'action' => 'added'
    ]);
  }
} catch (PDOException $e) {
  $conn->rollBack();
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Database error: ' . $e->getMessage()
  ]);
}
