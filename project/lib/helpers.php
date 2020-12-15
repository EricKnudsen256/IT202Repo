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

function getBalance() {
    if (is_logged_in() && isset($_SESSION["balance"])) {
        return $_SESSION["balance"];
    }
    return 0;
}

function UpdatePoints() {

$db = getDB();
$stmt = $db->prepare("UPDATE Scores SET score = (SELECT SUM(points_change) from PointsHistory WHERE user_id = :id) where user_id = :id");
$stmt->execute([":id"=>get_user_id()]);

}

function InitScoreEntry()
{
    $user = get_user_id();
    $score = 0;
    $user_id = get_user_id();
    $date = date('Y-m-d H:i:s');

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Scores(user_id, score, created) VALUES(:user_id, :score, :created)");
	$r = $stmt->execute([
		":user_id"=>$user_id,
		":score"=>$score,
		":created"=>$date
	]);
}

function UpdateCompetitions ()
{
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Competitions WHERE expires < DATE(NOW()) AND paid_out = 0");
	$r = $stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//res is competition array,  can access each data
	foreach($results as $competition)
	{
		$endDate = $competition["expires"];
		$stmt2 = $db->prepare("SELECT user_id FROM UserCompetitions WHERE competition_id = :cid");
		$r2 = $stmt2->execute([":cid"=>$competition["id"]]);
		$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

		$first = 0;
		$firstID = 0;
		$second= 0;
		$secondID = 0;
		$third = 0;
		$thirdID = 0;
		

		if(count($result2) < 3)
		{
			$stmtPayout = $db->prepare("UPDATE Competitions Set paid_out = 1 WHERE id = :cid");
			$rPayout = $stmtPayout->execute([":cid"=>$competition["id"]]);
			$resultPayout = $stmtPayout->fetchAll(PDO::FETCH_ASSOC);
			continue;
		}
		
		foreach($result2 as $participant)
		{

			$totalPoints = 0;
			$stmt3 = $db->prepare("SELECT points_change FROM PointsHistory WHERE user_id = :id AND created < :endDate");  
			$r3 = $stmt3->execute([":id"=>$participant["user_id"], ":endDate"=>$endDate]);
			$pointArray = $stmt3->fetchAll(PDO::FETCH_ASSOC);

			foreach($pointArray as $value)
			{
				$totalPoints = $totalPoints + $value["points_change"];
			}

			if($totalPoints > $first)
			{
				$third = $second;
				$thirdID = $secondID;
				$second = $first;
				$secondID = $firstID;
				$first = $totalPoints;
				$firstID = $participant["user_id"];
			}
			else if($totalPoints > $second)
			{
				$third = $second;
				$thirdID = $secondID;
				$second = $totalPoints;
				$secondID = $participant["user_id"];
			}
			else if($totalPoints > $third)
			{
				$third = $totalPoints;
				$thirdID = $participant["user_id"];
			}
		}
		

		if($competition["third_place_per"] != 0)
		{
			$payout = Ceil($competition["reward"] * $competition["third_place_per"]);
			
			   
    			$reason = "Competition: Third Place";
  			$user_id = $thirdID;
    			$date = date('Y-m-d H:i:s');

    			$stmt = $db->prepare("INSERT INTO PointsHistory(user_id, points_change, reason, created) VALUES(:user_id, :payout, :reason, :created)");
			$r = $stmt->execute([
                	 ":user_id"=>$user_id,
               		 ":payout"=>$payout,
                	 ":reason"=>$reason,
               		 ":created"=>$date
        		]);

		}

		if($competition["second_place_per"] != 0)
		{
			$payout = Ceil($competition["reward"] * $competition["second_place_per"]);
			
			   
    			$reason = "Competition: Second Place";
  			$user_id = $secondID;
    			$date = date('Y-m-d H:i:s');

    			$stmt = $db->prepare("INSERT INTO PointsHistory(user_id, points_change, reason, created) VALUES(:user_id, :payout, :reason, :created)");
			$r = $stmt->execute([
                	 ":user_id"=>$user_id,
               		 ":payout"=>$payout,
                	 ":reason"=>$reason,
               		 ":created"=>$date
        		]);

		}

		if($competition["first_place_per"] != 0)
		{
			$payout = Ceil($competition["reward"] * $competition["first_place_per"]);
			safer_echo($firstID);
			   
    			$reason = "Competition: First Place";
  			$user_id = $firstID;
    			$date = date('Y-m-d H:i:s');

    			$stmt = $db->prepare("INSERT INTO PointsHistory(user_id, points_change, reason, created) VALUES(:user_id, :payout, :reason, :created)");
			$r = $stmt->execute([
                	 ":user_id"=>$user_id,
               		 ":payout"=>$payout,
                	 ":reason"=>$reason,
               		 ":created"=>$date
        		]);

		}

		safer_echo($firstID);
		safer_echo($secondID);
		safer_echo($thirdID);
		
		$stmtPayout = $db->prepare("UPDATE Competitions Set paid_out = 1 WHERE id = :cid");
		$rPayout = $stmtPayout->execute([":cid"=>$competition["id"]]);
		$resultPayout = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

}





//end flash
?>
