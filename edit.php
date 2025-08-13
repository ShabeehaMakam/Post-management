<?php
include 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
if (!$post) {
    header('Location: index.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';

    $imageName = $post['image']; // keep old image by default

    // Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.';
        } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading the image.';
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $imageName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Delete old image if exists
                if (!empty($post['image']) && file_exists($uploadDir . $post['image'])) {
                    unlink($uploadDir . $post['image']);
                }
            } else {
                $errors[] = 'Failed to move uploaded image.';
            }
        }
    }

    if (empty($errors)) {
        $u = $conn->prepare('UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?');
        $u->bind_param('sssi', $title, $content, $imageName, $id);
        if ($u->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Update failed: ' . $conn->error;
        }
    }
} else {
    $title = $post['title'];
    $content = $post['content'];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Edit Post</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .form-card {
      max-width: 700px;
      margin: auto;
      box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 0.075);
      background: white;
      padding: 2rem;
      border-radius: 0.375rem;
    }
    .current-image {
      max-width: 200px;
      display: block;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Post Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
      <ul class="navbar-nav ms-auto mb-2 mb-md-0">
        <li class="nav-item">
          <a href="index.php" class="nav-link"><i class="bi bi-list"></i> Posts</a>
        </li>
        <li class="nav-item">
          <a href="add.php" class="nav-link"><i class="bi bi-plus-circle"></i> Add Post</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <div class="form-card">
    <h1 class="h4 mb-4">Edit Post</h1>

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
        <label class="form-label">Current Image</label><br>
        <?php if (!empty($post['image']) && file_exists('uploads/' . $post['image'])): ?>
          <img src="uploads/<?= htmlspecialchars($post['image']) ?>" class="current-image" alt="Current Post Image">
        <?php else: ?>
          <p class="text-muted">No image uploaded.</p>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Change Image</label>
        <input type="file" name="image" class="form-control" accept="image/*" />
      </div>
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
      <a href="index.php" class="btn btn-secondary ms-2"><i class="bi bi-arrow-left"></i> Cancel</a>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
