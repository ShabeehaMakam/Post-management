# Post Management System (PHP + MySQL + Bootstrap)

## What's included
- `posts.sql` — SQL file to create the database and `posts` table.
- `db.php` — Update with your MySQL credentials.
- `index.php` — List posts + AJAX delete.
- `add.php` — Add new post.
- `edit.php` — Edit existing post.
- `README.md` — This file.

## Setup
1. Copy the folder into your webserver root (e.g., XAMPP: `htdocs/post-management`).
2. Import `posts.sql` into MySQL (phpMyAdmin or CLI).
3. Edit `db.php` and set your DB credentials.
4. Visit `http://localhost/post-management` in your browser.

## Git
Initialize a repo and push to GitHub:
```
git init
git add .
git commit -m "Initial - Post Management System"
```

## Notes
- Delete is done via AJAX to avoid page reloads.
- Prepared statements are used to avoid SQL injection for create/update/delete.
