<?php
@set_time_limit(0);
header('Content-Type: text/plain');

function getTokenFromCookie($cookie) {
    $ch = curl_init("https://business.facebook.com/content_management");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "cookie: $cookie",
        "user-agent: Mozilla/5.0"
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/EAAG\w{50,}/', $response, $matches)) {
        return $matches[0];
    }
    return null;
}

function shareToFacebook($cookie, $token, $id_share) {
    $url = "https://graph.facebook.com/me/feed?link=https://m.facebook.com/$id_share&published=0&access_token=$token";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["cookie: $cookie"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function commentOnFacebook($cookie, $token, $postId, $message) {
    $url = "https://graph.facebook.com/$postId/comments?message=" . urlencode($message) . "&access_token=$token";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["cookie: $cookie"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_share = trim($_POST['id_share'] ?? '');
    $limit = intval($_POST['limit'] ?? 1);
    $action = $_POST['action_type'] ?? 'share';
    $commentContent = trim($_POST['comment_content'] ?? '');

    if (empty($id_share)) {
        echo "❌ Thiếu ID bài viết.";
        exit;
    }

    if (!isset($_FILES['cookie_file']) || !file_exists($_FILES['cookie_file']['tmp_name'])) {
        echo "❌ Không có file cookie.";
        exit;
    }

    $tmp = $_FILES['cookie_file']['tmp_name'];
    $cookies = file($tmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (empty($cookies)) {
        echo "❌ File cookie rỗng.";
        exit;
    }

    $cookie = array_shift($cookies);
    $token = getTokenFromCookie($cookie);

    if (!$token) {
        echo "❌ Không lấy được token.";
        exit;
    }

    if ($action === 'share') {
        $res = shareToFacebook($cookie, $token, $id_share);
        if (strpos($res, '"error"') !== false) {
            echo "❌ Lỗi share: $res";
        } else {
            echo "✅ SHARE thành công";
        }
    } elseif ($action === 'comment') {
        if (empty($commentContent)) {
            echo "❌ Thiếu nội dung comment.";
            exit;
        }
        $res = commentOnFacebook($cookie, $token, $id_share, $commentContent);
        if (strpos($res, '"error"') !== false) {
            echo "❌ Lỗi comment: $res";
        } else {
            echo "💬 COMMENT thành công";
        }
    } else {
        echo "❌ Chức năng không hợp lệ.";
    }

    @unlink($tmp);
}
