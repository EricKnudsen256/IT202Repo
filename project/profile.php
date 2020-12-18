<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    die(header("Location: login.php"));
}
UpdateCompetitions();

if(isset($_POST["public"]) && isset($_POST["saved"]))
{
   $db = getDB();
   $stmt = $db->prepare("UPDATE Users SET public = 1 WHERE id = :uid");
   $r = $stmt->execute([":uid"=>get_user_id()]);
}
else if(isset($_POST["saved"]))
{
   $db = getDB();                                                                                                          
   $stmt = $db->prepare("UPDATE Users SET public = 0 WHERE id = :uid");                                                    
   $r = $stmt->execute([":uid"=>get_user_id()]);
}


$currentID = get_user_id();
$currentUsername = get_username();
$currentEmail = get_email();

 flash("ID: $currentID");
 flash("Username: $currentUsername");
 flash("Email: $currentEmail");


$db = getDB();
//save data if we submitted the form
if (isset($_POST["saved"])) {
    $isValid = true;
    //check if our email changed
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            echo "Email is already in use";
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            echo "Username is already in use";
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    }
    if ($isValid) {
        $stmt = $db->prepare("UPDATE Users set email = :email, username= :username where id = :id");
        $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":id" => get_user_id()]);
        if ($r) {
            echo "Updated profile";
        }
        else {
            echo "Error updating profile";
        }
        //password is optional, so check if it's even set
        //if so, then check if it's a valid reset request
        if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
            if ($_POST["password"] == $_POST["confirm"]) {
                $password = $_POST["password"];
                $hash = password_hash($password, PASSWORD_BCRYPT);
                //this one we'll do separate
                $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
                if ($r) {
                    echo "Reset password";
                }
                else {
                    echo "Error resetting password";
                }
            }
        }
//fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
        }
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }

}

?>




<form method="POST">
    <label for="email">Email</label>
    <input type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
    <label for="username">Username</label>
    <input type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
    <!-- DO NOT PRELOAD PASSWORD-->
    <label for="pw">Password</label>
    <input type="password" name="password"/>
    <label for="cpw">Confirm Password</label>
    <input type="password" name="confirm"/>
    <input type="submit" name="saved" value="Save Profile"/>
    <label for="public">Public Profile?</label>
    <input type="checkbox" name="public"/>
</form>
<?php require(__DIR__ . "/partials/flash.php");?>

<?php
   	$user_id = get_user_id();
	$db = getDB();
	
	$stmt = $db->prepare("SELECT * FROM Scores WHERE :id=user_id");
	$r = $stmt->execute([
	":id"=>$user_id
	]);
	
	if ($r)
	{
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	if($results == []) 
	{
	    InitScoreEntry();
	    $stmt = $db->prepare("SELECT * FROM Scores WHERE :id=user_id");
            $r = $stmt->execute([
            ":id"=>$user_id
	    ]);
	    $results = $stmt->fetchAll(PDO::FETCH_ASSOC); 
	}
	foreach($results as $res)
	{
	    $balance = $res["score"];
	    $_SESSION["balance"] = $balance;	
	}
    if (!$r) {
        flash("There was a problem fetching the results");
		}
?>

<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Current Point Balance:</div>
                        <div><?php safer_echo($r["score"]); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>


<?php
$page = 1;
$per_page = 10;
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
	if($page <= 0)
	{
	    $page = 1;
	}
    }
    catch(Exception $e){

    }
}
$db = getDB();
$stmt = $db->prepare("SELECT count(*) as total from UserCompetitions uc LEFT JOIN Competitions c on c.id = uc.competition_id where uc.user_id = :id");
$stmt->execute([":id"=>get_user_id()]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;
$stmt = $db->prepare("SELECT c.* from UserCompetitions uc LEFT JOIN Competitions c on c.id = uc.competition_id where uc.user_id = :id LIMIT :offset, :count");
//need to use bindValue to tell PDO to create these as ints
//otherwise it fails when being converted to strings (the default behavior)
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", get_user_id());
$stmt->execute();
$e = $stmt->errorInfo();
if($e[0] != "00000"){
    flash(var_export($e, true), "alert");
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="container-fluid">
    <h3>My Competitions</h3>
    <div class="row">
    <div class="card-group">
<?php if($results && count($results) > 0):?>
    <?php foreach($results as $r):?>
        <div class="col-auto mb-3">
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <div class="card-title">
                        <?php safer_echo($r["name"]);?>
                    </div>
                    <div class="card-text">
			<div>Created: <?php safer_echo($r["created"]); ?></div>
			<div>Expires: <?php safer_echo($r["expires"]); ?></div>
			<div>Participants: <?php safer_echo($r["participants"]); ?></div>
			<div>Reward: <?php safer_echo($r["reward"]); ?></div>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach;?>

<?php else:?>
<div class="col-auto">
    <div class="card">
       You have not been in any competitions.
    </div>
</div>
<?php endif;?>
    </div>
    </div>
        <nav aria-label="My Competitions">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
