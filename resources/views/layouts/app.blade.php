<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý trường học</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #1c2b36;
            color: white;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            padding: 15px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #007bff;
        }
        .topbar {
            width: calc(100% - 250px);
            margin-left: 250px;
            background-color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        footer {
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: calc(100% - 250px);
            margin-left: 250px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h4 class="text-center">Quản lý trường học</h4>
    <a href="#"><i class="fas fa-home"></i> Trang chủ</a>
    <a href="#"><i class="fas fa-user"></i> Giáo viên</a>
    <a href="#"><i class="fas fa-users"></i> Học sinh</a>
    <a href="#"><i class="fas fa-calendar"></i> Lịch học</a>
    <a href="#"><i class="fas fa-cogs"></i> Cài đặt</a>
</div>

<div class="topbar">

</div>

<div class="content">
    <main>
    </main>@yield('content')
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>
