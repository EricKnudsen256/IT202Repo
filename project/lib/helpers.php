<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function getWeeklyScores() {
	$currentTime = date('Y-m-d H:i:s');
	$oneWeekAgo = strtotime("-1 week");
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Scores WHERE :oneWeekAgo > created order by score ASC LIMIT 10");
	$r = $stmt->execute([
		":oneWeekAgo"=>$oneWeekAgo
	]);
	
	if ($r) {
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getMonthlyScores() {
	$currentTime = date('Y-m-d H:i:s');
	$oneWeekAgo = strtotime("-1 month");
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Scores WHERE :oneWeekAgo > created order by score ASC LIMIT 10");
	$r = $stmt->execute([
		":oneWeekAgo"=>$oneWeekAgo
	]);
	
	if ($r) {
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getAllTimeScores() {
	$currentTime = date('Y-m-d H:i:s');

	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Scores order by score ASC LIMIT 10");
	$r = $stmt->execute([
	]);
	
	if ($r) {
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}
//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}
function getURL($path) {
    if (substr($path, 0, 1) == "/") {
        return $path;
    }
    return $_SERVER["CONTEXT_PREFIX"] . "/IT202/project/$path";
}

function UpdatePoints() {

$db = getDB();
$stmt = $db->prepare("UPDATE Scores SET score = (SELECT SUM(points_change) from PointsHistory WHERE user_id = :id) where user_id = :id");
$stmt->execute([":id"=>get_user_id()]);

}


//end flash
?>
