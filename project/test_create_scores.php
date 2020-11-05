<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
	<label>Score</label>
	<input type="number" min="1" name="score"/>

	<input type="number" min="1" name="mod_max"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$user_id = get_user_id();;
	$score = $_POST["score"];
	$time = date('Y-m-d H:i:s');//calc
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Scores (user_id, score, created) VALUES(:user_id, :score, :time)");
	$r = $stmt->execute([
		":user_id"=>$user_id,
		":score"=>$score,
		":time"=>$time,
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");?>
