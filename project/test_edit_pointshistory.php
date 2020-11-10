
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
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $points_change = (is_numeric($_POST['points_change']) ? (int)$_POST['points_change'] : 0);
    $reason = $_POST["reason"];
    $user_id = get_user_id();
	$current = date('Y-m-d H:i:s');//calc
    $db = getDB();
    if (isset($id)) {
        $stmt = $db->prepare("UPDATE PointsHistory set user_id=:user_id, points_change=:points_change, reason=:reason, created=:created where id=:id");
        $r = $stmt->execute([
			":id" => $id,
			":user_id" => $user_id,
			":points_change" => $points_change,
			":reason" => $reason,
			":created" => $current,
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>

    <h3>Edit Points Change</h3>
    <form method="POST">
        <label>Point change</label>
        <input type="number" name="points_change"/>
        <label>Reason</label>
        <input name="reason" placeholder="reason"/>
        <input type="submit" name="save" value="Create"/>
    </form>


<?php require(__DIR__ . "/partials/flash.php");
