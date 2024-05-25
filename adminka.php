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
    if (isset($_POST['action']) && $_POST['action'] == 'add_schedule') {
        $route_id = $_POST["route_id"];
        $driver_id = $_POST["driver_id"];
        $departure_time = $_POST["departure_time"];

     
        $sql = "INSERT INTO schedule (route_id, driver_id, departure_time) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $route_id, $driver_id, $departure_time);
        if ($stmt->execute()) {
            echo "Schedule added  ";
        } else {
            echo "Error adding schedule: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update_schedule') {
        $schedule_id = $_POST["id"];
        $route_id = $_POST["new_route_id"];
        $driver_id = $_POST["new_driver_id"];
        $departure_time = $_POST["new_departure_time"];
        $sql = "UPDATE schedule SET route_id = ?, driver_id = ?, departure_time = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $route_id, $driver_id, $departure_time, $schedule_id);
        if ($stmt->execute()) {
            echo "Schedule updated  ";
        } else {
            echo "Error updating schedule: " . $stmt->error;
        }
        $stmt->close();
        } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_schedule') {
            $id = $_POST["id"];

            $sql = "DELETE FROM schedule WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo "Schedule deleted  ";
            } else {
                echo "Error deleting schedule: " . $stmt->error;
            }

            $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'get_schedule') {
        $id = $_POST["id"];
        $sql = "SELECT * FROM schedule WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $schedule = $result->fetch_assoc();
            echo json_encode($schedule);
        } else {
            echo json_encode(array('error' => 'Schedule not found'));
        }
    
        $stmt->close();
    }
}

$sql = "SELECT s.id, d.first_name, d.last_name, r.name AS route_name, JSON_OBJECT('stations', JSON_EXTRACT(r.description, '$.stations')) AS route_description
        FROM Schedule s
        JOIN Drivers d ON s.driver_id = d.id
        JOIN Routes r ON s.route_id = r.id";
$result = $conn->query($sql);

$sql1 = "SELECT * from drivers";
$drivers = $conn->query($sql1);

$sql2 = "SELECT * FROM routes";
$routes = $conn->query($sql2);

$sql3 = "SELECT * from drivers";
$edit_drivers = $conn->query($sql3);

$sql4 = "SELECT * FROM routes";
$edit_routes = $conn->query($sql4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adminka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
 
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
                        <button class="btn btn-light" onclick="showAddScheduleModal()">Добавить расписание</button>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div id="schedules" class="container mt-5 d-flex pb-5 flex-wrap gap-5">
            <?php 
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Decode the JSON string to an array
                        $route_description = json_decode($row['route_description'], true);
                        // Extract the stations array and join them with commas
                        $stations = implode(', ', $route_description['stations']);
                ?>
                        <div class="schedule rounded text-white">
                            <h2 class="mt-2"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h2>
                            <h4>Маршрут: <?php echo $row['route_name']; ?></h4>
                            <p>Остановки: <?php echo $stations; ?></p>
                            <button class="btn btn-light mt-2" onclick="showEditScheduleModal('<?php echo $row['id'];?>')">Изменить</button>
                      
                            <button class="btn btn-danger mt-2" onclick="showDeleteScheduleModal('<?php echo $row['id'];?>')">Удалить</button>
                        </div>
                <?php 
                    } 
                } else { 
                ?>
                    <p>Нет маршрутов.</p>
                <?php 
                } 
                ?>
        </div>
    </main>
    
    <div class="modal fade" id="add_schedule-modal" tabindex="-1" aria-labelledby="add_schedule-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="add_schedule-modalLabel">Добавить расписание</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add_schedule_form" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Маршрут</label>
                            <select class="form-control" id="route_id" name="route_id" required>
                                <?php
                                    if ($routes->num_rows > 0) {
                                        while ($row = $routes->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">';
                                            echo '' . $row['name'] . '  ';
                                            echo '</option>';
                                            
                                        }
                                    } 
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Водитель</label>
                            <select class="form-control" id="driver_id" name="driver_id" required>
                                <?php
                                    if ($drivers->num_rows > 0) {
                                        while ($row = $drivers->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">';
                                            echo '' . $row['first_name'] . ' ' . $row['last_name'] . '';
                                            echo '</option>';
                                            
                                        }
                                    } 
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Время отправления</label>
                            <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success px-md-4" form="add_schedule_form" name="add_schedule" onclick="addSchedule()">Добавить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_schedule-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Редактировать расписание</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <form id="edit_schedule_form" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Маршрут</label>
                            <select class="form-control" id="new_route_id" name="new_route_id" required>
                            <?php
                                if ($edit_routes->num_rows > 0) {
                                    while ($row = $edit_routes->fetch_assoc()) {
                                        echo '<option value="' . $row['id'] . '">';
                                        echo '' . $row['name'] . '  ';
                                        echo '</option>';
                                    }
                                } 
                            ?>
                                                     
                        </select>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Водитель</label>
                            <select class="form-control" id="new_driver_id" name="new_driver_id" required>
                            <?php
                                    if ($edit_drivers->num_rows > 0) {
                                        while ($row = $edit_drivers->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">';
                                            echo '' . $row['first_name'] . ' ' . $row['last_name'] . '';
                                            echo '</option>';
                                            
                                        }
                                    } 
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Время отправления</label>
                            <input type="time" class="form-control" id="new_departure_time" name="new_departure_time" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success px-md-4" onclick="editSchedule()">Обновить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="delete_schedule-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Удалить расписание</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="delete_schedule_form">
                    <div class="modal-body">
                        <input type="hidden" id="delete_schedule_id" name="id"> <!-- Hidden input to store schedule ID -->
                        <p>Вы действительно хотите удалить расписание?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger px-md-4" onclick="deleteSchedule()">Удалить</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var globalScheduleId;

        function showAddScheduleModal() {
            $('#add_schedule-modal').modal('show');
        }

        function showEditScheduleModal(scheduleId) {
            globalScheduleId = scheduleId;
            var formData = new FormData();
            formData.append('action', 'get_schedule');
            formData.append('id', globalScheduleId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'adminka.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                  
                    var data = JSON.parse(xhr.responseText.split('}')[0]+"}");
                    document.getElementById('new_driver_id').value = data.driver_id;
                    document.getElementById('new_route_id').value = data.route_id;
                    document.getElementById('new_departure_time').value = data.departure_time;

                    $('#edit_schedule-modal').modal('show');
                } else {
                 
                }
            };
            xhr.onerror = function() {
                
            };
            xhr.send(formData);
               
                   
        }

        function showDeleteScheduleModal(scheduleId) {
            globalScheduleId = scheduleId;
            $('#delete_schedule_id').val(scheduleId);
            $('#delete_schedule-modal').modal('show');
        }

        function addSchedule() {
            var form = document.getElementById('add_schedule_form');
          
            if (!validateFormInputs(form)) {
                alert('Заполните все поля.');
                return; 
            }
            
            var formData = new FormData(form);
            formData.append('action', 'add_schedule');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'adminka.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Расписание добавлен');
                    window.location.reload();
                } else {
                    console.error('Error adding schedule:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error adding schedule:', xhr.statusText);
            };
            xhr.send(formData);
        }

        function validateFormInputs(form) {
            var inputs = form.querySelectorAll('input, select, textarea');
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

        function editSchedule() {
            var form = document.getElementById('edit_schedule_form');

            if (!validateFormInputs(form)) {
                alert('Заполните все поля и файл.');
                return; 
            }

            var formData = new FormData(form);
            formData.append('action', 'update_schedule');
            formData.append('id', globalScheduleId);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'adminka.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Расписание обновлен');
                    window.location.reload();
                } else {
                    console.error('Error updating schedule:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error updating schedule:', xhr.statusText);
            };
            xhr.send(formData);
        }

        function deleteSchedule() {
            var form = document.getElementById('delete_schedule_form');
            var formData = new FormData(form);
            formData.append('action', 'delete_schedule');
            formData.append('id', globalScheduleId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'adminka.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Расписание удален');
                    window.location.reload();
                } else {
                    console.error('Error deleting schedule:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error deleting schedule:', xhr.statusText);
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