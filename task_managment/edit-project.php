<?php
session_start();
include 'DB_connection.php';
include 'app/Model/User.php';

// Check for project ID
if (!isset($_GET['id'])) {
    header("Location: projects.php");
    exit();
}

$id = $_GET['id'];

// Get project data
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    echo "Project not found.";
    exit();
}

// Get total hours from tasks
$hours_stmt = $conn->prepare("SELECT COALESCE(SUM(hours), 0) FROM tasks WHERE project_id = ?");
$hours_stmt->execute([$id]);
$calculated_total_hours = $hours_stmt->fetchColumn(); // for info display

// Use stored total_hours from project table (so it can be edited)
$editable_total_hours = isset($project['total_hours']) ? $project['total_hours'] : $calculated_total_hours;

// Get all users for engineer selection
$users = get_all_users($conn);

// Pre-selected engineer IDs (as array)
$selected_engineers = explode(',', $project['engineer_ids']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Project</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .edit-form {
            max-width: 650px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="edit-form">
    <h3 class="mb-4">✏️ Edit Project</h3>

    <!-- Show total project hours (calculated from tasks) -->
    <div class="alert alert-info">
        <strong>Calculated Total Hours from Tasks:</strong> <?= htmlspecialchars($calculated_total_hours) ?> hours
    </div>

    <form method="POST" action="update-project.php">
        <!-- Hidden original ID -->
        <input type="hidden" name="old_id" value="<?= $project['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Project ID</label>
            <input type="number" name="new_id" class="form-control" value="<?= $project['id'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Project Name</label>
            <input type="text" name="project_name" class="form-control" value="<?= htmlspecialchars($project['project_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($project['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= $project['start_date'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= $project['end_date'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Stakeholder Name</label>
            <input type="text" name="stakeholder" class="form-control" value="<?= htmlspecialchars($project['stakeholder_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Project Engineers (select up to 2)</label>
            <select name="engineer[]" class="form-control" multiple required>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= in_array($user['id'], $selected_engineers) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Total Hours (editable)</label>
            <input type="number" name="total_hours" class="form-control" value="<?= htmlspecialchars($editable_total_hours) ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Update Project</button>
        <a href="projects.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
