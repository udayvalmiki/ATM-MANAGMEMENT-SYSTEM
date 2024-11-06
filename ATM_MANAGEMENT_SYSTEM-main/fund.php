<?php 
$con = mysqli_connect("localhost", "root", "", "atm1") or die(mysqli_errno($con));
session_start();
$pin = $_SESSION['Pin'];
$account = $_POST['account'];
$amount = $_POST['amount'];

// Fetch current balance and daily limit of the user
$select_user_data_query = "SELECT c.balance, a.user_id, a.daily_limit 
                           FROM card c 
                           INNER JOIN account a ON c.user_id = a.user_id 
                           WHERE c.card_pin = $pin";
$select_user_data_result = mysqli_query($con, $select_user_data_query) or die(mysqli_error($con));
$row_user_data = mysqli_fetch_array($select_user_data_result);
$user_balance = $row_user_data['balance'];
$user_id = $row_user_data['user_id'];
$daily_limit = $row_user_data['daily_limit'];

// Insert transaction record into transaction table
$insert_transaction_query = "INSERT INTO `transaction` (`transaction_date`, `transaction_status`, `transaction_type`, `user_id`) 
                              VALUES (NOW(), '', 'Transfer', $user_id)";
mysqli_query($con, $insert_transaction_query) or die(mysqli_error($con));

// Check if the user has enough balance for the transfer and if the transfer amount exceeds the daily limit
if ($user_balance >= $amount && $amount <= $daily_limit) {
    // Update the balance of the recipient account
    $update_recipient_balance_query = "UPDATE account SET balance = balance + $amount WHERE account_number = $account";
    $update_recipient_balance_result = mysqli_query($con, $update_recipient_balance_query) or die(mysqli_error($con));

    // Update the balance of the user making the transfer
    $update_user_balance_query = "UPDATE account SET balance = balance - $amount WHERE user_id = $user_id";
    $update_user_balance_result = mysqli_query($con, $update_user_balance_query) or die(mysqli_error($con));

    // Update the daily limit
    $updated_daily_limit = $daily_limit - $amount;
    $update_daily_limit_query = "UPDATE account SET daily_limit = $updated_daily_limit WHERE user_id = $user_id";
    mysqli_query($con, $update_daily_limit_query) or die(mysqli_error($con));

    // Update transaction status
    $update_transaction_query = "UPDATE `transaction` SET `transaction_status` = 'Successful' WHERE `user_id` = $user_id ORDER BY `transaction_id` DESC LIMIT 1";
    mysqli_query($con, $update_transaction_query) or die(mysqli_error($con));

    // Display success message
    $message = "Transaction Successful. Click here to check your balance";
} else {
    // Update transaction status
    $update_transaction_query = "UPDATE `transaction` SET `transaction_status` = 'Failed' WHERE `user_id` = $user_id ORDER BY `transaction_id` DESC LIMIT 1";
    mysqli_query($con, $update_transaction_query) or die(mysqli_error($con));

    // Display insufficient balance message or daily limit exceeded message
    if ($amount > $daily_limit) {
        $message = "Daily limit exceeded. Maximum transfer amount per day is $daily_limit";
    } else {
        $message = "Insufficient Balance. Unable to complete the transaction.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Transfer</title>
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
                    <center>CoderBank ATM</center> 
                </p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="padding">
            <table>
                <tbody>
                    <tr>
                        <th><h1><br><br><br><?php echo $message; ?><br><br><br><br></h1></th>
                    </tr>
                    <tr>
                        <td><a href="balance.php" class="button">View my Balance</a><br><br><br> &emsp;</td>
                        <td><a href="index.php" class="button">Exit</a><br><br><br> &emsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
