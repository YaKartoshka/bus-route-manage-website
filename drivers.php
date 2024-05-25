<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autopark";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['isAuthenticated']) || !$_SESSION['isAuthenticated']) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_driver') {
        $first_name = mysqli_real_escape_string($conn, $_POST["first_name"]); // Sanitize $first_name
        $last_name = mysqli_real_escape_string($conn, $_POST["last_name"]); // Sanitize $last_name
        $age = intval($_POST["age"]); // Ensure $age is an integer
        $license_number = mysqli_real_escape_string($conn, $_POST["license_number"]); // Sanitize $license_number

        $sql = "INSERT INTO Drivers (first_name, last_name, age, license_number) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssi", $first_name, $last_name, $age, $license_number);
            if ($stmt->execute()) {
                echo "Водитель успешно добавлен";
            } else {
                echo "Ошибка при добавлении водителя: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Ошибка при подготовке запроса: " . $conn->error;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update_driver') {
        $id = intval($_POST["id"]); 
        $first_name = mysqli_real_escape_string($conn, $_POST["first_name"]); 
        $last_name = mysqli_real_escape_string($conn, $_POST["last_name"]); 
        $age = intval($_POST["age"]); 
        $license_number = mysqli_real_escape_string($conn, $_POST["license_number"]); 
    
        $sql = "UPDATE Drivers SET first_name=?, last_name=?, age=?, license_number=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssisi", $first_name, $last_name, $age, $license_number, $id);
            if ($stmt->execute()) {
                echo "Данные водителя успешно обновлены";
            } else {
                echo "Ошибка при обновлении данных водителя: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Ошибка при подготовке запроса: " . $conn->error;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_driver') {
        $id = $_POST["id"];

        $sql = "DELETE FROM drivers WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Водитель удален";
        } else {
            echo "Error deleting driver: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'get_driver') {
        $id = $_POST["id"];
        $sql = "SELECT * FROM drivers WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                
                $driver = $result->fetch_assoc();
                echo json_encode($driver);  
            } else {
                echo json_encode(array('error' => 'Driver not found'));
            }
        } else {
            echo "Error fetching driver: " . $stmt->error;
        }

        $stmt->close();
    }
}

$sql = "SELECT * FROM drivers";
$result = $conn->query($sql);
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
                    <li class="nav-item">
                        <button class="btn btn-light" onclick="showAddDriverModal()">Добавить Водителя</button>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
   
    <main>
        <div id="drivers" class="container mt-5 d-flex pb-5 flex-wrap gap-5">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="driver rounded" id="driver-' . $row['id'] . '">';
                    echo '<h3 class="mt-2">' . $row['first_name'] . ' ' . $row['last_name'] . '</h3>';
                    echo '<h5 class="mt-2">Возраст: ' . $row['age'] . '</h5>';
                    echo '<p class="mt-2">Номер лицензии: ' . $row['license_number'] . '</p>';
                    echo '<div>';
                    echo '<button class="btn btn-light mt-2" onclick="showEditDriverModal(' . $row['id'] . ')">Изменить</button>';
                    echo '<br>';
                    echo '<button class="btn btn-danger mt-2" onclick="showDeleteDriverModal(' . $row['id'] . ')">Удалить</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "No drivers found.";
            }
            ?>
        </div>
    </main>

    <div class="modal fade" id="add_driver-modal" tabindex="-1" aria-labelledby="add_driver-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="add_driver-modalLabel">Добавить водитель</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add_driver_form" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">ФИО</label>
                            <input type="text" class="form-control" id="new_full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Специальность</label>
                            <input type="text" class="form-control" id="new_specialization" name="specialization" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success px-md-4" form="add_driver_form" name="add_driver" onclick="addDriver()">Добавить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_driver-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Изменить водительа</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit_driver_form">
                        <div class="mb-3">
                            <label for="name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Возраст</label>
                            <input type="text" class="form-control" id="age" name="age" required>
                        </div>  
                        <div class="mb-3">
                            <label for="name" class="form-label">Лицензия</label>
                            <input type="text" class="form-control" id="license_number" name="license_number" required>
                        </div> 
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success px-md-4" onclick="editDriver()">Обновить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="delete_driver-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Удалить водитель</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="delete_driver_form">
                <div class="modal-body">
                    <input type="hidden" id="delete_id" name="id"> 
                    <p>Вы действительно хотите удалить водитель?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger px-md-4" onclick="deleteDriver()">Удалить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var globalDriverId;
        function showAddDriverModal() {
            $('#add_driver-modal').modal('show');
        }

        function showEditDriverModal(driverId) {
            globalDriverId = driverId;
            var formData = new FormData();
            formData.append('action', 'get_driver');
            formData.append('id', globalDriverId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'drivers.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {

                    var data = JSON.parse(xhr.responseText.split('}')[0]+"}");
                    document.getElementById('first_name').value = data.first_name;
                    document.getElementById('last_name').value = data.last_name;
                    document.getElementById('age').value = data.age;
                    document.getElementById('license_number').value = data.license_number;
           
                    $('#edit_driver-modal').modal('show');
                } else {
                 
                }
            };
            xhr.onerror = function() {
                
            };
            xhr.send(formData);
               
                   
        }

        function showDeleteDriverModal(driverId) {
            globalDriverId = driverId;
            $('#delete_id').val(driverId);
            $('#delete_driver-modal').modal('show');
        }

        function addDriver() {
            var form = document.getElementById('add_driver_form');
          
            if (!validateForm(form)) {
                alert('Заполните все поля.');
                return; 
            }
            
            var formData = new FormData(form);
            formData.append('action', 'add_driver');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'drivers.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Водитель добавлен');
                    window.location.reload();
                } else {
                    console.error('Error adding driver:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error adding driver:', xhr.statusText);
            };
            xhr.send(formData);
        }

        function validateForm(form) {
            var inputs = form.querySelectorAll('input');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].required && !isValidInput(inputs[i])) {
                    return false; 
                }
            }
            return true;
        }

        function isValidInput(input) {
         
            if (input.type === 'file') {
              
                return input.files.length > 0;
            } else {
             
                return input.value.trim() !== '';
            }
        }

        function editDriver() {
            var form = document.getElementById('edit_driver_form');

            if (!validateForm(form)) {
                alert('Заполните все поля и файл.');
                return; 
            }

            var formData = new FormData(form);
            formData.append('action', 'update_driver');
            formData.append('id', globalDriverId);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'drivers.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Водитель редактирован');
                    window.location.reload();
                } else {
                    console.error( xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error( xhr.statusText);
            };
            xhr.send(formData);
        }

        function deleteDriver() {
            var form = document.getElementById('delete_driver_form');
            var formData = new FormData(form);
            formData.append('action', 'delete_driver');
            formData.append('id', globalDriverId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'drivers.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Водитель удален');
                    window.location.reload();
                } else {
                    console.error('Error deleting driver:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error deleting driver:', xhr.statusText);
            };
            xhr.send(formData);
        }


    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>