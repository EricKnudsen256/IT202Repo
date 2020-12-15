<?php
//since API is 100% server, we won't include navbar or flash
require_once(__DIR__ . "/../lib/helpers.php");
if (!is_logged_in()) {
    die(header(':', true, 403));
}
$testing = false;
if (isset($_GET["test"])) {
    $testing = true;
}


$user = get_user_id();
    $points_change = 10;
    $reason = "Game points";
    $user_id = get_user_id();
    $date = date('Y-m-d H:i:s');

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO PointsHistory(user_id, points_change, reason, created) VALUES(:user_id, :points_change, :reason, :created)");
	$r = $stmt->execute([
		":user_id"=>$user_id,
		":points_change"=>$points_change,
		":reason"=>$reason,
		":created"=>$date
	]);
    if ($r) {
	UpdatePoints();
        $response = ["status" => 200];
        echo json_encode($response);
        die();
    }
    else {
        $e = $stmt->errorInfo();
        $response = ["status" => 400, "error" => $e];
        echo json_encode($response);
        die();
    }

?>


