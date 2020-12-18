
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
    $oneWeekAgo = date('Y-m-d H:i:s', strtotime("-7 days"));
    $db = getDB();
    $stmt = $db->prepare("SELECT user_id, sum(points_change) as points from PointsHistory WHERE :oneWeekAgo < created group by user_id order by points DESC LIMIT 10 ");
    $r = $stmt->execute([":oneWeekAgo"=>$oneWeekAgo
        ]);

   $resultW = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $oneWeekAgo = date('Y-m-d H:i:s', strtotime("-1 month"));
    $db = getDB();
    $stmt = $db->prepare("SELECT user_id, sum(points_change) as points from PointsHistory WHERE :oneWeekAgo < created group by user_id order by points DESC LIMIT 10 ");
    $r = $stmt->execute([":oneWeekAgo"=>$oneWeekAgo
        ]);

   $resultM = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $db = getDB();
    $stmt = $db->prepare("SELECT user_id, sum(points_change) as points from PointsHistory group by user_id order by points DESC LIMIT 10 ");
    $r = $stmt->execute([
        ]);

   $resultA = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

    <div class="container-fluid">
    <h3>Weekly Points</h3>
    <div class="row">
    <div class="card-group">
<?php if($resultW && count($resultW) > 0):?>
    <?php foreach($resultW as $r):?>
        <div class="col-auto mb-3">
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <div class="card-text">
                        <div>User: <?php safer_echo($r["user_id"]);?>
			</div>
			<div>Points:  <?php safer_echo($r["points"]);?>
			</div>
			</br>
                </div>
            </div>
        </div>
    <?php endforeach;?>
<?php endif;?>
    </div>
    </div>

    <div class="container-fluid">
    <h3>Monthly Points</h3>
    <div class="row">
    <div class="card-group">
<?php if($resultM && count($resultM) > 0):?>
    <?php foreach($resultM as $r):?>
        <div class="col-auto mb-3">
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <div class="card-text">
                        <div>User: <?php safer_echo($r["user_id"]);?>
                        </div>
                        <div>Points:  <?php safer_echo($r["points"]);?>
                        </div>
                        </br>
                </div>
            </div>
        </div>
    <?php endforeach;?>
<?php endif;?>
    </div>
    </div>



    <div class="container-fluid">
    <h3>All Time Points</h3>
    <div class="row">
    <div class="card-group">
<?php if($resultA && count($resultA) > 0):?>
    <?php foreach($resultA as $r):?>
        <div class="col-auto mb-3">
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <div class="card-text">
                        <div>User: <?php safer_echo($r["user_id"]);?>
                        </div>
                        <div>Points:  <?php safer_echo($r["points"]);?>
                        </div>
                        </br>
                </div>
            </div>
        </div>
    <?php endforeach;?>
<?php endif;?>
    </div>
    </div>

<?php require(__DIR__ . "/partials/flash.php");?>
