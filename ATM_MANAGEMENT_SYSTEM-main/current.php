<?php 
// Database connection
$con = mysqli_connect("localhost", "root", "", "atm1") or die(mysqli_errno($con));
session_start();
$pin = $_SESSION['Pin'];
$cash1 = $_POST['cash1'];

// Fetch the daily limit
$select_daily_limit_query = "SELECT daily_limit FROM account WHERE user_id = (SELECT user_id FROM card WHERE card_pin = $pin)";
$select_daily_limit_result = mysqli_query($con, $select_daily_limit_query) or die(mysqli_error($con));
$row_daily_limit = mysqli_fetch_array($select_daily_limit_result);
$daily_limit = $row_daily_limit['daily_limit'];

// Fetch the available balance
$select_balance_query = "SELECT balance FROM card WHERE card_pin = $pin";
$select_balance_result = mysqli_query($con, $select_balance_query) or die(mysqli_error($con));
$row_balance = mysqli_fetch_array($select_balance_result);
$available_balance = $row_balance['balance'];

// Check if the available balance is less than the withdrawal amount
if ($available_balance < $cash1) {
    // Display error message for insufficient balance
    $message = "Insufficient balance. Unable to complete the transaction.";
} elseif ($cash1 > $daily_limit) {
    // Display error message for exceeding daily limit
    $message = "Exceeded daily withdrawal limit. Unable to complete the transaction.";
} else {
    // Update the daily limit in the database (subtract the withdrawal amount)
    $new_daily_limit = $daily_limit - $cash1;
    $update_daily_limit_query = "UPDATE account SET daily_limit = $new_daily_limit WHERE user_id = (SELECT user_id FROM card WHERE card_pin = $pin)";
    mysqli_query($con, $update_daily_limit_query) or die(mysqli_error($con));

    // Update the balance
    $update_balance_query = "UPDATE card SET balance = balance - $cash1 WHERE card_pin = $pin";
    mysqli_query($con, $update_balance_query) or die(mysqli_error($con));

    // Display success message
    $message = "Transaction Successful. Please collect Your Money";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Account</title>
    <link  rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" >
    <link href="style.css" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" ></script>
</head>
<body style="background-image:url(img1/atm4.jpg)">
    <link href="style.css" rel="stylesheet" type="text/css"/>
    <div class="header">
        <div class="inner-header">
            <div class="logo">
                <p><center>
                    CoderBank ATM</center><br><br><br> <br><br><br>
                </p>
            </div>
        </div>
    </div>
     <div class="container">
        <div class="padding">
    <table>
            <tbody>
            <th><h1>
                    <br><br><br><?php echo $message; ?><br><br><br><br>
                </h1>
            </th>
                <tr>
                    <td>
                        <a href="balance.php" class="button">View my Balance</a> <br><br><br> &emsp; 
                    </td>
                    <td>
                        <a href="index.php" class="button">Exit</a><br><br><br> &emsp;
                    </td>
                </tr>
        </div>
     </div>
    </body>
    </html>
