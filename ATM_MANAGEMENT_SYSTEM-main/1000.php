<?php
$con = mysqli_connect("localhost", "root", "", "atm1") or die(mysqli_errno($con));
session_start();
$pin = $_SESSION['Pin'];

// Fetch current balance and daily limit
$select_query = "SELECT c.balance, a.daily_limit FROM card c 
                INNER JOIN account a ON c.user_id = a.user_id 
                WHERE c.card_pin = '$pin'";
$select_query_result = mysqli_query($con, $select_query) or die(mysqli_error($con));
$row = mysqli_fetch_array($select_query_result);
$current_balance = $row['balance'];
$daily_limit = $row['daily_limit'];

$withdrawal_amount = 1000;

// Check if there is enough balance and if withdrawal amount exceeds daily limit
if ($current_balance >= $withdrawal_amount && $withdrawal_amount <= $daily_limit) {
    // Update the balance and perform the withdrawal
    $update_balance_query = "UPDATE account SET balance = balance - $withdrawal_amount WHERE user_id IN 
                            (SELECT user_id FROM card WHERE card_pin = '$pin')";
    mysqli_query($con, $update_balance_query) or die(mysqli_error($con));

    // Update the daily limit
    $updated_daily_limit = $daily_limit - $withdrawal_amount;
    $update_daily_limit_query = "UPDATE account SET daily_limit = $updated_daily_limit WHERE user_id IN 
                                (SELECT user_id FROM card WHERE card_pin = '$pin')";
    mysqli_query($con, $update_daily_limit_query) or die(mysqli_error($con));

    // Insert transaction record into transaction table for successful transaction
    $insert_transaction_query = "INSERT INTO `transaction` (`transaction_date`, `transaction_status`, `transaction_type`, `user_id`) 
                                VALUES (NOW(), 'Successful', 'Withdrawal', 
                                (SELECT user_id FROM card WHERE card_pin = '$pin'))";
    mysqli_query($con, $insert_transaction_query) or die(mysqli_error($con));

    // Display success message
    $message = "Transaction Successful. Please collect Your Money";
} else {
    // Insert transaction record into transaction table for failed transaction
    $insert_transaction_query = "INSERT INTO `transaction` (`transaction_date`, `transaction_status`, `transaction_type`, `user_id`) 
                                VALUES (NOW(), 'Failed', 'Withdrawal', 
                                (SELECT user_id FROM card WHERE card_pin = '$pin'))";
    mysqli_query($con, $insert_transaction_query) or die(mysqli_error($con));

    // Display insufficient balance message or daily limit exceeded message
    if ($withdrawal_amount > $daily_limit) {
        $message = "Daily limit exceeded. Maximum withdrawal amount for  today left is $daily_limit";
    } else {
        $message = "Insufficient Balance. Unable to complete the transaction.";
    }
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
