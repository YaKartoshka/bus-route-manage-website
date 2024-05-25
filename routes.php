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
    if (isset($_POST['action']) && $_POST['action'] == 'add_route') {
        $name = $_POST["name"];
        $description = $_POST["description"];
        
        $sql = "INSERT INTO routes (name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $description);

        if ($stmt->execute()) {
            echo "Route added successfully";
        } else {
            echo "Error adding route: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_route') {
        $id = $_POST["id"];

        $sql = "DELETE FROM routes WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "route deleted successfully";
        } else {
            echo "Error deleting route: " . $stmt->error;
        }

        $stmt->close();
    }
}

$sql = "SELECT id, name AS route_name, JSON_OBJECT('stations', JSON_EXTRACT(description, '$.stations')) AS route_description
        FROM Routes";
        
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
                        <button class="btn btn-light" onclick="addroute()">Добавить Маршрут</button>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
   
        <div id="routes" class="container mt-5 d-flex pb-5 flex-wrap gap-5">
            <?php 
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Decode the JSON string to an array
                        $route_description = json_decode($row['route_description'], true);
                        // Extract the stations array and join them with commas
                        $stations = implode(', ', $route_description['stations']);
                ?>
                        <div class="admin-route rounded text-white">
                            <h2>Маршрут: <?php echo $row['route_name']; ?></h2>
                            <h5>Остановки: <?php echo $stations; ?></h5>
                            <button class="btn btn-light mt-2" onclick="showDeleteRouteModal(' <?php echo $row['id']; ?>')">Удалить</button>
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



    <div class="modal fade" id="delete_route-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Удалить маршрут</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="delete_route_form">
                    <div class="modal-body">
                        <input type="hidden" id="delete_route_id" name="id">
                        <p>Вы действительно хотите удалить маршрут?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger px-md-4" onclick="deleteroute()">Удалить</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var globalrouteId;

        function showDeleteRouteModal(routeId) {
            globalrouteId = routeId;
            $('#delete_route_id').val(routeId);
            $('#delete_route-modal').modal('show');
        }

        function addroute() {
            var form = document.getElementById('add_route_form');
            var name = prompt('Введите номер маршрута')
            if(!name) return alert('Поле не может быть пустым');
            var stations = []
            var count_stations = prompt('Введите количество остановок')
            for(let i = 1; i <= count_stations; i++){
                stations.push(prompt('Введите название остановки ' + i))
            }
            
            if(!count_stations) return alert('Поле не может быть пустым');
            
            var formData = new FormData();
            formData.append('action', 'add_route');
            formData.append('name', name);
            formData.append('description', `{"stations": ${JSON.stringify(stations)}}`);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'routes.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Маршрут добавлен');
                    window.location.reload();
                } else {
                    console.error('Error adding route:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error adding route:', xhr.statusText);
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

     

        function deleteroute() {
            var form = document.getElementById('delete_route_form');
            var formData = new FormData(form);
            formData.append('action', 'delete_route');
            formData.append('id', globalrouteId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'routes.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Маршрут удален');
                    window.location.reload();
                } else {
                    console.error('Error deleting route:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error deleting route:', xhr.statusText);
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