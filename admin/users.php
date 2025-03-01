
<?php
session_start();
require_once("../config.php");
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}


function validateUsername($username) {
  
    if (strlen($username) < 4) {
        return "Username must be at least 4 characters long";
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return "Username can only contain letters, numbers, and underscores";
    }
    
    return true;
}


function validatePassword($password) {
    
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }
    
    return true;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    
    $username_validation = validateUsername($username);
    if ($username_validation !== true) {
        $error = $username_validation;
    }
   
    else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $error = "Username already exists";
        }
        $stmt->close();
    }
    
    
    if (!isset($error)) {
        $password_validation = validatePassword($password);
        if ($password_validation !== true) {
            $error = $password_validation;
        }
        elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        }
    }
    
   
    if (!isset($error)) {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        
        
        if ($stmt->execute()) {
            $success = "New admin user created successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $password = $_POST['password']; 
    
    
    $username_validation = validateUsername($username);
    if ($username_validation !== true) {
        $error = $username_validation;
    }
    
    else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM admins WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $error = "Username already exists";
        }
        $stmt->close();
    }
    
   
    if (!isset($error)) {
        
        if (!empty($password)) {
            $password_validation = validatePassword($password);
            if ($password_validation !== true) {
                $error = $password_validation;
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
            }
        } else {
           
            $stmt = $conn->prepare("UPDATE admins SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $username, $user_id);
        }
        
        if (!isset($error)) {
            
            if ($stmt->execute()) {
                $success = "Admin user updated successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    
    if ($_SESSION['admin_id'] == $user_id) {
        $error = "You cannot delete your own account";
    } else {
      
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = "Admin user deleted successfully!";
        } else {
            $error = "Error deleting user: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $user_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT id, username, created_at FROM admins WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $edit_user = $result->fetch_assoc();
    }
    
    $stmt->close();
}


$users = [];
$sql = "SELECT id, username, created_at FROM admins ORDER BY username ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

require_once("header.php");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Admin User Management</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo $edit_user ? 'Edit Admin User' : 'Add New Admin User'; ?></h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="post" id="userForm">
                                <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
                                
                                <?php if ($edit_user): ?>
                                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required 
                                           value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>">
                                    <small class="form-text text-muted">Username must be at least 4 characters and can only contain letters, numbers, and underscores.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password"><?php echo $edit_user ? 'New Password (leave blank to keep current)' : 'Password'; ?></label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           <?php echo $edit_user ? '' : 'required'; ?>>
                                    <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                                </div>
                                
                                <?php if (!$edit_user): ?>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($edit_user): ?>
                                <div class="form-group">
                                    <label>Created At</label>
                                    <p class="form-control-static"><?php echo date('F j, Y, g:i a', strtotime($edit_user['created_at'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
                                    </button>
                                    
                                    <?php if ($edit_user): ?>
                                        <a href="users.php" class="btn btn-secondary">Cancel</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Admin Users</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                    <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                                        <span class="badge badge-primary">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="users.php?edit=<?php echo $user['id']; ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                            <button type="button" class="btn btn-sm btn-danger delete-user" 
                                                               data-id="<?php echo $user['id']; ?>" 
                                                               data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <div class="empty-state">
                                                        <i class="fas fa-users fa-3x text-muted"></i>
                                                        <p class="mt-3">No admin users found</p>
                                                        <small class="text-muted">Create your first admin user using the form on the left</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                </div>
                <p class="text-center">Are you sure you want to delete the user "<strong id="usernameToDelete"></strong>"?</p>
                <p class="text-center text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i> Delete
                </a>
            </div>
        </div>
    </div>
</div>

<style>
        .content-wrapper {
      
        padding: 20px;
        transition: margin-left 0.3s;
        background-color: #f4f6f9;
        min-height: calc(100vh - 60px);
    }
    
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.5rem;
        border: none;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: rgba(0,0,0,.03);
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 0.75rem 1.25rem;
        position: relative;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    
    .card-title {
        font-weight: 500;
        margin-bottom: 0;
        color: #212529;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-control {
        display: block;
        width: 100%;
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .form-control-static {
        padding-top: calc(0.375rem + 1px);
        padding-bottom: calc(0.375rem + 1px);
        margin-bottom: 0;
        line-height: 1.5;
    }
    
    .btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .btn-primary {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .btn-primary:hover {
        color: #fff;
        background-color: #0069d9;
        border-color: #0062cc;
    }
    
    .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        color: #fff;
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    .btn-info {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    
    .btn-info:hover {
        color: #fff;
        background-color: #138496;
        border-color: #117a8b;
    }
    
    .btn-danger {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-danger:hover {
        color: #fff;
        background-color: #c82333;
        border-color: #bd2130;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    .btn-group {
        position: relative;
        display: inline-flex;
        vertical-align: middle;
    }
    
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
    }
    
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .table th, .table td {
        padding: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }
    
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6;
    }
    
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        background-color: #f8f9fa;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    
    .badge-primary {
        color: #fff;
        background-color: #007bff;
    }
    
    .alert {
        position: relative;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
    }
    
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    
    .alert-dismissible {
        padding-right: 4rem;
    }
    
    .alert-dismissible .close {
        position: absolute;
        top: 0;
        right: 0;
        padding: 0.75rem 1.25rem;
        color: inherit;
    }
    
    .close {
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
    }
    
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        color: #6c757d;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }
    
    .modal-dialog {
        position: relative;
        width: auto;
        margin: 1.75rem auto;
        max-width: 500px;
    }
    
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
    }
    
    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 0.3rem;
        outline: 0;
    }
    
    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
    }
    
    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1rem;
    }
    
    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 1rem;
        border-top: 1px solid #dee2e6;
        border-bottom-right-radius: 0.3rem;
        border-bottom-left-radius: 0.3rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        .content-wrapper {
            margin-left: 0;
        }
        
        .row {
            flex-direction: column;
        }
        
        .col-md-5, .col-md-7 {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }
        
        .col-md-5 {
            margin-bottom: 20px;
        }
        
        .btn-group {
            display: flex;
            flex-direction: column;
        }
        
        .btn-group .btn {
            margin-bottom: 5px;
        }
    }
    
    
    .alert {
        animation: fadeInDown 0.5s ease-out;
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#usersTable').DataTable({
                "responsive": true,
                "autoWidth": false,
                "order": [[0, "desc"]]
            });
        }
        
        
        const deleteButtons = document.querySelectorAll('.delete-user');
        const deleteModal = document.getElementById('deleteModal');
        const usernameToDelete = document.getElementById('usernameToDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                
                usernameToDelete.textContent = username;
                confirmDelete.href = 'users.php?delete=' + userId;
                
                $('#deleteModal').modal('show');
            });
        });
        
        
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
   
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirm_password');
        const userForm = document.getElementById('userForm');
        
        if (userForm && confirmPasswordField) {
            userForm.addEventListener('submit', function(e) {
                if (passwordField.value !== confirmPasswordField.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        }
    });
</script>

<?php require_once("footer.php"); ?>