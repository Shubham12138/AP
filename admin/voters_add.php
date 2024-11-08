<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
    // Use regex to allow only alphabet characters for firstname and lastname
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    
    if (!preg_match("/^[a-zA-Z]+$/", $firstname) || !preg_match("/^[a-zA-Z]+$/", $lastname)) {
        $_SESSION['error'] = 'First name and Last name should only contain alphabets.';
        header('location: voters.php');
        exit();
    }

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $filename = $_FILES['photo']['name'];
    if (!empty($filename)) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['photo']['size'] < 2000000) { // limit file size to 2MB
            $filepath = '../images/' . $filename;
            move_uploaded_file($_FILES['photo']['tmp_name'], $filepath);
        } else {
            $_SESSION['error'] = 'Invalid file type or size';
            header('location: voters.php');
            exit();
        }
    } else {
        $filename = null; // Handle case if no file is uploaded
    }

    // Generate voters id
    $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $voter = substr(str_shuffle($set), 0, 15);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO voters (voters_id, password, firstname, lastname, photo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $voter, $password, $firstname, $lastname, $filename);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Voter added successfully';
    } else {
        $_SESSION['error'] = $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: voters.php');
?>
