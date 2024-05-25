<?php
session_start();
if (!isset($_SESSION['isAuthenticated']) || !$_SESSION['isAuthenticated']) {
    header("Location: /feride/login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="project.css">
</head>

<body>
    <header>
    <nav class="navbar navbar-expand-sm bg-primary navbar-primary">
            <div class="container-fluid justify-content-center">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="/feride/">Autopark</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white" href="/feride/adminka.php">Админ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/feride/drivers.php">Водители</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/feride/routes.php">Маршруты</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/feride/complaints.php">Жалобы</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div id="complaints" class="container mt-5 d-flex gap-5 flex-wrap justify-content-center">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "autopark";

           
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

        
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $sql = "SELECT * FROM complaints";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="complaint ">';
                        echo '<h2 class="mt-2">' . $row['name'] . '</h2>';
                        echo '<h6>Номер тел: ' . $row['phone_number'] . '</h6>';
                        echo '<p>Описание: ' . $row['description'] . '</p>';
                        echo '<div>';
                        echo '<button class="btn btn-light mt-2" onclick="showDeleteComplaintModal(' . $row['complaint_id'] . ')">Удалить</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "Нет жалоб";
                }
            }

          
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                $complaintId = $_POST['id'];
                $sql = "DELETE FROM complaints WHERE complaint_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $complaintId);

                if ($stmt->execute()) {
                    echo "Жалоба удалена";
                } else {
                    echo "Error: " . $stmt->error;
                }

                $stmt->close();
            }

            $conn->close();
            ?>
        </div>
    </main>

    <div class="modal fade" id="delete_complaint-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Удалить жалобу</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Вы уверены что хотите удалить жалобу?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger px-md-4" onclick="deleteComplaint()">Удалить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var globalComplaintId;

        function showDeleteComplaintModal(complaintId) {
            globalComplaintId = complaintId;
            $('#delete_complaint-modal').modal('show');
        }

        function deleteComplaint() {
            $.ajax({
                url: '',
                type: 'POST',
                data: { 'id': globalComplaintId },
                success: function(response) {
                    alert('Жалоба удалена');
                    window.location.reload();
                }
            });
        }
    </script>
</body>

</html>