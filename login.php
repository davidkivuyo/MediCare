<?php
include 'db.php';

$doctors_result = $conn->query("SELECT * FROM doctors ORDER BY doc_name");

if (isset($_POST['register'])) {
    $reg_user = $_POST['reg_user'];
    $reg_pass = password_hash($_POST['reg_pass'], PASSWORD_DEFAULT);
    $reg_name = $_POST['reg_name'];
    $reg_phone = $_POST['reg_phone'];

    $sql = "INSERT INTO patients (username, password, fullname, phone) VALUES ('$reg_user', '$reg_pass', '$reg_name', '$reg_phone')";
    
    if ($conn->query($sql)) {
        echo "<script>alert('Registration Successful! Please Login.');</script>";
    } else {
        if (str_contains($conn->error, "Duplicate entry")) {
            echo "<script>alert('Error: This username is already taken. Please choose another.');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'admin') {
        $_SESSION['user'] = 'admin';
        $_SESSION['role'] = 'admin';
        header("Location: index.php");
        exit();
    } 
    else {
        $sql = "SELECT * FROM patients WHERE username='$username' AND password='$password'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['user'] = $row['fullname'];
            $_SESSION['role'] = 'patient';
            $_SESSION['pid'] = $row['pid'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid Username or Password";
        }
    }
}

function get_image_path($img) {
    if (filter_var($img, FILTER_VALIDATE_URL)) {
        return $img;
    }
    if (!empty($img) && file_exists("uploads/" . $img)) {
        return "uploads/" . $img;
    }
    return "uploads/default.png";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>HMS Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="main-page">

    <div class="main-header">
        <h1>Hospital Management System</h1>
        <p>DBMS Project Prototype - 736 & 769</p>
    </div>

    <div class="doctor-scroller-container">
        <h2>Meet Our Specialists</h2>
        <div class="doctor-scroller">
            <?php 
            if ($doctors_result) {
                while($doc = $doctors_result->fetch_assoc()): 
                    $image_path = get_image_path($doc['doc_image']);
                ?>
                    <div class="mini-doctor-card">
                        <img src="<?php echo $image_path; ?>" alt="<?php echo $doc['doc_name']; ?>">
                        <h4><?php echo $doc['doc_name']; ?></h4>
                        <p><?php echo $doc['specialization']; ?></p>
                    </div>
            <?php 
                endwhile; 
            } else {
                echo "<p style='color:white;'>Could not fetch doctor information.</p>";
            }
            ?>
        </div>
    </div>


    <div class="login-card">
        <div>
            <h3>Login</h3>
            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            <form method="POST">
                <label>Username (Use 'admin' for Admin)</label>
                <input type="text" name="username" required>
                <label>Password</label>
                <input type="password" name="password">
                <button type="submit" name="login">Login</button>
            </form>
        </div>
        
        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <div>
            <h3>New Patient? Register</h3>
            <form method="POST">
                <label>Full Name</label>
                <input type="text" name="reg_name" required>
                <label>Phone Number</label>
                <input type="text" name="reg_phone" required>
                <label>Create Username</label>
                <input type="text" name="reg_user" required>
                <label>Create Password</label>
                <input type="password" name="reg_pass" required>
                <button type="submit" name="register" class="btn-success">Register</button>
            </form>
        </div>
    </div>
</body>
</html>