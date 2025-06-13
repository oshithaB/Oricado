<?php
require_once '../config/config.php';
checkAuth(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];
                $name = $_POST['name'];
                $contact = $_POST['contact'];

                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, name, contact) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $email, $password, $role, $name, $contact);
                
                if (!$stmt->execute()) {
                    $error = "Error adding user: " . $conn->error;
                }
                break;

            case 'delete':
                $id = $_POST['user_id'];
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;

            case 'edit':
                $id = $_POST['user_id'];
                $username = $_POST['username'];
                $email = $_POST['email'];
                $role = $_POST['role'];
                $name = $_POST['name'];
                $contact = $_POST['contact'];

                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, name = ?, contact = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $email, $role, $name, $contact, $id);
                
                if (!$stmt->execute()) {
                    $error = "Error updating user: " . $conn->error;
                }
                break;
        }
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role, name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>User Management</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <h3>Add New User</h3>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="add-user-form">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="supervisor">Supervisor</option>
                        <option value="office_staff">Office Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Contact:</label>
                    <input type="text" name="contact">
                </div>
                <button type="submit">Add User</button>
            </form>

            <h3>Existing Users</h3>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['contact']); ?></td>
                        <td>
                            <button type="button" class="btn-edit" onclick="editUser(<?php 
                                echo htmlspecialchars(json_encode([
                                    'id' => $user['id'],
                                    'username' => $user['username'],
                                    'email' => $user['email'],
                                    'role' => $user['role'],
                                    'name' => $user['name'],
                                    'contact' => $user['contact']
                                ])); 
                            ?>)">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit User</h3>
            <form method="POST" id="editUserForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" id="edit_role" required>
                        <option value="supervisor">Supervisor</option>
                        <option value="office_staff">Office Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Contact:</label>
                    <input type="text" name="contact" id="edit_contact">
                </div>
                <button type="submit" class="btn-submit">Update User</button>
            </form>
        </div>
    </div>

    <style>
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .btn-edit, .btn-delete {
            padding: 5px 10px;
            margin: 0 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #28a745;
            color: white;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
    </style>

    <script>
    const modal = document.getElementById("editModal");
    const span = document.getElementsByClassName("close")[0];

    function editUser(userData) {
        document.getElementById('edit_user_id').value = userData.id;
        document.getElementById('edit_username').value = userData.username;
        document.getElementById('edit_email').value = userData.email;
        document.getElementById('edit_role').value = userData.role;
        document.getElementById('edit_name').value = userData.name;
        document.getElementById('edit_contact').value = userData.contact;
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>
</html>
