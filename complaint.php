<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autopark";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone_number = $_POST["phone_number"];
    $description = $_POST["description"];
    $sql_complaint = "INSERT INTO complaints (name, phone_number, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_complaint);
    $stmt->bind_param("sss", $name, $phone_number, $description);


    if ($stmt->execute()) {
        echo "Жалоба добавлена";
    } else {
        echo "Error: " . $sql_complaint . "<br>" . $conn->error;
    }

 
    $stmt->close();
    $conn->close();
}
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
        <div class="container mt-5 w-50">
            <h2 align="center">Оставить жалобу</h2>
            <br>
            <form id="add_complaint_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Имя</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Номер телефона</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Жалоба</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>
                <p align="center">
                <button type="submit" class="btn btn-primary">Отправить</button>
                </p>
            </form>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
     

        $(document).ready(function() {
            $('#add_complaint_form').submit(function(event) {
                event.preventDefault(); 

                $.ajax({
                    type: 'POST',
                    url: '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>',
                    data: $(this).serialize(),
                    success: function(response) {
                   
                        alert('Ваш отзыв в обработке');
                        window.location.href = '/feride';
                    },
                    error: function(xhr, status, error) {
                   
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>