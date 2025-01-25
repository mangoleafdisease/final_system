<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo "Unauthorized access. Only admins can view this page.";
    header("Location: ../home.php");
    exit();
}

include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $itemsPerPage = 10;

    // Get current pages for each role
    $currentPageAdmins = isset($_GET['page_admins']) ? (int)$_GET['page_admins'] : 1;
    $currentPageCashiers = isset($_GET['page_cashiers']) ? (int)$_GET['page_cashiers'] : 1;
    $currentPageCustomers = isset($_GET['page_customers']) ? (int)$_GET['page_customers'] : 1;
    $currentPageUsers = isset($_GET['page_users']) ? (int)$_GET['page_users'] : 1;

    $offsetAdmins = ($currentPageAdmins - 1) * $itemsPerPage;
    $offsetCashiers = ($currentPageCashiers - 1) * $itemsPerPage;
    $offsetCustomers = ($currentPageCustomers - 1) * $itemsPerPage;
    $offsetUsers = ($currentPageUsers - 1) * $itemsPerPage;

    // Total counts for each role
    $totalAdminsStmt = $pdo->prepare("SELECT COUNT(*) FROM admins");
    $totalAdminsStmt->execute();
    $totalAdmins = $totalAdminsStmt->fetchColumn();
    $totalAdminPages = ceil($totalAdmins / $itemsPerPage);

    $totalCashiersStmt = $pdo->prepare("SELECT COUNT(*) FROM cashier");
    $totalCashiersStmt->execute();
    $totalCashiers = $totalCashiersStmt->fetchColumn();
    $totalCashierPages = ceil($totalCashiers / $itemsPerPage);

    $totalCustomersStmt = $pdo->prepare("SELECT COUNT(*) FROM customers");
    $totalCustomersStmt->execute();
    $totalCustomers = $totalCustomersStmt->fetchColumn();
    $totalCustomerPages = ceil($totalCustomers / $itemsPerPage);

    $totalUsersStmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $totalUsersStmt->execute();
    $totalUsers = $totalUsersStmt->fetchColumn();
    $totalUserPages = ceil($totalUsers / $itemsPerPage);

    // Fetch data for each role
    $adminsStmt = $pdo->prepare("SELECT 'Admin' AS role, username, password FROM admins LIMIT :offset, :itemsPerPage");
    $adminsStmt->bindParam(':offset', $offsetAdmins, PDO::PARAM_INT);
    $adminsStmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $adminsStmt->execute();
    $admins = $adminsStmt->fetchAll(PDO::FETCH_ASSOC);

    $cashiersStmt = $pdo->prepare("SELECT 'Cashier' AS role, username, password FROM cashier LIMIT :offset, :itemsPerPage");
    $cashiersStmt->bindParam(':offset', $offsetCashiers, PDO::PARAM_INT);
    $cashiersStmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $cashiersStmt->execute();
    $cashiers = $cashiersStmt->fetchAll(PDO::FETCH_ASSOC);

    $customersStmt = $pdo->prepare("SELECT 'Customer' AS role, username, password FROM customers LIMIT :offset, :itemsPerPage");
    $customersStmt->bindParam(':offset', $offsetCustomers, PDO::PARAM_INT);
    $customersStmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $customersStmt->execute();
    $customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

    $usersStmt = $pdo->prepare("SELECT 'User' AS role, username, password FROM users LIMIT :offset, :itemsPerPage");
    $usersStmt->bindParam(':offset', $offsetUsers, PDO::PARAM_INT);
    $usersStmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $usersStmt->execute();
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
    <link rel="stylesheet" href="css/totalstocks.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solid #007BFF;
            color: #007BFF;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a.active {
            background-color: #007BFF;
            color: white;
        }

        .pagination a:hover {
            background-color: #0056b3;
            color: white;
        }
    .modal {
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 40%;
        border-radius: 8px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
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
        margin: 0;
    }

    .btn {
        padding: 10px 20px;
        background-color:rgb(3, 119, 3);
        color: white;
        border: none;
        cursor: pointer;
        margin-left : 85%;
    }

    .btn:hover {
        background-color: #0056b3;
    }
</style>

</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>

<div class="container">
    <div class="toplabel">
        <h1>All Users</h1>
        <p>| Admin,Customer,Cashier</p>
    </div>

  
<button id="addAccountBtn" class="btn btn-primary">
    <i class="fas fa-user-plus"></i> Add Account
</button>

    <!-- Admins -->
    <h2>Admins</h2>
    <?php if (empty($admins)): ?>
        <p>No admins found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Username</th>
                    <th>Password (Hashed)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['role']); ?></td>
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                        <td><?php echo htmlspecialchars($admin['password']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

       
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalAdminPages; $i++): ?>
                <a href="?page_admins=<?php echo $i; ?>" class="<?php echo ($i === $currentPageAdmins) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>



    <!-- Cashiers -->
    <h2>Cashiers</h2>
    <?php if (empty($cashiers)): ?>
        <p>No cashiers found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Username</th>
                    <th>Password (Hashed)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cashiers as $cashier): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cashier['role']); ?></td>
                        <td><?php echo htmlspecialchars($cashier['username']); ?></td>
                        <td><?php echo htmlspecialchars($cashier['password']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination for Cashiers -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalCashierPages; $i++): ?>
                <a href="?page_cashiers=<?php echo $i; ?>" class="<?php echo ($i === $currentPageCashiers) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>



    <!-- Customers -->
    <h2>Customers</h2>
    <?php if (empty($customers)): ?>
        <p>No customers found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Username</th>
                    <th>Password (Hashed)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['role']); ?></td>
                        <td><?php echo htmlspecialchars($customer['username']); ?></td>
                        <td><?php echo htmlspecialchars($customer['password']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

       
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalCustomerPages; $i++): ?>
                <a href="?page_customers=<?php echo $i; ?>" class="<?php echo ($i === $currentPageCustomers) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>


  
        <!-- Add Account Modal -->
        <div id="addAccountModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New Account</h2>
                <form id="addAccountForm" method="POST" action="add_account.php">
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Customer">Customer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Account</button>
                </form>
            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const modal = document.getElementById("addAccountModal");
        const btn = document.getElementById("addAccountBtn");
        const span = document.querySelector(".close");

        btn.onclick = function () {
            modal.style.display = "block";
        };

        span.onclick = function () {
            modal.style.display = "none";
        };

        window.onclick = function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>

    <script>
        document.getElementById('addAccountForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('add_account.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                    }).then(() => {
                        location.reload(); 
                    });
                } else {
            
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message,
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An unexpected error occurred. Please try again.',
                });
                console.error('Error:', error);
            });
        });
    </script>

    </body>
    </html>
