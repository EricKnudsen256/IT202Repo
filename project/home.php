
<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
<p>Welcome, <?php echo $email; ?><p>

<?php

    $currentTime = date('Y-m-d H:i:s');
    $oneWeekAgo = strtotime("-1 month");
    $db = getDB();
    $stmt = $db->prepare("SELECT user_id, sum(points_change) as points from PointsHistory group by user_id");
    $r = $stmt->execute([
        ]);

   $resultA = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

    <div class="container-fluid">
    <h3>Weekly Points</h3>
    <div class="row">
    <div class="card-group">
<?php if($resultA && count($resultA) > 0):?>
    <?php foreach($resultA as $r):?>
        <div class="col-auto mb-3">
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <div class="card-title">
                        <?php safer_echo("Weekly Scoreboard");?>
                    </div>
                    <div class="card-text">
                        <div>User: <?php safer_echo($r["user_id"]);?>
			</div>
			<div>Points:  <?php safer_echo($r["points"]);?>
			</div>
                </div>
            </div>
        </div>
    <?php endforeach;?>
<?php endif;?>
    </div>
    </div>



<?php require(__DIR__ . "/partials/flash.php");?>
