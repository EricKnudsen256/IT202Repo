<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
if (isset($_POST["id"])) {

    $cost = (int)$_POST["reward"];
    if ($cost <= 0) {
        $cost = 0;
    }
    $cost++;
    //TODO other validation
    $balance = getBalance();
        $db = getDB();
        $expires = new DateTime();
        $days = (int)$_POST["duration"];
        $expires->add(new DateInterval("P" . $days . "D"));
        $expires = $expires->format("Y-m-d H:i:s");

	$stmt = $db->prepare("SELECT paid_out FROM Competitions WHERE id = :cid");
	$r = $stmt->execute([":cid"=>$_POST["id"]]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);

	if($result["paid_out"] == 1)
	{
	    flash("Competition already paid out, cannot edit");
	}
	else
	{

        $query = "UPDATE Competitions SET name = :name, duration = :duration, expires = :expires, min_score = :min_score, first_place_per = :fp, second_place_per = :sp, third_place_per = :tp, reward = :reward, fee = :fee, cost = :cost WHERE id = :cid";
        $stmt = $db->prepare($query);
        $params = [
            ":name" => $_POST["name"],
            ":duration" => $days,
            ":min_score" => $_POST["min_score"],
	    ":expires" => $expires,
            ":fee" => $_POST["fee"],
            ":reward" => $_POST["reward"],
	    ":cid" => $_POST["id"],
	    ":cost" => $cost
        ];
        switch ((int)$_POST["split"]) {
            /* case 0:
                 break;  using default for this*/
            case 1:
                $params[":fp"] = .8;
                $params[":sp"] = .2;
                $params[":tp"] = 0;
                break;
            case 2:
                $params[":fp"] = .7;
                $params[":sp"] = .3;
                $params[":tp"] = 0;
                break;
            case 3:
                $params[":fp"] = .7;
                $params[":sp"] = .2;
                $params[":tp"] = .1;
                break;
            case 4:
                $params[":fp"] = .6;
                $params[":sp"] = .3;
                $params[":tp"] = .1;
                break;
            default:
                $params[":fp"] = 1;
                $params[":sp"] = 0;
                $params[":tp"] = 0;
                break;
        }
        $r = $stmt->execute($params);
        if ($r) {
            flash("Successfully updated competition", "success");
            die(header("Location: #"));
        }
        else {
            flash("There was a problem creating a competition: " . var_export($stmt->errorInfo(), true), "danger");
        }
    }
}

?>
    <div class="container-fluid">
        <h3>
	    Edit Competition
	</br>
	    Current Points: <?php echo getBalance();?>
	</h3>
        <form method="POST">
	    <div class="form-group">
		<label for="id">Competition ID</label>
		<input id="id" name="id" class="form-comtrol"/>
            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" name="name" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="d">Duration (in days)</label>
                <input id="d" name="duration" type="number" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="s">Minimum Required Score</label>
                <input id="s" name="min_score" type="number" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="r">Reward Split (First, Second, Third)</label>
                <select id="r" name="split" type="number" class="form-control">
                    <option value="0">100%</option>
                    <option value="1">80%/20%</option>
                    <option value="2">70%/30%</option>
                    <option value="3">70%/20%/10%</option>
                    <option value="4">60%/30%/10%</option>
                </select>
            </div>
            <div class="form-group">
                <label for="rw">Reward/Payout</label>
                <input id="rw" name="reward" type="number" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="f">Entry Fee</label>
                <input id="f" name="fee" type="number" class="form-control"/>
            </div>
            <input type="submit" class="btn btn-success" value="Create (Cost: 1)"/>
        </form>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
