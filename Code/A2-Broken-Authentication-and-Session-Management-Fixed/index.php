<?php

require 'config.php';

readfile('header.html');

$errors = array();

// Is the user logging in?
if (!empty($_POST)) {
	if (empty($_POST['username'])) {
		$errors['username'] = "Please enter a username";
	}
	if (empty($_POST['password'])) {
		$errors['password'] = "Please enter a password";
	}
	if (empty($errors)) {
		// try log in
		$query = $db->prepare('select * from users where username = :username');
		$query->bindValue(':username', $_POST['username']);
		$query->execute();
		$res = $query->fetch(PDO::FETCH_ASSOC);
		if ($res) {
			if (crypt($_POST['password'], $res['password']) === $res['password']) {
				$_SESSION['username'] = $res['username'];
				session_regenerate_id(); // Regenerate ID's on successful login
			} else {
				$errors['password'] = 'Invalid password';
			}
		} else {
			$errors['username'] = 'Username not found';
		}
	}
}

if (!empty($_SESSION['username'])) {
	echo '<h1>Welcome, ' . htmlspecialchars($_SESSION['username']) . '</h1>';
} else {
	?>
<h1>Log in</h1>
<form class="form-horizontal" method="post">
	<div class="control-group <?php if (!empty($errors['username'])) { ?>error<?php } ?>">
		<label class="control-label" for="inputUsername">Username</label>
		<div class="controls">
			<input type="text" id="inputUsername" name="username" placeholder="Username">
		</div>
	</div>
	<div class="control-group <?php if (!empty($errors['password'])) { ?>error<?php } ?>">
		<label class="control-label" for="inputPassword">Password</label>
		<div class="controls">
			<input type="password" id="inputPassword" name="password" placeholder="Password">
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn">Sign in</button>
		</div>
	</div>
</form>

<?php

}

readfile('footer.html');
