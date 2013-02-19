<?php

require 'database.php';

readfile('header.html');
?>

<h1>Welcome to Task One</h1>

<h3>Search our product database!</h3>
<form class="form-search">
	<input type="text" name="s" class="input-medium search-query">
	<button type="submit" class="btn">Search</button>
</form>

<?php

if (empty($_GET['s'])) {
	$s = '%';
} else {
	$s = '%' . $_GET['s'] . '%';
}

$query = $db->prepare("select * from products where description LIKE :search");
$query->bindValue(':search', $s);
$query->execute();
foreach ($query as $row) {
	echo 'ID: ' . htmlspecialchars($row['id']) . '<br>';
	echo 'Title: ' . htmlspecialchars($row['title']) . '<br>';
	echo 'Description: ' . htmlspecialchars($row['description']) . '<br>';
	echo '<br>';
}

readfile('footer.html');
