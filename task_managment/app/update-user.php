<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

    // التحقق من وجود المدخلات المطلوبة
    if (isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['full_name']) && isset($_POST['id']) && $_SESSION['role'] == 'admin') {
        include "../DB_connection.php";

        // دالة لتنظيف المدخلات
        function validate_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        // الحصول على المدخلات من النموذج
        $user_name = validate_input($_POST['user_name']);
        $password = validate_input($_POST['password']);
        $full_name = validate_input($_POST['full_name']);
        $id = validate_input($_POST['id']);  // الحصول على الـ ID

        // التحقق من صحة المدخلات
        if (empty($user_name)) {
            $em = "User name is required";
            header("Location: ../edit-user.php?error=$em&id=$id");
            exit();
        } else if (empty($password)) {
            $em = "Password is required";
            header("Location: ../edit-user.php?error=$em&id=$id");
            exit();
        } else if (empty($full_name)) {
            $em = "Full name is required";
            header("Location: ../edit-user.php?error=$em&id=$id");
            exit();
        } else if (empty($id)) {  // التحقق من وجود الرقم التعريفي
            $em = "ID is required";
            header("Location: ../edit-user.php?error=$em&id=$id");
            exit();
        } else {
            // شيفرة كلمة المرور
            $password = password_hash($password, PASSWORD_DEFAULT);

            // استدعاء دالة التحديث
            include "Model/User.php";
            $data = array($full_name, $user_name, $password, "employee", $id, "employee");
            update_user($conn, $data);

            // إعادة التوجيه مع رسالة نجاح
            $em = "User updated successfully";
            header("Location: ../edit-user.php?success=$em&id=$id");
            exit();
        }

    } else {
        // إذا لم تكن المدخلات صحيحة
        $em = "Unknown error occurred";
        header("Location: ../edit-user.php?error=$em");
        exit();
    }

} else { 
    // إذا لم يكن المستخدم قد قام بتسجيل الدخول
    $em = "First login";
    header("Location: ../edit-user.php?error=$em");
    exit();
}
?>
