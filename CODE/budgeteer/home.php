<?php
	session_start();

  $startDate = 00-00-00;
	$endDate = 00-00-00;
 
	if(!isset($_SESSION['loggedUserId'])) {

    require_once 'database.php';

		if(isset($_POST['username'])) {
		
			$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
      $username = filter_input(INPUT_POST, 'username');
			$password = filter_input(INPUT_POST, 'password');
		
			$userQuery = $db -> prepare(
			"SELECT user_id, password, username
			FROM users
			WHERE username = :username");
			$userQuery->execute([':username'=> $username]);
			
			$user = $userQuery -> fetch();

			if($user && password_verify($password, $user['password'])) {
			//if($user && $password) {
				
				$_SESSION['loggedUserId'] = $user['user_id'];
				$_SESSION['username'] = $user['username'];
				unset($_SESSION['badAttempt']);
				
			} else {
				
				$_SESSION['badAttempt'] = "";
				header ('Location: login.php');
				exit();
			}
    }else {

      header ("Location: login.php");
      exit();
    }
	} 
  
?>

<?php
 
   if(isset($_SESSION['loggedUserId'])) {
     
     require_once 'database.php';

     $startDate = date('Y-m-d', strtotime('today'));
     $endDate = date('Y-m-d', strtotime('tomorrow - 1 second'));
     
     if(isset($_GET['userStartDate'])) {
       
       if($_GET['userStartDate'] > $_GET['userEndDate']) {
         
         $startDate = $_GET['userEndDate'];
         $endDate = $_GET['userStartDate'];
       } else {
         
         $startDate = $_GET['userStartDate'];
         $endDate = $_GET['userEndDate'];
       }
       
      }

       $expensesQuery = $db -> prepare(
       "SELECT e.category_id, ec.expense_category, SUM(e.expense_amount) AS expense_amount
       FROM expenses e NATURAL JOIN expense_categories ec
       WHERE e.user_id=:loggedUserId AND e.expense_date BETWEEN :startDate AND :endDate
       GROUP BY e.category_id
       ORDER BY expense_amount DESC");
       $expensesQuery -> execute([':loggedUserId'=> $_SESSION['loggedUserId'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
       
       $expensesOfLoggedUser = $expensesQuery -> fetchAll();
       
       $incomesQuery = $db -> prepare(
       "SELECT i.category_id, ic.income_category, SUM(i.income_amount) AS income_amount
       FROM incomes i NATURAL JOIN income_categories ic
       WHERE i.user_id=:loggedUserId AND i.income_date BETWEEN :startDate AND :endDate
       GROUP BY i.category_id
       ORDER BY income_amount DESC");
       $incomesQuery -> execute([':loggedUserId'=> $_SESSION['loggedUserId'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
       
       $incomesOfLoggedUser = $incomesQuery -> fetchAll();
       
       echo "<script>
           var incomes = ".json_encode($incomesOfLoggedUser).";
           var expenses = ".json_encode($expensesOfLoggedUser)."
         </script>";
   } 
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home-Budgeteer</title>
	  <link rel="stylesheet" type="text/css" href="homestyle.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<?php

?>


<body onload="drawChart(incomes, expenses)" onresize="drawChart(incomes, expenses)">
  
	<div class="navbar">
		<div class="profile">

			<img src="images/profile pic.png" alt="Profile Picture">

			<p>John Doe</p>
		</div>
		<ul>
			<li>
        
        <?php
          $userStartDate = date('Y-m-d');
		      $userEndDate = date('Y-m-d');
                  
          echo '<a href="home.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'" class="active">Home</a>';
        ?>
      </li>
			<li><a href="budget.php">Budget</a></li>
      <li><a href="expense.php">Expense</a></li>
			<li>
        
        <?php
          $userStartDate = date('Y-m-01');
          $userEndDate = date('Y-m-t');
                  
          echo '<a href="summary.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'&period=month">Statistics</a>';
        ?>
      </li>
      <li><a href="#">Notes</a></li>
      <li><a href="calendar.php">Calendar</a></li>
      <li><a href="settings.php">Settings</a></li>
      <li><a href="logout.php">Log Out</a></li>
		</ul>
        <div class="logo">
            <img src="images/Logo3.png" alt="Logo">
          </div>
	</div>
  
  <!-- start arrow buttons -->
    <div class="container">
        <div class="calendar-slider">
          <div class="slider-nav">
            
          <?php
            $date = isset($_GET['userStartDate']) ? $_GET['userStartDate'] : date('Y-m-d');
            $prevDate = date('Y-m-d', strtotime('-1 day', strtotime($date)));
            $nextDate = date('Y-m-d', strtotime('1 day', strtotime($date)));
            $currentDate = date('Y-m-d');
            $isCurrentDate = $date === $currentDate;
            echo '<a class="fas fa-chevron-left" onclick="changeDate(\''.$prevDate.'\')"></a>';

            // Display the date
            $formattedDate = date('F j, Y', strtotime($date));
            echo '<span class="date" id="result">'.$formattedDate.'</span>';

            // Display the right button if it's not the current date
            if (!$isCurrentDate) {
              echo '<a class="fas fa-chevron-right" onclick="changeDate(\''.$nextDate.'\')"></a>';
            }
            
            else {
              echo '<a class="fas fa-chevron-right inactive"></a>';
            }
          ?>
          <style>
            .inactive {
              opacity: 0.5;
              pointer-events: none;
            }
          </style>

          <script>
            function changeDate(date) {
              var newDate = new Date(date);
              newDate.setDate(newDate.getDate());
              var newDateString = newDate.toISOString().split('T')[0];
              var url = 'home.php?userStartDate=' + newDateString + '&userEndDate=' + newDateString;
              window.location.href = url;
            }
          </script>


          </div>

    <!-- end arrow buttons --> 
       
              
        </div>
        <div class="box big-box">
          <div class="box-label">Overview</div>
          <!-- Content for big box here -->

          
          <!-- Statistics -->
            <?php	
            $totalIncomes = 0;
            $totalExpenses = 0;
                    
            foreach ($incomesOfLoggedUser as $incomes) {
              $totalIncomes += $incomes['income_amount'];
              
            }
            foreach ($expensesOfLoggedUser as $expenses) {
              $totalExpenses += $expenses['expense_amount'];
            }
              $balance = $totalIncomes - $totalExpenses;
              //echo '<center><div id="balance">BALANCE:&emsp;'.$balance.'</div></center>';
            ?>
            
      
          
          <?php
            if(!empty($incomesOfLoggedUser)) {
              
              echo '<div id="piechart1" class="income-chart"></div>';
            }
          
            if(!empty($expensesOfLoggedUser)) {
              
              echo '<div id="piechart2" class="expense-chart"></div>';
            }
          ?>

          <!-- End of Statistics -->


        </div>
        <div class="box small-box">
          <div class="box-label">Transactions</div>
          
          <div class="histBtns">
          <button id="expenseBtn">Expenses</button>
          <button id="incomeBtn">Incomes</button>
          </div>


          <div id="incomeList" style="display: none; margin-left: 10px;" >
            <!-- Incomes -->
            <center><caption>Incomes</caption></center>	
            <tr>
                  
            </tr>
            
            <?php
								$totalIncomes = 0;
								$incomeCount = 0;
								foreach ($incomesOfLoggedUser as $incomes) {
									
									echo "<tr class=\"summary\">
                  
                  <!-- <td class=\"category\">{$incomes['income_category']}</td><td class=\"sum\">{$incomes['income_amount']} ₱</td> 
									
									<td class=\"category\">Category: {$incomes['income_category']}</td>
                  -->
									</tr>";
									
									
									$totalIncomes += $incomes['income_amount'];
									
									$incomesTableRowsQuery = $db -> prepare(
									"SELECT income_date, income_amount, income_comment
									FROM incomes
									WHERE category_id=:incomeCategoryId AND user_id=:loggedUserId AND income_date BETWEEN :startDate AND :endDate
									ORDER BY income_date ASC");
									$incomesTableRowsQuery -> execute([':loggedUserId' => $_SESSION['loggedUserId'], ':incomeCategoryId' => $incomes['category_id'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
									
									$incomesOfSpecificCategory = $incomesTableRowsQuery -> fetchAll();
									
									foreach ($incomesOfSpecificCategory as $categoryIncome) {
										
										echo "<div class='inc'>
                    <tr>
                    
                    <div class='incAmount'>₱ 
                      <td class=\"amount\">{$categoryIncome['income_amount']}  </td>
                      </div>
                    <div class='incDate'>
                      <td class=\"date\">{$incomes['income_category']}</td> 
                      </div>
                    <div class='incComment'>
                      <td class=\"comment\">{$categoryIncome['income_comment']}<br></td>
                      </div>
										</tr></div>";
                    
                    $incomeCount++;
									}
								}

                    if ($incomeCount<=0) {
                      echo "<div class='expAmount'>No Incomes Recorded</div>";
                  } 
								
								//echo "<tr class=\"summary\"><td class=\"total\">TOTAL</td><td class=\"sum\">{$totalIncomes} ₱</td>
						
								
								//</tr>";
							?>
            </div>

          <div id="expenseList">

            <!-- Expenses -->
            <center><caption>Expenses</caption></center>	
            
            <tr>
            

              <?php
								$totalExpenses = 0;
								$expenseCount = 0;
								foreach ($expensesOfLoggedUser as $expenses) {
									
									echo "<tr class=\"summary\">
                  <!--<td class=\"category\">{$expenses['expense_category']}</td><td class=\"sum\"> {$expenses['expense_amount']} ₱</td>
									
                  <td class=\"category\">Category: {$expenses['expense_category']}</td>
									-->
									</tr>";
                  
									
									$totalExpenses += $expenses['expense_amount'];
									
									$expensesTableRowsQuery = $db -> prepare(
									"SELECT e.expense_date, e.expense_amount, pm.payment_method, e.expense_comment
									FROM expenses e NATURAL JOIN payment_methods pm
									WHERE e.category_id=:expenseCategoryId AND e.user_id=:loggedUserId AND e.expense_date BETWEEN :startDate AND :endDate
									ORDER BY e.expense_date ASC");
									$expensesTableRowsQuery -> execute([':loggedUserId' => $_SESSION['loggedUserId'], ':expenseCategoryId' => $expenses['category_id'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
									
									$expensesOfSpecificCategory = $expensesTableRowsQuery -> fetchAll();
                  
                  
                    foreach ($expensesOfSpecificCategory as $categoryExpense) {
                      
                      echo "<div class='exp'>
                            <tr>
                              
                              <div class='expAmount'> ₱ 
                                <td class=\"amount\" style='color: red;'>{$categoryExpense['expense_amount']}  </td> 
                                </div>
                              <div class='expPayment'>
                                <td class=\"payment\">{$categoryExpense['payment_method']}</td> 
                                </div> 
                              <div class='expDate'>
                                <td class=\"date\">{$expenses['expense_category']}</td>
                                </div> 
                              <div class='expComment'> 
                                <td class=\"comment\">{$categoryExpense['expense_comment']}<br></td>	
                                </div>  
                      </tr> </div>";
                      $expenseCount ++;
                    }
                    
								}

                //echo $expenseCount;
                    if ($expenseCount<=0) {
                      echo "<div class='expAmount'>No Expenses Recorded</div>";
                  } 
								
								//echo "<tr class=\"summary\"><td class=\"total\">TOTAL</td><td class=\"sum\">{$totalExpenses} ₱</td></tr>";
							?>
          </div>

          <script>
            // Get references to the buttons and lists
            const incomeBtn = document.getElementById('incomeBtn');
            const expenseBtn = document.getElementById('expenseBtn');
            const incomeList = document.getElementById('incomeList');
            const expenseList = document.getElementById('expenseList');

            // Show the expense list by default
            expenseList.style.display = 'block';

            // Event listeners for button clicks
            incomeBtn.addEventListener('click', function() {
              // Show income list and hide expense list
              incomeList.style.display = 'block';
              expenseList.style.display = 'none';
            });

            expenseBtn.addEventListener('click', function() {
              // Show expense list and hide income list
              expenseList.style.display = 'block';
              incomeList.style.display = 'none';
            });
          </script>
          
        </div>

      </div>

      <script src="js/budget.js"></script>
      <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/jquery-3.4.1.min.js"></script>
</body>

</html>

<!--

<script>
                /*function dateToWords() {
                  const options = { month: 'long', day: 'numeric', year: 'numeric' };
                  const dateObj = new Date();
                  const dateString = dateObj.toLocaleDateString('en-US', options);
                  return dateString;
                }

                  const dateElement = document.getElementById('result');
                  const currentDate = dateToWords();
                  dateElement.innerHTML = currentDate;*/
                </script>


<div style="display: inline-block; text-align: center;">
              <?php
                $today = date('Y-m-d');
                $startOfWeek = date('Y-m-d', strtotime('last Sunday', strtotime($today)));
                $endOfWeek = date('Y-m-d', strtotime('next Saturday', strtotime($today)));
                
                echo '<a class="button" href="home.php?userStartDate='.$startOfWeek.'&userEndDate='.$endOfWeek.'">Current Week</a>';
                //echo($startOfWeek);
                //echo($endOfWeek);
							?>
               

              <?php
								$userStartDate = date('Y-m-01');
								$userEndDate = date('Y-m-t');
								
								echo '<a class="button" href="home.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Current Month</a>';
                
							?>

              <?php
								$userStartDate = date('Y-01-01');
								$userEndDate = date('Y-12-31');
								
								echo '<a class="button" href="home.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Current Year</a>';
							?>

              <?php
              $dailyDate = date('Y-m-d');
              echo '<a class="button" href="home.php?userStartDate='.$dailyDate.'&userEndDate='.$dailyDate.'">Daily</a>';
              ?>
          </div>
              -->