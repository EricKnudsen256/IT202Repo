<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
//we'll put this at the top so both php block have access to it
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>



<?php
if(isset($_POST["save"])) {
	//TODO add proper validation/checks
	$user_id = get_user_id();
	$score = $_POST["score"];
	$time = date('Y-m-d H:i:s');//calc
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE Scores set user_id=:user_id, score=:score, created=:time where id=:id");
		$r = $stmt->execute([
			":user_id"=>$user_id,
			":score"=>$score,
			":time"=>$time,
			":id"=>$id
		]);
		if($r) {
			flash("Created successfully with id: " . $id);
		}
		else {
			$e = $stmt->errorInfo();
			flash("Error creating: " . var_export($e, true));
		}
	}
	else {
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>

<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Scores where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Score</label>
	<input type="number" min="1" name="score"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php require(__DIR__ . "/partials/flash.php");?>
