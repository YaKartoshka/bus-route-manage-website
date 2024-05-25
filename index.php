<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autopark";

$conn = new mysqli($servername, $username, $password, $dbname);


$sql = "SELECT d.first_name, d.last_name, r.name AS route_name, JSON_OBJECT('stations', JSON_EXTRACT(r.description, '$.stations')) AS route_description
        FROM Schedule s
        JOIN Drivers d ON s.driver_id = d.id
        JOIN Routes r ON s.route_id = r.id";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание и планирование маршрутов в автопарке</title>
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
                        <a class="nav-link text-white" href="/feride/">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/feride/complaint.php">Оставить жалобу</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div class="container intro_block  p-5 mt-5" style="text-align: center;">
                <div class="text">
                    <h1>Расписание и планирование <br> маршрутов в автопарке</h1>
                    <br>
                    <p>Наша платформа предлагает инновационное решение для управления вашим автопарком.
                        Мы предлагаем полный набор инструментов для составления расписания
                        и оптимизации маршрутов, чтобы обеспечить эффективное использование ресурсов и экономию времени.</p>
                        <br>
                    <a href="#routes"><button type="button" class="btn btn-light    ">Смотреть маршруты</button></a>
                </div>
        
            </div>
            <h2 align="center" class="mt-5 mb-3">Маршруты</h2>
        <div id="routes" class="container mt-5 d-flex gap-5 flex-wrap pb-5">
            <?php 
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Decode the JSON string to an array
                        $route_description = json_decode($row['route_description'], true);
                        // Extract the stations array and join them with commas
                        $stations = implode(', ', $route_description['stations']);
                ?>
                        <div class="route rounded text-white">
                            <h2 class="mt-2"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h2>
                            <h4>Маршрут: <?php echo $row['route_name']; ?></h4>
                            <p>Остановки: <?php echo $stations; ?></p>
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
    <footer class="p-4 bg-primary text-white">
        <h3>Феридэ</h3>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>