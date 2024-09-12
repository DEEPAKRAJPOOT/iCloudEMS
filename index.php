<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV File</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .upload-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .upload-container h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        .btn-upload {
            background-color: #007bff;
            color: white;
            width: 100%;
        }
        .btn-upload:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h3>Upload CSV File</h3>
        <form action="import.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Choose CSV File</label>
                <input class="form-control" type="file" name="file" id="file"  required>
            </div>
            <input type="submit" name="upload" class="btn btn-upload" value="Upload">
        </form>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
