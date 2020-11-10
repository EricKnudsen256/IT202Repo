
<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
    <h3>Create Point Change</h3>
    <form method="POST">
        <label>Point change</label>
        <input type="number" name="points_change"/>
        <label>Reason</label>
        <input name="reason" placeholder="Reason"/>
        <input type="submit" name="save" value="Create"/>
    </form>

<?php
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $user_id = get_user_id();
    $points_change = (is_numeric($_POST['points_change']) ? (int)$_POST['points_change'] : 0);
    $reason = $_POST["reason"];
    $user = get_user_id();
	$current = date('Y-m-d H:i:s');//calc
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO PointsHistory (user_id, points_change, reason, created) VALUES(:user_id, :points_change, :reason,:created)");
    $r = $stmt->execute([
        ":user_id" => $user_id,
        ":points_change" => $points_change,
        ":reason" => $reason,
        ":created" => $current,
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php");
