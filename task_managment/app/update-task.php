<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] === 'admin') {

    if (
        isset($_POST['id'], $_POST['title'], $_POST['description'], 
              $_POST['assigned_to'], $_POST['deadline'], $_POST['hours'])
    ) {
        include "../DB_connection.php";

        function validate_input($data) {
            return htmlspecialchars(stripslashes(trim($data)));
        }

        $id = validate_input($_POST['id']);
        $title = validate_input($_POST['title']);
        $description = validate_input($_POST['description']);
        $assigned_to = validate_input($_POST['assigned_to']);
        $deadline = validate_input($_POST['deadline']);
        $hours = validate_input($_POST['hours']);

        // Input validation
        if (empty($title)) {
            $em = "Title is required";
        } elseif (empty($description)) {
            $em = "Description is required";
        } elseif ($assigned_to == 0) {
            $em = "Select User";
        } elseif (!is_numeric($hours) || $hours < 0) {
            $em = "Estimated hours must be a positive number";
        }

        if (isset($em)) {
            header("Location: ../edit-task.php?error=$em&id=$id");
            exit();
        }

        include "Model/Task.php";
        $data = array($title, $description, $assigned_to, $deadline, $hours, $id);
        update_task($conn, $data);

        $sm = "Task updated successfully";
        header("Location: ../edit-task.php?success=$sm&id=$id");
        exit();

    } else {
        $em = "Missing required fields";
        header("Location: ../edit-task.php?error=$em");
        exit();
    }

} else {
    $em = "First login";
    header("Location: ../login.php?error=$em");
    exit();
}
?>
