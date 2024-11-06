<?php 
// Establish database connection
$con = mysqli_connect("localhost", "root", "", "atm1") or die(mysqli_errno($con));
session_start();
$pin = $_SESSION['Pin'];
$cash = $_POST['cash'];

// Check if the withdrawal amount is less than or equal to the available balance
$select_balance_query = "SELECT balance, daily_limit FROM account WHERE user_id = (SELECT user_id FROM card WHERE card_pin = $pin)";
$select_balance_result = mysqli_query($con, $select_balance_query) or die(mysqli_error($con));
$row = mysqli_fetch_assoc($select_balance_result);
$current_balance = $row['balance'];
$daily_limit = $row['daily_limit'];

// Check if the withdrawal amount is within the daily limit
if ($cash <= $current_balance && $cash <= $daily_limit) {
    // If the withdrawal amount is less than or equal to the available balance and within the daily limit, proceed with the transaction
    // Update the balance in the account table after withdrawal
    $update_query = "UPDATE account SET balance = balance - $cash WHERE user_id = (SELECT user_id FROM card WHERE card_pin = $pin)";
    $update_query_result = mysqli_query($con, $update_query) or die(mysqli_error($con));

    // Deduct the withdrawal amount from the daily limit
    $update_limit_query = "UPDATE account SET daily_limit = daily_limit - $cash WHERE user_id = (SELECT user_id FROM card WHERE card_pin = $pin)";
    $update_limit_result = mysqli_query($con, $update_limit_query) or die(mysqli_error($con));

    // Set the transaction status to Successful
    $transaction_status = "Successful";

    // Insert transaction details into the transaction table
    $transaction_date = date('Y-m-d');
    $transaction_type = "Withdrawal";
    $insert_query = "INSERT INTO transaction (transaction_date, transaction_status, transaction_type, user_id) 
                     VALUES ('$transaction_date', 'Successful', '$transaction_type', (SELECT user_id FROM card WHERE card_pin = $pin))";
    $insert_result = mysqli_query($con, $insert_query) or die(mysqli_error($con));

    // Set the success message
    $message = "Transaction Successful. Please collect your money.";
} else {
    // If the withdrawal amount exceeds the available balance or the daily limit, set the transaction status to Failed
    $transaction_status = "Failed";

    // Set the error message
    $message = "Transaction Failed. Insufficient Balance or Exceeded Daily Limit.";

    // Still insert transaction details into the transaction table even if the transaction fails
    $transaction_date = date('Y-m-d');
    $transaction_type = "Withdrawal";
    $insert_query = "INSERT INTO transaction (transaction_date, transaction_status, transaction_type, user_id) 
                     VALUES ('$transaction_date', 'Failed', '$transaction_type', (SELECT user_id FROM card WHERE card_pin = $pin))";
    $insert_result = mysqli_query($con, $insert_query) or die(mysqli_error($con));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Account</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="style.css" rel="stylesheet" type="text/css">
    <style>
        body {
            background-image: url(img1/atm4.jpg);
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .padding {
            padding: 20px;
            text-align: center;
        }
        .button {
            margin: 10px;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<div class="header">
    <div class="inner-header">
        <div class="logo">
            <p><center>CoderBank ATM</center></p>
        </div>
    </div>
</div>
<div class="container">
    <div class="padding">
        <h2><?php echo $message; ?></h2>
        <a href="balance.php" class="button btn btn-primary">View my Balance</a>
        <a href="index.php" class="button btn btn-primary">Exit</a>
    </div>
</div>
</body>
</html>
