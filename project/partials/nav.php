<link rel="stylesheet" href="static/css/styles.css">
<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<ul>
    <li><a href="home.php">Home</a></li>
    <?php if (!is_logged_in()): ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
	<li><a href="games/pong.php">Pong</a></li>
    <?php endif; ?>
    <?php if (has_role("Admin")): ?>
        <li><a href="test_create_scores.php">Create Scores</a></li>
        <li><a href="test_list_scores.php">List Scores</a></li>
	<li><a href="test_create_pointshistory.php">Create Point Change</a></li>
        <li><a href="test_list_pointshistory.php">List Point Change</a></li>
	<li><a href="edit_competition.php">Edit Competition</a></li>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
	<li><a href="games/pong.php">Pong</a></li>
	<li><a href="create_competition.php">Create Competition</a></li>
	<li><a href="competitions.php">Competitions</a></li>
	<li><a href="pointshistory.php">Point History</a></li>
    <?php endif; ?>
</ul>
