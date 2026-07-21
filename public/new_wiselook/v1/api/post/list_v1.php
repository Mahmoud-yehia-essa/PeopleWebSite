<?php
// post/list.php
// ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$profile_id = isset($input['profile_id']) ? (int)$input['profile_id'] : null;
$post_id = isset($input['post_id']) ? (int)$input['post_id'] : null;
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit = isset($input['limit']) ? (int)$input['limit'] : 20;
$filter = isset($input['filter']) ? $input['filter'] : 'recent';
$lang = isset($input['lang']) ? $input['lang'] : 'ar';

$db = new Database();
$conn = $db->getConnection();

// Main posts query
$sql = "";

if ($profile_id !== null) {
    // Profile view: pins with pin_scope = 'profile' for this profile
    $baseSelect = "
        SELECT 
            p.*,
            u.first_name, u.last_name, u.profile_picture,
            op.id AS original_post_id, op.content AS original_content, op.image AS original_image,
            op.video AS original_video, op.post_type_id AS original_post_type_id,
            ou.first_name AS original_first_name, ou.last_name AS original_last_name, 
            ou.profile_picture AS original_profile_picture
    ";
    
    // Pinned posts subquery for profile
    $pinnedSubquery = "
        (
            $baseSelect,
            1 as sort_priority,
            pp.pinned_at as sort_date
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN posts op ON op.id = p.parent_id
            LEFT JOIN users ou ON ou.id = op.user_id
            JOIN pinned_posts pp ON pp.post_id = p.id 
            WHERE pp.pin_scope = 'profile'
            AND p.user_id = :profile_id
            AND p.is_active = 1
    ";
    
    // Regular posts subquery for profile (excluding pinned)
    $regularSubquery = "
        (
            $baseSelect,
            2 as sort_priority,
            p.created_at as sort_date
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN posts op ON op.id = p.parent_id
            LEFT JOIN users ou ON ou.id = op.user_id
            WHERE p.is_active = 1
            AND (
                p.user_id = :profile_id 
                OR p.id IN (
                    SELECT m.content_id 
                    FROM mentions m
                    JOIN posts mp ON mp.id = m.content_id
                    WHERE m.user_id = :profile_id 
                      AND mp.is_active = 1
                )
            )
    ";
    
    // Add block checks for both subqueries if user_id is provided
    if ($user_id !== null) {
        $blockCondition = " AND p.user_id NOT IN (SELECT blocked_id FROM block WHERE blocker_id = :user_id) ";
        $blockCondition .= " AND p.user_id NOT IN (SELECT blocker_id FROM block WHERE blocked_id = :user_id) ";
        
        $pinnedSubquery .= $blockCondition;
        $regularSubquery .= $blockCondition;
    }
    
    // Close subqueries and add exclusion for pinned posts in regular subquery
    $pinnedSubquery .= "
        )
    ";
    
    $regularSubquery .= "
            AND p.id NOT IN (
                SELECT post_id FROM pinned_posts 
                WHERE pin_scope = 'profile'
                AND post_id IN (SELECT id FROM posts WHERE user_id = :profile_id)
            )
        )
    ";
    
    // Combine with UNION ALL and apply ordering
    $sql = $pinnedSubquery . " UNION ALL " . $regularSubquery . " 
            ORDER BY sort_priority ASC, sort_date DESC 
            LIMIT :offset, :limit";
            
} elseif ($post_id !== null) {
    // Single post view
    $sql = "
        SELECT 
            p.*,
            u.first_name, u.last_name, u.profile_picture,
            op.id AS original_post_id, op.content AS original_content, op.image AS original_image,
            op.video AS original_video, op.post_type_id AS original_post_type_id,
            ou.first_name AS original_first_name, ou.last_name AS original_last_name, 
            ou.profile_picture AS original_profile_picture
        FROM posts p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN posts op ON op.id = p.parent_id
        LEFT JOIN users ou ON ou.id = op.user_id
        WHERE p.is_active = 1
        AND p.id = :post_id
    ";
    
    if ($user_id !== null) {
        $sql .= " AND p.user_id NOT IN (SELECT blocked_id FROM block WHERE blocker_id = :user_id) ";
        $sql .= " AND p.user_id NOT IN (SELECT blocker_id FROM block WHERE blocked_id = :user_id) ";
    }
} else {
    // Home feed (no profile_id) - show posts with pin_scope = 'home' first
    $baseSelect = "
        SELECT 
            p.*,
            u.first_name, u.last_name, u.profile_picture,
            op.id AS original_post_id, op.content AS original_content, op.image AS original_image,
            op.video AS original_video, op.post_type_id AS original_post_type_id,
            ou.first_name AS original_first_name, ou.last_name AS original_last_name, 
            ou.profile_picture AS original_profile_picture
    ";
    
    // Pinned posts subquery for home feed
    $pinnedSubquery = "
        (
            $baseSelect,
            1 as sort_priority,
            pp.pinned_at as sort_date
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN posts op ON op.id = p.parent_id
            LEFT JOIN users ou ON ou.id = op.user_id
            JOIN pinned_posts pp ON pp.post_id = p.id 
            WHERE pp.pin_scope = 'home'
            AND p.is_active = 1
    ";
    
    // Regular posts subquery for home feed (excluding pinned)
    $regularSubquery = "
        (
            $baseSelect,
            2 as sort_priority,
            p.created_at as sort_date
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN posts op ON op.id = p.parent_id
            LEFT JOIN users ou ON ou.id = op.user_id
            WHERE p.is_active = 1
    ";
    
    // Add block checks for both subqueries if user_id is provided
    if ($user_id !== null) {
        $blockCondition = " AND p.user_id NOT IN (SELECT blocked_id FROM block WHERE blocker_id = :user_id) ";
        $blockCondition .= " AND p.user_id NOT IN (SELECT blocker_id FROM block WHERE blocked_id = :user_id) ";
        
        $pinnedSubquery .= $blockCondition;
        $regularSubquery .= $blockCondition;
    }
    
    // Close pinned subquery
    $pinnedSubquery .= "
        )
    ";
    
    // Add exclusion for pinned posts
    $regularSubquery .= "
            AND p.id NOT IN (
                SELECT post_id FROM pinned_posts 
                WHERE pin_scope = 'home'
            )
    ";
    
    // Apply ordering based on filter
    switch ($filter) {
        case 'most_liked':
            $regularSubquery .= " ORDER BY p.like_count DESC";
            break;
        case 'most_interactive':
            $regularSubquery .= " ORDER BY (p.like_count + p.comment_count + p.share_count + 
                     CASE WHEN p.post_type_id = 2 THEN 
                         (SELECT COALESCE(SUM(total_votes), 0) FROM polls WHERE post_id = p.id)
                     ELSE 0 END) DESC";
            break;
        case 'random':
            $regularSubquery .= " ORDER BY RAND()";
            break;
        case 'recent':
        default:
            $regularSubquery .= " ORDER BY p.created_at DESC";
            break;
    }
    
    // Close regular subquery
    $regularSubquery .= "
        )
    ";
    
    // Combine with UNION ALL and apply ordering
    $sql = $pinnedSubquery . " UNION ALL " . $regularSubquery . " 
            ORDER BY sort_priority ASC, sort_date DESC 
            LIMIT :offset, :limit";
}

$stmt = $conn->prepare($sql);

// Bind parameters based on query type
if ($user_id !== null) {
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}

if ($post_id !== null) {
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
}

if ($profile_id !== null) {
    $stmt->bindValue(':profile_id', $profile_id, PDO::PARAM_INT);
}

// Always bind offset and limit for listing queries
if ($post_id === null) {
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
}

$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($post_id !== null && empty($posts)) {
    echo json_encode(['success' => false, 'message' => 'Post not found or not active']);
    exit;
}

// Collect post IDs
$post_ids = [];
foreach ($posts as $post) {
    $post_ids[] = $post['id'];
    if (!empty($post['original_post_id'])) $post_ids[] = $post['original_post_id'];
}

// Fetch mentions for posts
$mentions_data = [];
if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $mentions_sql = "
        SELECT 
            m.content_id,
            u.id as user_id,
            u.first_name,
            u.last_name,
            u.profile_picture
        FROM mentions m
        JOIN users u ON u.id = m.user_id
        WHERE m.content_type_id = 1 
        AND m.content_id IN ($placeholders)
        AND u.is_active = 1
        ORDER BY m.created_at ASC
    ";
    $mentions_stmt = $conn->prepare($mentions_sql);
    $mentions_stmt->execute($post_ids);
    $mentions = $mentions_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mentions as $mention) {
        if (!isset($mentions_data[$mention['content_id']])) {
            $mentions_data[$mention['content_id']] = [];
        }
        $mentions_data[$mention['content_id']][] = [
            'user_id' => $mention['user_id'],
            'first_name' => $mention['first_name'],
            'last_name' => $mention['last_name'],
            'profile_picture' => $mention['profile_picture'] ? $uploadsPath . $mention['profile_picture'] : null,
            'full_name' => $mention['first_name'] . ' ' . $mention['last_name']
        ];
    }
}

// Fetch post media (images/videos)
$post_media = [];
if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $media_sql = "SELECT post_id, image, video FROM post_media 
                  WHERE is_active = 1 AND post_id IN ($placeholders)";
    $media_stmt = $conn->prepare($media_sql);
    $media_stmt->execute($post_ids);
    $media_rows = $media_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($media_rows as $m) {
        if (!isset($post_media[$m['post_id']])) {
            $post_media[$m['post_id']] = ['images' => [], 'videos' => []];
        }
        if (!empty($m['image'])) $post_media[$m['post_id']]['images'][] = $uploadsPath . $m['image'];
        if (!empty($m['video'])) $post_media[$m['post_id']]['videos'][] = $uploadsPath . $m['video'];
    }
}

// Fetch user saved posts
$user_saved_posts = [];
if ($user_id !== null && !empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $saved_sql = "SELECT post_id FROM saved_posts 
                 WHERE user_id = ? AND post_id IN ($placeholders)";
    $saved_stmt = $conn->prepare($saved_sql);
    $saved_stmt->execute(array_merge([$user_id], $post_ids));
    $user_saved_posts = $saved_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch user reactions
$user_reactions = [];
if ($user_id !== null && !empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $reaction_sql = "SELECT content_id FROM reactions 
                     WHERE user_id = ? AND content_type_id = 1 AND is_active = 1 
                     AND content_id IN ($placeholders)";
    $reaction_stmt = $conn->prepare($reaction_sql);
    $reaction_stmt->execute(array_merge([$user_id], $post_ids));
    $user_reactions = $reaction_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch polls
$poll_post_ids = [];
foreach ($posts as $post) {
    if ($post['post_type_id'] == 2) $poll_post_ids[] = $post['id'];
    if (!empty($post['original_post_id']) && $post['original_post_type_id'] == 2) {
        $poll_post_ids[] = $post['original_post_id'];
    }
}

$poll_data = [];
if (!empty($poll_post_ids)) {
    $placeholders = implode(',', array_fill(0, count($poll_post_ids), '?'));
    $poll_sql = "SELECT * FROM polls WHERE post_id IN ($placeholders)";
    $poll_stmt = $conn->prepare($poll_sql);
    $poll_stmt->execute($poll_post_ids);
    $polls = $poll_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($polls)) {
        $poll_ids = array_column($polls, 'id');
        $poll_placeholders = implode(',', array_fill(0, count($poll_ids), '?'));
     
        // $poll_option_sql = "
        //     SELECT po.*, 
        //         (SELECT COUNT(*) FROM poll_responses pr WHERE pr.poll_option_id = po.id) AS vote_count,
        //         (SELECT COUNT(*) FROM poll_responses pr WHERE pr.poll_option_id = po.id AND pr.user_id = ?) AS is_selected
        //     FROM poll_options po
        //     WHERE po.poll_id IN ($poll_placeholders)";
        // $poll_option_stmt = $conn->prepare($poll_option_sql);
        // $poll_option_stmt->execute(array_merge([$user_id], $poll_ids));
        // $poll_options = $poll_option_stmt->fetchAll(PDO::FETCH_ASSOC);

        // foreach ($polls as $poll) {
        //     $poll_data[$poll['post_id']] = [
        //         'question' => $poll['question'],
        //         'expires_at' => $poll['expires_at'],
        //         'total_votes' => (int)$poll['total_votes'],
        //         'options' => []
        //     ];
        // }

        // foreach ($poll_options as $option) {
        //     foreach ($polls as $poll) {
        //         if ($poll['id'] == $option['poll_id']) {
        //             $poll_data[$poll['post_id']]['options'][] = [
        //                 'id' => $option['id'],
        //                 'content' => $option['content'],
        //                 'vote_count' => (int)$option['vote_count'],
        //                 'is_selected' => (int)$option['is_selected']
        //             ];
        //             break;
        //         }
        //     }
        // }
        
        
        
        
        
        
        // تعديل استعلام خيارات التصويت
$poll_option_sql = "
    SELECT po.*, 
        (SELECT COUNT(*) FROM poll_responses pr WHERE pr.poll_option_id = po.id) AS vote_count,
        (SELECT COUNT(*) FROM poll_responses pr WHERE pr.poll_option_id = po.id AND pr.user_id = ?) AS is_selected,
        (SELECT COUNT(DISTINCT pr.user_id) FROM poll_responses pr WHERE pr.poll_option_id = po.id) AS unique_voters_count
    FROM poll_options po
    WHERE po.poll_id IN ($poll_placeholders)";
    
$poll_option_stmt = $conn->prepare($poll_option_sql);
$poll_option_stmt->execute(array_merge([$user_id], $poll_ids));
$poll_options = $poll_option_stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب آخر 3 مصوتين لكل خيار
$recent_voters = [];
if (!empty($poll_ids)) {
    $recent_voters_sql = "
        SELECT 
            pr.poll_option_id,
            u.id as user_id,
            u.first_name,
            u.last_name,
            u.profile_picture
        FROM poll_responses pr
        JOIN users u ON u.id = pr.user_id
        WHERE pr.poll_option_id IN (
            SELECT id FROM poll_options WHERE poll_id IN ($poll_placeholders)
        )
        AND u.is_active = 1
        ORDER BY pr.created_at DESC
    ";
    
    $recent_voters_stmt = $conn->prepare($recent_voters_sql);
    $recent_voters_stmt->execute($poll_ids);
    $voters_rows = $recent_voters_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تنظيم المصوتين حسب poll_option_id
    foreach ($voters_rows as $voter) {
        if (!isset($recent_voters[$voter['poll_option_id']])) {
            $recent_voters[$voter['poll_option_id']] = [];
        }
        if (count($recent_voters[$voter['poll_option_id']]) < 3) { // نأخذ فقط آخر 3
            $recent_voters[$voter['poll_option_id']][] = [
                'user_id' => $voter['user_id'],
                'profile_picture' => $voter['profile_picture'] ? $uploadsPath . $voter['profile_picture'] : null,
                'first_name' => $voter['first_name'],
                'last_name' => $voter['last_name']
            ];
        }
    }
}

// ثم في حلقة تجهيز البيانات
foreach ($poll_options as $option) {
    foreach ($polls as $poll) {
        if ($poll['id'] == $option['poll_id']) {
            $poll_data[$poll['post_id']]['options'][] = [
                'id' => $option['id'],
                'content' => $option['content'],
                'vote_count' => (int)$option['vote_count'],
                'is_selected' => (int)$option['is_selected'],
                'recent_voters' => isset($recent_voters[$option['id']]) ? $recent_voters[$option['id']] : [] // آخر 3 مصوتين
            ];
            break;
        }
    }
}



    }
}

// Prepare final posts
$processed_posts = [];
foreach ($posts as $post) {

    // combine post.image + post_media in single array "media"
    $media = [];
    if (!empty($post['image'])) $media[] = ['path' => $uploadsPath . $post['image'], 'type' => 'image'];
    if (!empty($post['video'])) $media[] = ['path' => $uploadsPath . $post['video'], 'type' => 'video'];

    if (isset($post_media[$post['id']]['images'])) {
        foreach ($post_media[$post['id']]['images'] as $img)
            $media[] = ['path' => $img, 'type' => 'image'];
    }
    if (isset($post_media[$post['id']]['videos'])) {
        foreach ($post_media[$post['id']]['videos'] as $vid)
            $media[] = ['path' => $vid, 'type' => 'video'];
    }

    // Get mentions for this post
    $mentions = isset($mentions_data[$post['id']]) ? $mentions_data[$post['id']] : [];

    $processed_post = [
        'post_id' => $post['id'],
        'user_id' => $post['user_id'],
        'content' => $post['content'],
        'media' => $media,
        'like_count' => $post['like_count'],
        'comment_count' => $post['comment_count'],
        'share_count' => $post['share_count'],
        'parent_id' => $post['parent_id'],
        'post_type_id' => $post['post_type_id'],
        'created_at' => $post['created_at'],
        'first_name' => $post['first_name'],
        'last_name' => $post['last_name'],
        'profile_picture' => $post['profile_picture'] ? $uploadsPath . $post['profile_picture'] : null,
        'is_reacted' => in_array($post['id'], $user_reactions),
        'is_saved' => in_array($post['id'], $user_saved_posts),
        'time_ago' => $db->timeAgo($post['created_at'], $lang),
        'mentions' => $mentions,
        'question' => null,
        'expires_at' => null,
        'total_votes' => 0,
        'options' => []
    ];

    // Add pin info if available
    if (isset($post['sort_priority'])) {
        $processed_post['is_pinned'] = ($post['sort_priority'] == 1);
        $processed_post['pin_scope'] = ($profile_id !== null) ? 'profile' : 'home';
    }

    if ($post['post_type_id'] == 2 && isset($poll_data[$post['id']])) {
        $processed_post['question'] = $poll_data[$post['id']]['question'];
        $processed_post['expires_at'] = $poll_data[$post['id']]['expires_at'];
        $processed_post['total_votes'] = $poll_data[$post['id']]['total_votes'];
        $processed_post['options'] = $poll_data[$post['id']]['options'];
    }

    // original post
    if (!empty($post['parent_id'])) {
        $original_media = [];

        if (!empty($post['original_image'])) 
            $original_media[] = ['path' => $uploadsPath . $post['original_image'], 'type' => 'image'];
        if (!empty($post['original_video'])) 
            $original_media[] = ['path' => $uploadsPath . $post['original_video'], 'type' => 'video'];

        if (isset($post_media[$post['original_post_id']]['images'])) {
            foreach ($post_media[$post['original_post_id']]['images'] as $img)
                $original_media[] = ['path' => $img, 'type' => 'image'];
        }
        if (isset($post_media[$post['original_post_id']]['videos'])) {
            foreach ($post_media[$post['original_post_id']]['videos'] as $vid)
                $original_media[] = ['path' => $vid, 'type' => 'video'];
        }

        // Get mentions for original post
        $original_mentions = isset($mentions_data[$post['original_post_id']]) ? $mentions_data[$post['original_post_id']] : [];

        $original = [
            'post_id' => $post['original_post_id'],
            'content' => $post['original_content'],
            'media' => $original_media,
            'post_type_id' => $post['original_post_type_id'],
            'first_name' => $post['original_first_name'],
            'last_name' => $post['original_last_name'],
            'profile_picture' => $post['original_profile_picture'] ? $uploadsPath . $post['original_profile_picture'] : null,
            'mentions' => $original_mentions,
            'question' => null,
            'expires_at' => null,
            'total_votes' => 0,
            'options' => []
        ];

        if ($post['original_post_type_id'] == 2 && isset($poll_data[$post['original_post_id']])) {
            $original['question'] = $poll_data[$post['original_post_id']]['question'];
            $original['expires_at'] = $poll_data[$post['original_post_id']]['expires_at'];
            $original['total_votes'] = $poll_data[$post['original_post_id']]['total_votes'];
            $original['options'] = $poll_data[$post['original_post_id']]['options'];
        }

        $processed_post['original_post'] = $original;
    }

    $processed_posts[] = $processed_post;
}

// Output
echo json_encode([
    'success' => true,
    'message' => $post_id !== null ? 'Post fetched successfully' : 'Posts fetched successfully',
    'data' => $post_id !== null ? ($processed_posts[0] ?? null) : $processed_posts,
    'filter' => $filter
]);
exit;
?>