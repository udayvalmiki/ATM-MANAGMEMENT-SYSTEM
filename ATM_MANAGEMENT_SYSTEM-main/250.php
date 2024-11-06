<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "atm1") or die(mysqli_errno($con));
session_start();
$pin = $_SESSION['Pin'];

// Fetch current balance, user_id, and daily limit
$select_query = "SELECT c.balance, c.user_id, a.daily_limit FROM card c JOIN account a ON c.user_id = a.user_id WHERE c.card_pin = $pin";
$select_query_result = mysqli_query($con, $select_query) or die(mysqli_error($con));
$row = mysqli_fetch_array($select_query_result);
$current_balance = $row['balance'];
$user_id = $row['user_id'];
$daily_limit = $row['daily_limit'];

$withdrawal_amount = 250;

// Check if the withdrawal amount exceeds the daily limit
if ($withdrawal_amount <= $daily_limit) {
    // Check if there is enough balance
    if ($current_balance >= $withdrawal_amount) {
        // Update the balance and perform the withdrawal
        $update_query = "UPDATE account SET balance = balance - $withdrawal_amount WHERE user_id = $user_id";
        $update_query_result = mysqli_query($con, $update_query) or die(mysqli_error($con));

        // Update daily limit
        $new_daily_limit = $daily_limit - $withdrawal_amount;
        $update_daily_limit_query = "UPDATE account SET daily_limit = $new_daily_limit WHERE user_id = $user_id";
        mysqli_query($con, $update_daily_limit_query) or die(mysqli_error($con));

        // Insert transaction record into transaction table for successful transaction
        $insert_transaction_query = "INSERT INTO transaction (transaction_date, transaction_status, transaction_type, user_id) 
                                    VALUES (NOW(), 'Successful', 'Withdrawal', $user_id)";
        mysqli_query($con, $insert_transaction_query) or die(mysqli_error($con));

        // Display success message
        $message = "Transaction Successful. Please collect Your Money";
    } else {
        // Insert transaction record into transaction table for failed transaction
        $insert_transaction_query = "INSERT INTO transaction (transaction_date, transaction_status, transaction_type, user_id) 
                                    VALUES (NOW(), 'Failed', 'Withdrawal', $user_id)";
        mysqli_query($con, $insert_transaction_query) or die(mysqli_error($con));

        // Display insufficient balance message
        $message = "Insufficient Balance. Unable to complete the transaction.";
    }
} else {
    // Display error message for exceeding daily limit
    $message = "Exceeded daily withdrawal limit. Maximum withdrawal amount for today left is $daily_limit";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Type</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="style.css" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body style="background-image:url(img1/atm4.jpg)">
    <div class="header">
        <div class="inner-header">
            <div class="logo">
                <p>
                    <center>CoderBank ATM</center><br><br><br> <br><br><br>
                </p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="padding">
            <table>
                <tbody>
                    <th>
                        <h1><br><br><br><?php echo $message; ?><br><br><br><br></h1>
                    </th>
                    <tr>
                        <td>
                            <a href="balance.php" class="button">View my Balance</a> <br><br><br> &emsp; 
                        </td>
                        <td>
                            <a href="index.php" class="button">Exit</a><br><br><br> &emsp;
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
