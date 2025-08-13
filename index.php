<?php
session_start();
include 'db.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Logged-in user: show only their posts
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $heading = "📌 My Posts";
} else {
    // Guest view: show all posts
    $sql = "SELECT * FROM posts ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $heading = "📌 All Posts (Guest View)";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .post-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
            border-top-left-radius: .5rem;
            border-top-right-radius: .5rem;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-md navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Post Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
      <ul class="navbar-nav ms-auto mb-2 mb-md-0">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a href="add.php" class="nav-link"><i class="bi bi-plus-circle"></i> Add Post</a>
            </li>
            <li class="nav-item">
              <a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item">
              <a href="login.php" class="nav-link"><i class="bi bi-box-arrow-in-right"></i> Login</a>
            </li>
            <li class="nav-item">
              <a href="signup.php" class="nav-link"><i class="bi bi-person-plus"></i> Sign Up</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><?= $heading ?></h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="add.php" class="btn btn-primary">➕ Add New Post</a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <?php if (!empty($row['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Post Image" class="post-img">
                        <?php else: ?>
                            <img src="uploads/default.jpg" alt="Default Image" class="post-img">
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo nl2br(htmlspecialchars(substr($row['content'], 0, 120))); ?>...
                            </p>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="card-footer bg-white border-0 d-flex justify-content-between">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">✏ Edit</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this post?');">
                                   🗑 Delete
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="text-muted small px-3 pb-2">
                            Posted on: <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">No posts found.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
