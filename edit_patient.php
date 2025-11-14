<?php
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$pid = $_GET['pid'];

if (isset($_POST['update_patient'])) {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    
    $sql = "UPDATE patients SET fullname='$fullname', phone='$phone', username='$username' WHERE pid=$pid";
    if ($conn->query($sql)) {
        header("Location: dashboard.php?view=patients");
        exit();
    } else {
        $error = "Error updating record: " . $conn->error;
    }
}

$sql = "SELECT * FROM patients WHERE pid = $pid";
$result = $conn->query($sql);
$patient = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Patient</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="main-page">

<div class="main-header">
    <h1>Hospital Management System</h1>
    <p>DBMS Project Prototype - 736 & 769</p>
</div>

<div class="main-nav">
     <a href="dashboard.php?view=patients" class="btn-secondary">Back to Patient Management</a>
     <a href="login.php" class="logout-link">Logout</a>
</div>

<div class="content-card" style="max-width: 500px; margin: 30px auto;">
    <h2>Editing Patient: <?php echo $patient['fullname']; ?></h2>
    
    <?php if(isset($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Full Name:</label>
        <input type="text" name="fullname" value="<?php echo $patient['fullname']; ?>" required>
        
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo $patient['phone']; ?>" required>
        
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo $patient['username']; ?>" required>
        
        <p style="font-size:0.9rem; color:#555;">(Password cannot be changed from this panel)</p>

        <button type="submit" name="update_patient" class="btn-success">Save Changes</button>
    </form>
</div>

</body>
</html>