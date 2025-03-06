<?php

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

// Set up database connection parameters
// Replace the hardcoded credentials with:
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'default_user';
$password = getenv('DB_PASSWORD') ?: 'default_password';
$database = getenv('DB_NAME') ?: 'default_db';

$full_domain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

if (isset($_POST['submit'])) {
  //=============================================================================================================
  
  
    // Validate the input
    if(!isset($_POST["token_name"]) || !isset($_POST["current_price"]) || !is_numeric($_POST["current_price"]) || 
       !isset($_POST["ath"]) || !is_numeric($_POST["ath"])) {
        echo "Invalid input. Please provide valid numbers for current price and ATH";
        return;
    }
    $current_price = (float)$_POST["current_price"];
    @$original_current_price = (float)$_POST["original_current_price"];
    $ath = (float)$_POST["ath"];
    $total_coin = (float)$_POST["total_coin"];    
	$token_name = htmlentities($_POST["token_name"]);
        $token_name = strtoupper(trim($token_name));
	$total_coin=htmlentities($total_coin);
	$unique_id = uniqid();
	
	if(empty($total_coin) || isset($_POST['use_sample_value'])) {
		$total_coin=50/$current_price;
		//echo "data";		
	}
	
	if(empty($original_current_price)) {
		$original_current_price=$current_price;
		//echo "data";		
	}

	

    // Check if the ATH is greater than the current price
    if($ath <= $current_price) {
        echo "Invalid input. ATH must be greater than the current price.";
        return;
    }

    //Calculate total investment 
    $total_investment = $total_coin * $current_price;
    $coin_to_sell = $total_coin/($ath/$current_price);
	$current_coint_count=0;
    $total_amount = 0;
	$alert = false;
	$counter = 0;
	$min_amount=2;
	$profit=0;
	$xfromath=$ath/$current_price;
	@$original_current_price=htmlentities($original_current_price);
	$double_current_price=$current_price+$original_current_price;
	
	// Create a new MySQLi object and connect to the database
	$conn = new mysqli($host, $username, $password, $database);

	// Check for errors
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// Prepare the query
	$sql = "CREATE TABLE IF NOT EXISTS hodl (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				current_price FLOAT,
				original_current_price FLOAT,
				ath FLOAT,
				total_coin FLOAT,
				token_name VARCHAR(255),
				unique_id VARCHAR(255)
			)";

	if ($stmt = $conn->prepare($sql)) {
		// Execute the query to create the table (if it doesn't already exist)
		$stmt->execute();

		// Prepare the query to insert the data into the table
		$sql = "INSERT INTO hodl (current_price, original_current_price, ath, total_coin, token_name, unique_id)
				VALUES (?, ?, ?, ?, ?, ?)";

		if ($stmt = $conn->prepare($sql)) {
			// Bind the variables to the prepared statement
			$stmt->bind_param("ddddss", $current_price, $original_current_price, $ath, $total_coin, $token_name, $unique_id);

			// Execute the statement to insert the data
			$stmt->execute();

			// Close the statement
			$stmt->close();
		}
	}

	// Close the database connection
	$conn->close();
	
	echo "
	<!DOCTYPE html>
	<html>
	  <head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
		<title>$token_name</title>
		<meta name='description' content='$token_name @ $current_price USDT. Hit & Run Strategy. HODL with confidence, reap the rewards with ease. Created with &hearts; by Exrienz @2023'>
		<link rel='stylesheet' href='style.css'>
		<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css'>
	  </head>
	  <body>
		<div class='container'>
		  <div class='card'>
		<div class='card-header text-center'>
			<a href='index.php'><img src='hodl.png' alt='HODL Calculator' style='max-width: 100%;'></a>
		</div>
	";
	
	echo "
	<div class='card-body' style='font-size: 14px;'>
          <h4 class='card-title'>$token_name HODL Settings:</h4>
          <ul class='list-group'>
            <li class='list-group-item'><b>Token Name:</b> $token_name</li>
            <li class='list-group-item'><b>Current Token Price:</b> $current_price USD (x".round($xfromath,0)." from ATH)</li>
            <li class='list-group-item'><b>Token Price All Time High (ATH):</b> $ath USD</li>
            <li class='list-group-item'><b>Total Token In Possession:</b> ".round($total_coin,5)."</li>
            <li class='list-group-item'><b>Initial Investment:</b> ".round($total_investment,2)." USD</li>
			<li class='list-group-item'><b>Share This Strategy?:</b> <a href='".$full_domain."/index.php?hodl_id=$unique_id' target='_blank'>".$full_domain."/index.php?hodl_id=$unique_id</a></li>
<li class='list-group-item'><b>Convert to Text?:</b> <a href='".$full_domain."/convert2text.php?hodl_id=$unique_id' target='_blank'>".$full_domain."/convert2text.php?hodl_id=$unique_id</a></li>

          </ul>
        </div>
        <div class='card-footer text-center'>
          <form method='post' action=''>
            <input type='hidden' value='".$token_name."' name='token_name'>
            <input type='hidden' value='".$double_current_price."' name='current_price' required>
            <input type='hidden' value='".$ath."' name='ath' required>
            <input type='hidden' value='".$total_coin."' id='total_coin' name='total_coin'>
            <input type='hidden' value='".$original_current_price."' name='original_current_price'>
            <button class='btn btn-primary btn-block' width='100%' name='submit'>Too many Sell-Limit Orders? Click here to double the Entry Point</button>
          </form>
        </div>
      </div>
      <br>
	";	
	
	echo '
	<div class="card" style="font-size: 12px;">
		<div class="card-header text-center">
			<h5 class="card-title">Sell-Limit Orders</h5>
		</div>
		<div class="card-body">
	';

    for($x = 2; $x < ($ath/$current_price); $x++) {
        $price = $current_price*$x;
        $amount = $coin_to_sell*$price;
        $total_amount += $amount;
		$current_coint_count += $coin_to_sell;
		$coin_left=$total_coin-$current_coint_count;
		$coin_left_value=$coin_left*$price;
		$counter++;
		$profit=$profit+$amount;
		$sell_all=$profit+$coin_left_value;
		        
		if ($total_amount > $total_investment && !$alert) {
			$balance=$total_amount-$total_investment;		
			echo "
			  <p><b>TP $counter :::: Sell ".round($coin_to_sell,5)." of coin at ".round($price,4)." USD (x$x)::::</b></p>
			  <p>You will get <b style='color:green'>".round($amount,2)." USD</b>. Your accumulated profit now is <b style='color:green'>".round($profit,2)." USD</b></p>
			  <p style='font-size:12px' class='text-danger'>Breakeven! Your initial investment, <b style='color:green'>".round($total_investment,2)." USD</b> claimed with extra profit <b style='color:green'>".round($balance,5)." USD</b>! Leave moonbag and invest on other coin!</p>
			  <p>Token balance in posession now is ".round($coin_left,5)." $token_name (<font style='color:green'>".round($coin_left_value,2)." USD</font>)</p>
			  <p>If you sell everything now, your total profit is <font style='color:green'>".round($sell_all,2)." USD</font></p>";
            $alert = true;
        } else{
			echo "<p><b>TP $counter :::: Sell ".round($coin_to_sell,5)." of coin at ".round($price,4)." USD (x$x)::::</b></p>
			  <p>You will get <b style='color:green'>".round($amount,2)." USD</b>. Your accumulated profit now is <b style='color:green'>".round($profit,2)." USD</b></p>
			  <p>Token balance in posession now is ".round($coin_left,5)." $token_name (<font style='color:green'>".round($coin_left_value,2)." USD</font>)</p>
			  <p>If you sell everything now, your total profit is <font style='color:green'>".round($sell_all,2)." USD</font></p>";
		}
		echo "<hr/>";
    }
    
	if($current_coint_count <= $total_coin){
		$bal_coin=$total_coin-$current_coint_count;
		$last_coint_amnt=$bal_coin*$ath;
		$total_amount=$total_amount+$last_coint_amnt;
		$xs=$total_amount/$total_investment;
		$coin_left=0;
		$coin_left_value=$coin_left*$ath;
		$counter++;
		$profit=$profit+$last_coint_amnt;
		echo "<p><b>TP $counter :::: Sell ".round($bal_coin,5)." of coin at ".round($ath,4)." USD (x$x)::::</b></p>
			  <p>You will get <b style='color:green'>".round($last_coint_amnt,2)." USD</b>. Your accumulated profit now is <b style='color:green'>".round($profit,2)." USD</b></p>
			  <p>Token balance in posession now is ".round($coin_left,5)." $token_name (<font style='color:green'>".round($coin_left_value,2)." USD</font>)</p>
			</div>
			<div class='card-footer text-center'>
			  Total Profit Amount is <b>".round($total_amount,2)." USD</b>. Its <b>x".round($xs,1)."</b> from your initial investment!
			</div>
		  </div>
		  <br>";
	}else
	{
		$xs=$total_amount/$total_investment;
		
		echo "
			<div class='card-footer text-center'>
			  Total Profit Amount is <b>".round($total_amount,2)." USD</b>. Its <b>x".round($xs,1)."</b> from your initial investment!
			</div>
		  </div>
		  <br>";		
		
		
		
	}
	echo "
		<p align='right'>
				<button class='btn btn-primary ml-2' onclick='printPage()'>Print Page</button>
			<a href='index.php'><button class='btn btn-primary ml-2'>Return</button></a></p>
		<hr/>
		<p align='center' style='font-size:9px'>HODL with confidence, reap the rewards with ease. Created with &hearts; by Exrienz @2025</p>
		</div>
		<script>
		  function printPage() {
			window.print();
		  }
		</script>
	  </body>
	</html>
	";
  
  
  
  
  
  //=============================================================================================================
} elseif (isset($_GET['hodl_id']) && !empty($_GET['hodl_id'])){	
  //=============================================================================================================



    // The request is a GET request
	// Handle the GET request here
	//connect db and get data
	$unique_id = htmlentities(trim(str_replace(' ', '', $_GET["hodl_id"])));

	// Create a new MySQLi object and connect to the database
	$conn = new mysqli($host, $username, $password, $database);

	// Check for errors
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// Prepare the query to select the data from the table
	$sql = "SELECT current_price, original_current_price, ath, total_coin, token_name
			FROM hodl
			WHERE unique_id = ?";

	if ($stmt = $conn->prepare($sql)) {
		// Bind the variable to the prepared statement
		$stmt->bind_param("s", $unique_id);

		// Execute the statement to select the data
		$stmt->execute();

		// Bind the result variables
		$stmt->bind_result($current_price, $original_current_price, $ath, $total_coin, $token_name);

		// Fetch the results
		$stmt->fetch();

		// Close the statement
		$stmt->close();
	}else{
		echo "Hodl ID not found!<script>window.location = '".$full_domain."/index.php';</script>";
	}

	// Close the database connection
	$conn->close();
	
	if(empty($total_coin) || isset($_POST['use_sample_value'])) {
		$total_coin=50/$current_price;
		//echo "data";		
	}
	
	if(empty($original_current_price)) {
		$original_current_price=$current_price;
		//echo "data";		
	}

	

    // Check if the ATH is greater than the current price
    if($ath <= $current_price) {
        echo "Invalid input. ATH must be greater than the current price.";
        return;
    }

    //Calculate total investment 
    $total_investment = $total_coin * $current_price;
    $coin_to_sell = $total_coin/($ath/$current_price);
	$current_coint_count=0;
    $total_amount = 0;
	$alert = false;
	$counter = 0;
	$min_amount=2;
	$profit=0;
	$xfromath=$ath/$current_price;
	@$original_current_price=htmlentities($original_current_price);
	$double_current_price=$current_price+$original_current_price;
	
	echo "
	<!DOCTYPE html>
	<html>
	  <head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
		<title>$token_name</title>
		<meta name='description' content='$token_name @ $current_price USDT. Hit & Run Strategy HODL with confidence, reap the rewards with ease. Created with &hearts; by Exrienz @2023'>
		<link rel='stylesheet' href='style.css'>
		<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css'>
	  </head>
	  <body>
		<div class='container'>
		  <div class='card'>
		<div class='card-header text-center'>
			<a href='index.php'><img src='hodl.png' alt='HODL Calculator' style='max-width: 100%;'></a>
		</div>

	";
	
	echo "
	<div class='card-body' style='font-size: 14px;'>
          <h4 class='card-title'>$token_name HODL Settings:</h4>
          <ul class='list-group'>
            <li class='list-group-item'><b>Token Name:</b> $token_name</li>
            <li class='list-group-item'><b>Current Token Price:</b> $current_price USD (x".round($xfromath,0)." from ATH)</li>
            <li class='list-group-item'><b>Token Price All Time High (ATH):</b> $ath USD</li>
            <li class='list-group-item'><b>Total Token In Posession:</b> ".round($total_coin,5)."</li>
            <li class='list-group-item'><b>Initial Investment:</b> ".round($total_investment,2)." USD</li>
          </ul>
        </div>
        <div class='card-footer text-center'>
          <form method='post' action=''>
            <input type='hidden' value='".$token_name."' name='token_name'>
            <input type='hidden' value='".$double_current_price."' name='current_price' required>
            <input type='hidden' value='".$ath."' name='ath' required>
            <input type='hidden' value='".$total_coin."' id='total_coin' name='total_coin'>
            <input type='hidden' value='".$original_current_price."' name='original_current_price'>
            <button class='btn btn-primary btn-block' width='100%' name='submit'>Too many Sell-Limit Orders? Click here to double the Entry Point</button>
          </form>
        </div>
      </div>
      <br>
	";	
	
	echo '
	<div class="card" style="font-size: 12px;">
		<div class="card-header text-center">
			<h5 class="card-title">Sell-Limit Orders</h5>
		</div>
		<div class="card-body">
	';

    for($x = 2; $x < ($ath/$current_price); $x++) {
        $price = $current_price*$x;
        $amount = $coin_to_sell*$price;
        $total_amount += $amount;
		$current_coint_count += $coin_to_sell;
		$coin_left=$total_coin-$current_coint_count;
		$coin_left_value=$coin_left*$price;
		$counter++;
		$profit=$profit+$amount;
		$sell_all=$profit+$coin_left_value;
		        
		if ($total_amount > $total_investment && !$alert) {
			$balance=$total_amount-$total_investment;		
			echo "<p><b>TP $counter :::: Sell ".round($coin_to_sell,5)." of coin at ".round($price,4)." USD (x$x)::::</b></p>";
			echo "<p>You will get <b style='color:green'>".round($amount,2)." USD</b>. Your accumulated profit now is <b style='color:green'>".round($profit,2)." USD</b></p>
			  <p style='font-size:12px' class='text-danger'>Breakeven! Your initial investment, <b style='color:green'>".round($total_investment,2)." USD</b> claimed with extra profit <b style='color:green'>".round($balance,5)." USD</b>! Leave moonbag and invest on other coin!</p>
			  <p>Token balance in posession now is ".round($coin_left,5)." $token_name (<font style='color:green'>".round($coin_left_value,2)." USD</font>)</p>
			  <p>If you sell everything now, your total profit is <font style='color:green'>".round($sell_all,2)." USD</font></p>";
            $alert = true;
        } else{
			echo "<p><b>TP $counter :::: Sell ".round($coin_to_sell,5)." of coin at ".round($price,4)." USD (x$x)::::</b></p>";
			echo "<p>You will get <b style='color:green'>".round($amount,2)." USD</b>. Your accumulated profit now is <b style='color:green'>".round($profit,2)." USD</b></p>
			  <p>Token balance in posession now is ".round($coin_left,5)." $token_name (<font style='color:green'>".round($coin_left_value,2)." USD</font>)</p>
			  <p>If you sell everything now, your total profit is <font style='color:green'>".round($sell_all,2)." USD</font></p>";
		}
		echo "<hr/>";
    }
    
	if($current_coint_count <= $total_coin){
		$bal_coin=$total_coin-$current_coint_count;
		$last_coint_amnt=$bal_coin*$ath;
		$total_amount=$total_amount+$last_coint_amnt;
		$xs=$total_amount/$total_investment;
		$coin_left=0;
		$coin_left_value=$coin_left*$ath;
		$counter++;
		$profit=$profit+$last_coint_amnt;
		echo "<p><b>TP $counter :::: Sell ".round($bal_coin,5)." of coin at ".round($ath,4)." USD (x$x)::::</b></p>";
		echo "<p>You will get <b style='color:green'>".round($last_coint_amnt,2)." USD</b>. Your accumulated profit now is <b style='color:green'>".round($profit,2)." USD</b></p>
			  <p>Token balance in posession now is ".round($coin_left,5)." $token_name (<font style='color:green'>".round($coin_left_value,2)." USD</font>)</p>
			</div>
			<div class='card-footer text-center'>
			  Total Profit Amount is <b>".round($total_amount,2)." USD</b>. Its <b>x".round($xs,1)."</b> from your initial investment!
			</div>
		  </div>
		  <br>";
	}else
	{
		$xs=$total_amount/$total_investment;
		
		echo "
			<div class='card-footer text-center'>
			  Total Profit Amount is <b>".round($total_amount,2)." USD</b>. Its <b>x".round($xs,1)."</b> from your initial investment!
			</div>
		  </div>
		  <br>";		
		
		
		
	}
	echo "
		<p align='right'>
			<!--<form method='post'><input type='submit' class='btn btn-primary ml-2' name='convert' value='Convert to Text'></form>-->
			<button class='btn btn-primary ml-2' onclick='printPage()'>Print Page</button>
			<a href='index.php'><button class='btn btn-primary ml-2'>Return</button></a></p>
		<hr/>
		<p align='center' style='font-size:9px'>HODL with confidence, reap the rewards with ease. Created with &hearts; by Exrienz @2023</p>
		</div>
		<script>
		  function printPage() {
			window.print();
		  }
		</script>
	  </body>
	</html>
	";
	

	//if(isset($_POST['convert'])) {
		//@$unique_id = htmlentities(trim($_POST['unique_id']));
		//@$output = shell_exec("python3 tp2text.py $unique_id > $unique_id.txt");
		//echo "<script>window.open('$unique_id.txt', '_blank');</script>";
		//@$output = shell_exec("rm $unique_id.txt");
	//}


  




  //=============================================================================================================
} else {
  // No form submitted or GET request, show the form
  
  echo "
	<!DOCTYPE html>
	<html>
	  <head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
		<title>HODL Calculator</title>
		<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css'>
	  </head>	  
	  <body>
		<div class='container'>
		  <div class='card'>
		<div class='card-header text-center'>
			<a href='index.php'><img src='hodl.png' alt='HODL Calculator' style='max-width: 100%;'></a>
		</div>
		  <div class='card' style='font-size:12px'>
			<div class='card-body'>
			  <form method='post' action=''>
				<div class='form-group'>
				  <label for='token_name'>Token Name:</label>
				  <input type='text' class='form-control' id='token_name' name='token_name' required>
				</div>
				<div class='form-group'>
				  <label for='current_price'>Current Token Price:</label>
				  <input type='text' class='form-control' id='current_price' name='current_price' required>
				</div>
				<div class='form-group'>
				  <label for='ath'>Token Price All Time High (ATH) or Expected Price to Achieve:</label>
				  <input type='text' class='form-control' id='ath' name='ath' required>
				</div>
				<div class='form-group'>
				  <label for='total_coin'>Total Coin ( Your Initial Investment(USD) / Current Price ):</label>
				  <input type='text' class='form-control' id='total_coin' name='total_coin'>
				</div>
				<div class='form-row'>
				  <div class='form-group col-md-6'>
					<div class='form-check'>
					  <input type='checkbox' class='form-check-input' id='use_sample_value' name='use_sample_value'>
					  <label class='form-check-label' for='use_sample_value'>Sample trading with 50 USD?</label>
					</div>
				  </div>
				</div>
				<div class='text-right'>
					<input type='submit' class='btn btn-primary' value='Submit' name='submit'>
				</div>
			  </form>
			</div>
		  </div>
		  <p align='center' style='font-size:9px'>HODL with confidence, reap the rewards with ease. Created with &hearts; by Exrienz @2025</p>
	  </body>
	</html>
		";
}
?>