<?php
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$doc_id = $_GET['doc_id'];

function get_image_path($img) {
    if (filter_var($img, FILTER_VALIDATE_URL)) {
        return $img;
    }
    if (!empty($img) && file_exists("uploads/" . $img)) {
        return "uploads/" . $img;
    }
    return "uploads/default.png";
}

if (isset($_POST['update_doctor'])) {
    $doc_name = $_POST['doc_name'];
    $specialization = $_POST['specialization'];
    $email = $_POST['email'];
    $consultancy_fees = $_POST['consultancy_fees'];
    
    $image_sql_part = "";
    
    if (isset($_FILES['doc_image']) && $_FILES['doc_image']['error'] == 0) {
        $target_dir = "uploads/";
        $image_name = basename($_FILES["doc_image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES["doc_image"]["tmp_name"], $target_file)) {
            $image_sql_part = ", doc_image='" . $conn->real_escape_string($image_name) . "'";
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }

    if (!isset($error)) {
        $sql = "UPDATE doctors SET 
                doc_name='" . $conn->real_escape_string($doc_name) . "', 
                specialization='" . $conn->real_escape_string($specialization) . "', 
                email='" . $conn->real_escape_string($email) . "', 
                consultancy_fees='" . $conn->real_escape_string($consultancy_fees) . "'
                $image_sql_part
                WHERE doc_id=$doc_id";
                
        if ($conn->query($sql)) {
            header("Location: index.php?view=doctors&status=updated");
            exit();
        } else {
            $error = "Error updating record: " . $conn->error;
        }
    }
}

$sql = "SELECT * FROM doctors WHERE doc_id = $doc_id";
$result = $conn->query($sql);
$doctor = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Doctor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="main-page">

<div class="main-header">
    <h1>Hospital Management System</h1>
    <p>DBMS Project Prototype - 736 & 769</p>
</div>

<div class="main-nav">
     <a href="index.php?view=doctors" class="btn-secondary">Back to Doctor Management</a>
     <a href="login.php" class="logout-link">Logout</a>
</div>

<div class="content-card" style="max-width: 500px; margin: 30px auto;">
    <h2>Editing Doctor: <?php echo htmlspecialchars($doctor['doc_name']); ?></h2>
    
    <?php if(isset($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Doctor Name:</label>
        <input type="text" name="doc_name" value="<?php echo htmlspecialchars($doctor['doc_name']); ?>" required>
        
        <label>Specialization:</label>
        <input type="text" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
        
        <label>Consultancy Fees:</label>
        <input type="text" name="consultancy_fees" value="<?php echo htmlspecialchars($doctor['consultancy_fees']); ?>" required>

        <label>Current Photo:</label>
        <div>
            <img src="<?php echo get_image_path($doctor['doc_image']); ?>" alt="Current Photo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 15px;">
        </div>
        
        <label>Or Upload New Photo (Optional):</label>
        <input type="file" name="doc_image">

        <button type="submit" name="update_doctor" class="btn-success">Save Changes</button>
    </form>
</div>

</body>
</html>