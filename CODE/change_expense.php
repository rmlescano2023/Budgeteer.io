<?php
    session_start();
	if(isset($_SESSION['loggedUserId'])) {
        require_once 'database.php';

    }else {

		header ("Location: landing.php");
		exit();
    }
?>

<!DOCTYPE html>
<html>
<head>

	<meta charset="utf-8">
	<title>MyBudget - Your Personal Finance Manager</title>
	<meta name="description" content="Track your income and expenses - avoid overspending!">
	<meta name="keywords" content="expense manager, budget planner, expense tracker, budgeting app, money manager, money management, personal finance management software, finance manager, saving planner">
	<meta name="author" content="Magdalena Słomiany">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<meta http-equiv="X-Ua-Compatible" content="IE=edge">

	<!--
		<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/fontello.css">
	-->

	<link rel="stylesheet" href="homestyle.css">
	<link href="https://fonts.googleapis.com/css2?family=Baloo+Paaji+2:wght@400;500;700&family=Fredoka+One&family=Roboto:wght@400;700;900&family=Varela+Round&display=swap" rel="stylesheet">

</head>
<body>

	<div class="navbar">
			<div class="profile">
				<img src="images/profile picture.png" alt="Profile Picture">
				<p>John Doe</p>
			</div>
			<ul>
				<li><a href="#">Profile</a></li>
				<li><a href="home.php">Home</a></li>
				<li><a href="budget.php">Budget</a></li>
				<li><a href="expense.php">Expense</a></li>
				<li>
					<!--<a href="balance.php">Statistics</a>-->
					<?php
					$userStartDate = date('Y-m-01');
					$userEndDate = date('Y-m-t');

					echo '<a class="dropdown-item" href="balance.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Statistics</a>';
					?>
				</li>
				<li><a href="#">Notes</a></li>
				<li><a href="#">Calendar</a></li>
				<li><a href="settings.php" class="active">Settings</a></li>
			</ul>
			<div class="logo">
				<img src="images/Logo3.png" alt="Logo">
			</div>
		</div>

		<!-- creates a connection -->
	<?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "my_budget";

        $conn = new mysqli($servername, $username, $password, $dbname);

		// Check connection
		if (!$conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}

		// Get user's current information

		$user_id = $_SESSION['loggedUserId']; // Assume user is logged in
		$expense_id = $_GET['expense_id']; // gets  the expense_id of a specific expense

		// sql queries to get expense_id, expense_amount, and expense_comment from expenses table
		$sql = "SELECT expense_id, expense_amount, expense_comment FROM expenses WHERE expense_id = '$expense_id'";

		$result = mysqli_query($conn, $sql);

		// fetches the current amount and comment for expense
		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$current_amount = $row['expense_amount'];
			$current_comment = $row['expense_comment'];
		} else { // user is not found
			echo "Error: User not found";
		}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input
    $new_expense = $_POST['expense_amount'];
    $new_comment = $_POST['expense_comment'];

    // Validate input (not shown)

    // Update database using prepared statements
    $sql = "UPDATE expenses SET expense_amount = ?, expense_comment = ? WHERE expense_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_expense, $new_comment, $expense_id);

    if ($stmt->execute()) {
        header("Location: summary.php");
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

}
	?>

	<!-- forms to change expense amount and expense comment -->
	<form method="post">
		<label for="name">Expense Amount:</label>
		<input type="text" name="expense_amount" value = "<?php echo $current_amount; ?>" required><br>

		<label for="password">Comments:</label>
		<input type="text" name="expense_comment" value = "<?php echo $current_comment; ?>"><br>

		<input type="submit" value="Save Changes">
	</form>
</body>
</html>
