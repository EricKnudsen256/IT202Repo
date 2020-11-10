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
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Scores.id,user_id,score,Scores.created, Users.username FROM Scores JOIN Users on Scores.user_id = Users.id where Scores.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
	    Score ID: 
            <?php safer_echo($result["id"]); ?>
        </div>
        <div class="card-body">
            <div>
                <p>Stats</p>
                <div>ID: <?php safer_echo($result["id"]); ?></div>
                <div>User_ID: <?php safer_echo($result["user_id"]); ?></div>
                <div>Score: <?php safer_echo($result["score"]); ?></div>
                
                <div>User: <?php safer_echo($result["username"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
