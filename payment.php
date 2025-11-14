<?php
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['make_payment'])) {
    $app_id = $_POST['app_id'];
    $card = $_POST['card_num'];

    if (strlen($card) >= 12) {
        $sql = "UPDATE payments SET payment_status = 'Paid', payment_date = NOW() WHERE app_id = $app_id";
        if ($conn->query($sql)) {
            header("Location: dashboard.php?payment=success");
            exit();
        }
    } else {
        $error = "Please enter a valid 12-digit card number.";
    }
}

if (!isset($_GET['app_id'])) {
    header("Location: dashboard.php");
    exit();
}
$app_id = $_GET['app_id'];
$amount = $_GET['amount'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Make Payment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="main-page">

<div class="main-header">
    <h1>Hospital Management System</h1>
    <p>DBMS Project Prototype - 736 & 769</p>
</div>

<div class="main-nav">
     <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
     <a href="login.php" class="logout-link">Logout</a>
</div>


<div class="content-card" style="max-width: 500px; margin: 30px auto;">
    <h3>Complete Your Payment</h3>
    <p style="font-size: 1.1rem;">You are paying <strong>$<?php echo htmlspecialchars($amount); ?></strong> for Appointment ID: <?php echo htmlspecialchars($app_id); ?></p>
    
    <?php if(isset($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Card Number (enter any 12 digits):</label>
        <input type="text" name="card_num" required placeholder="XXXX XXXX XXXX" minlength="12" pattern="\d{12,}">
        <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">
        <button type="submit" name="make_payment" class="btn-success">Pay Now</button>
    </form>
</div>

</body>
</html>