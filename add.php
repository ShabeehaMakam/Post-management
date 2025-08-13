<?php
session_start();
include 'db.php';

// Redirect guests to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$title = $content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';

    // Handle image upload
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.';
        } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading the image.';
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $errors[] = 'Failed to move uploaded image.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO posts (title, content, image, user_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $title, $content, $imageName, $_SESSION['user_id']);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Add Post</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body { background-color: #f8f9fa; }
    .form-card {
      max-width: 700px;
      margin: auto;
      box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 0.075);
      background: white;
      padding: 2rem;
      border-radius: 0.375rem;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Post Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExampleDefault">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
      <ul class="navbar-nav ms-auto mb-2 mb-md-0">
        <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-list"></i> Posts</a></li>
        <li class="nav-item"><a href="add.php" class="nav-link active"><i class="bi bi-plus-circle"></i> Add Post</a></li>
        <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <div class="form-card">
    <h1 class="h4 mb-4">Add New Post</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required />
      </div>
      <div class="mb-3">
        <label class="form-label">Content</label>
        <textarea name="content" rows="6" class="form-control" required><?= htmlspecialchars($content) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Post Image</label>
        <input type="file" name="image" class="form-control" accept="image/*" />
      </div>
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
      <a href="index.php" class="btn btn-secondary ms-2"><i class="bi bi-arrow-left"></i> Cancel</a>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
