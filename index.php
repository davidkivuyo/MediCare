<?php
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_name = $_SESSION['user'];
$view = $_GET['view'] ?? ($role == 'admin' ? 'appointments' : 'my_appointments');

function get_image_path($img) {
    if (filter_var($img, FILTER_VALIDATE_URL)) {
        return $img;
    }
    if (!empty($img) && file_exists("uploads/" . $img)) {
        return "uploads/" . $img;
    }
    return "uploads/default.png";
}

if ($role == 'admin') {
    if (isset($_GET['delete_app'])) {
        $app_id = $_GET['delete_app'];
        $conn->query("DELETE FROM appointments WHERE app_id=$app_id");
        header("Location: index.php?view=appointments");
        exit();
    }
    
    if (isset($_GET['delete_patient'])) {
        $pid = $_GET['delete_patient'];
        $conn->query("DELETE FROM patients WHERE pid=$pid");
        header("Location: index.php?view=patients");
        exit();
    }
    
    if (isset($_GET['delete_doctor'])) {
        $doc_id = $_GET['delete_doctor'];
        $conn->query("DELETE FROM doctors WHERE doc_id=$doc_id");
        header("Location: index.php?view=doctors");
        exit();
    }
    
    if (isset($_POST['add_patient'])) {
        $fullname = $_POST['fullname'];
        $phone = $_POST['phone'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $sql = "INSERT INTO patients (fullname, phone, username, password) VALUES ('$fullname', '$phone', '$username', '$password')";
        $conn->query($sql);
        header("Location: index.php?view=patients");
        exit();
    }
    
    if (isset($_POST['add_doctor'])) {
        $doc_name = $_POST['doc_name'];
        $specialization = $_POST['specialization'];
        $email = $_POST['email'];
        $consultancy_fees = $_POST['consultancy_fees'];
        
        $image_name = 'default.png';
        
        if (isset($_FILES['doc_image']) && $_FILES['doc_image']['error'] == 0) {
            $target_dir = "uploads/";
            $image_name = basename($_FILES["doc_image"]["name"]);
            $target_file = $target_dir . $image_name;
            
            if (!move_uploaded_file($_FILES["doc_image"]["tmp_name"], $target_file)) {
                echo "<script>alert('Error uploading file.');</script>";
                $image_name = 'default.png';
            }
        }

        $sql = "INSERT INTO doctors (doc_name, specialization, email, consultancy_fees, doc_image) 
                VALUES (
                    '" . $conn->real_escape_string($doc_name) . "', 
                    '" . $conn->real_escape_string($specialization) . "', 
                    '" . $conn->real_escape_string($email) . "', 
                    '" . $conn->real_escape_string($consultancy_fees) . "', 
                    '" . $conn->real_escape_string($image_name) . "'
                )";
        
        $conn->query($sql);
        header("Location: index.php?view=doctors");
        exit();
    }

} elseif ($role == 'patient') {
    $pid = $_SESSION['pid'];

    if (isset($_GET['delete_app'])) {
        $app_id = $_GET['delete_app'];
        $conn->query("DELETE FROM appointments WHERE app_id=$app_id AND patient_id=$pid");
        header("Location: index.php?view=my_appointments");
        exit();
    }

    if (isset($_POST['book_appointment'])) {
        $doc_id = $_POST['doctor_id'];
        $app_date = $_POST['app_date'];
        $sql = "INSERT INTO appointments (patient_id, doctor_id, app_date) VALUES ($pid, $doc_id, '$app_date')";
        $conn->query($sql);
        header("Location: index.php?view=my_appointments");
        exit();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="main-page">

<div class="main-header">
    <h1>Hospital Management System</h1>
    <p>DBMS Project Prototype - 736 & 769</p>
</div>

<div class="main-nav">
    <?php if ($role == 'admin'): ?>
        <a href="?view=appointments" class="<?php echo $view == 'appointments' ? 'btn-primary' : 'btn-secondary'; ?>">View Appointments</a>
        <a href="?view=patients" class="<?php echo $view == 'patients' ? 'btn-primary' : 'btn-secondary'; ?>">View Patients</a>
        <a href="?view=add_patient" class="<?php echo $view == 'add_patient' ? 'btn-primary' : 'btn-secondary'; ?>">Add New Patient</a>
        <a href="?view=doctors" class="<?php echo $view == 'doctors' ? 'btn-primary' : 'btn-secondary'; ?>">View Doctors</a>
        <a href="?view=add_doctor" class="<?php echo $view == 'add_doctor' ? 'btn-primary' : 'btn-secondary'; ?>">Add New Doctor</a>
        <a href="?view=payments" class="<?php echo $view == 'payments' ? 'btn-primary' : 'btn-secondary'; ?>">Payment Log</a>
    <?php else: ?>
        <a href="?view=my_appointments" class="<?php echo $view == 'my_appointments' ? 'btn-primary' : 'btn-secondary'; ?>">My Appointments</a>
        <a href="?view=book_appointment" class="<?php echo $view == 'book_appointment' ? 'btn-primary' : 'btn-secondary'; ?>">Book Appointment</a>
    <?php endif; ?>
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<div class="content-card">
    <h2>Welcome, <?php echo $user_name; ?>!</h2>

    <?php if ($role == 'admin'): ?>
        
        <?php switch ($view):
            case 'appointments': ?>
                <h3>All Appointments</h3>
                <table>
                    <thead><tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Created At</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT a.*, p.fullname, d.doc_name FROM appointments a 
                                JOIN patients p ON a.patient_id = p.pid
                                JOIN doctors d ON a.doctor_id = d.doc_id
                                ORDER BY a.app_date DESC";
                        $result = $conn->query($sql);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['app_id']}</td>";
                            echo "<td>{$row['fullname']}</td>";
                            echo "<td>{$row['doc_name']}</td>";
                            echo "<td>{$row['app_date']}</td>";
                            echo "<td>{$row['created_at']}</td>";
                            echo "<td><a href='?view=appointments&delete_app={$row['app_id']}' class='btn-danger btn' onclick='return confirm(\"Delete?\")'>Delete</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php break; ?>

            <?php case 'patients': ?>
                <h3>All Patients</h3>
                <table>
                    <thead><tr><th>ID</th><th>Full Name</th><th>Phone</th><th>Username</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM patients";
                        $result = $conn->query($sql);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['pid']}</td>";
                            echo "<td>{$row['fullname']}</td>";
                            echo "<td>{$row['phone']}</td>";
                            echo "<td>{$row['username']}</td>";
                            echo "<td>
                                    <a href='edit_patient.php?pid={$row['pid']}' class='btn-edit btn'>Edit</a>
                                    <a href='?view=patients&delete_patient={$row['pid']}' class='btn-danger btn' onclick='return confirm(\"Delete?\")'>Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php break; ?>

            <?php case 'add_patient': ?>
                <h3>Add New Patient</h3>
                <form method="POST" style="max-width: 500px;">
                    <label>Full Name:</label>
                    <input type="text" name="fullname" required>
                    <label>Phone:</label>
                    <input type="text" name="phone" required>
                    <label>Username:</label>
                    <input type="text" " name="username" required>
                    <label>Password:</label>
                    <input type="password" name="password" required>
                    <button type="submit" name="add_patient" class="btn-success">Add Patient</button>
                </form>
                <?php break; ?>

            <?php case 'doctors': ?>
                <h3>All Doctors</h3>
                <table>
                    <thead><tr><th>ID</th><th>Photo</th><th>Name</th><th>Specialization</th><th>Email</th><th>Fees</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM doctors";
                        $result = $conn->query($sql);
                        while($row = $result->fetch_assoc()) {
                            $image_path = get_image_path($row['doc_image']);
                            echo "<tr>";
                            echo "<td>{$row['doc_id']}</td>";
                            echo "<td><img src='{$image_path}' alt='{$row['doc_name']}'></td>";
                            echo "<td>{$row['doc_name']}</td>";
                            echo "<td>{$row['specialization']}</td>";
                            echo "<td>{$row['email']}</td>";
                            echo "<td>\${$row['consultancy_fees']}</td>";
                            echo "<td>
                                    <a href='edit_doctor.php?doc_id={$row['doc_id']}' class='btn-edit btn'>Edit</a>
                                    <a href='?view=doctors&delete_doctor={$row['doc_id']}' class='btn-danger btn' onclick='return confirm(\"Delete?\")'>Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php break; ?>
            
            <?php case 'add_doctor': ?>
                <h3>Add New Doctor</h3>
                <form method="POST" enctype="multipart/form-data" style="max-width: 500px;">
                    <label>Doctor Name:</label>
                    <input type="text" name="doc_name" required>
                    <label>Specialization:</label>
                    <input type="text" name="specialization" required>
                    <label>Email:</label>
                    <input type="email" name="email" required>
                    <label>Consultancy Fees:</label>
                    <input type="text" name="consultancy_fees" required>
                    <label>Upload Photo (Optional):</label>
                    <input type="file" name="doc_image">
                    <button type="submit" name="add_doctor" class="btn-success">Add Doctor</button>
                </form>
                <?php break; ?>
            
            <?php case 'payments': ?>
                <h3>Payment Log</h3>
                <table>
                    <thead><tr><th>Pay ID</th><th>App. ID</th><th>Patient</th><th>Amount</th><th>Status</th><th>Payment Date</th></tr></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, a.patient_id, pt.fullname FROM payments p
                                JOIN appointments a ON p.app_id = a.app_id
                                JOIN patients pt ON a.patient_id = pt.pid
                                WHERE p.payment_status = 'Paid'
                                ORDER BY p.payment_date DESC";
                        $result = $conn->query($sql);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['pay_id']}</td>";
                            echo "<td>{$row['app_id']}</td>";
                            echo "<td>{$row['fullname']} (ID: {$row['patient_id']})</td>";
                            echo "<td>\${$row['amount']}</td>";
                            echo "<td>{$row['payment_status']}</td>";
                            echo "<td>{$row['payment_date']}</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php break; ?>

        <?php endswitch; ?>

    <?php else: // Patient Views ?>

        <?php switch ($view):
            case 'my_appointments': ?>
                <h3>My Appointments</h3>
                <?php if(isset($_GET['payment']) && $_GET['payment'] == 'success') echo "<p style='color:green; font-weight:bold;'>Payment Successful!</p>"; ?>
                <table>
                    <thead><tr><th>App. ID</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT a.*, d.doc_name, d.specialization, p.amount, p.payment_status FROM appointments a
                                JOIN doctors d ON a.doctor_id = d.doc_id
                                JOIN payments p ON a.app_id = p.app_id
                                WHERE a.patient_id = $pid
                                ORDER BY a.app_date DESC";
                        $result = $conn->query($sql);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['app_id']}</td>";
                            echo "<td>{$row['doc_name']}</td>";
                            echo "<td>{$row['specialization']}</td>";
                            echo "<td>{$row['app_date']}</td>";
                            echo "<td>\${$row['amount']}</td>";
                            
                            if ($row['payment_status'] == 'Paid') {
                                echo "<td><span style='color:green; font-weight:bold;'>Paid &#10004;</span></td>";
                                echo "<td>(Paid)</td>";
                            } else {
                                echo "<td>{$row['payment_status']}</td>";
                                echo "<td>
                                        <a href='payment.php?app_id={$row['app_id']}&amount={$row['amount']}' class='btn-success btn'>Pay Now</a>
                                        <a href='?view=my_appointments&delete_app={$row['app_id']}' class='btn-danger btn' onclick='return confirm(\"Delete?\")'>Delete</a>
                                      </td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php break; ?>
            
            <?php case 'book_appointment': ?>
                <h3>Book a New Appointment</h3>
                <div class="doctor-card-container">
                    <?php
                    $doc_sql = "SELECT * FROM doctors";
                    $doc_result = $conn->query($doc_sql);
                    while($doc = $doc_result->fetch_assoc()) {
                        $image_path = get_image_path($doc['doc_image']);
                        echo "<div class='doctor-card'>";
                        echo "<img src='{$image_path}' alt='{$doc['doc_name']}'>";
                        echo "<h4>{$doc['doc_name']}</h4>";
                        echo "<p>{$doc['specialization']}</p>";
                        echo "<strong>\${$doc['consultancy_fees']}</strong>";
                        echo "<button onclick=\"selectDoctor({$doc['doc_id']}, '{$doc['doc_name']}')\">Select</button>";
                        echo "</div>";
                    }
                    ?>
                </div>

                <form method="POST" id="booking-form" style="display:none; max-width: 500px;">
                    <h4>Booking with <span id="selected-doc-name" style="color:var(--primary-blue)"></span></h4>
                    <input type="hidden" name="doctor_id" id="selected-doc-id">
                    <label>Select Date:</label>
                    <input type="date" name="app_date" required>
                    <button type="submit" name="book_appointment" class="btn-success">Confirm Booking</button>
                </form>

                <script>
                    function selectDoctor(id, name) {
                        document.getElementById('selected-doc-id').value = id;
                        document.getElementById('selected-doc-name').innerText = name;
                        document.getElementById('booking-form').style.display = 'block';
                        document.getElementById('booking-form').scrollIntoView({ behavior: 'smooth' });
                    }
                </script>
                <?php break; ?>

        <?php endswitch; ?>
    
    <?php endif; ?>

</div>

</body>
</html>