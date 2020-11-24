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
    $score = 10;
    $user_id = get_user_id();
    $date = date('Y-m-d H:i:s');

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Scores(user_id, score, created) VALUES(:user_id, :score, :created)");
	$r = $stmt->execute([
		":user_id"=>$user_id,
		":score"=>$score,
		":created"=>$date
	]);
    if ($r) {
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


