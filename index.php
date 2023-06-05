<!DOCTYPE html>
<html>
<head>
  <title>File Sharing Website</title>
  <style>
    #progress-bar {
      width: 100%;
      background-color: #f1f1f1;
      border: 1px solid #ccc;
      height: 20px;
      position: relative;
    }

    #progress {
      width: 0;
      height: 100%;
      background-color: #4caf50;
      position: absolute;
    }

    #preview {
      margin-top: 20px;
    }

    .download-button {
      margin-left: 10px;
    }
  </style>
</head>
<body>
  <h1>File Sharing Website</h1>

  <?php
  require_once 'config.php';

  // Function to log user information
  function logUpload($ip, $device, $fileName)
  {
    $logEntry = "IP: " . $ip . " | Device: " . $device . " | File: " . $fileName . "\n";
    file_put_contents(UPLOAD_LOGS, $logEntry, FILE_APPEND);
  }

  // Function to block certain IP addresses
  function blockIP($ip)
  {
    $blockedIPs = ['']; // Add IP addresses to block here

    if (in_array($ip, $blockedIPs)) {
      echo '<p>Uploads from your IP address are blocked.</p>';
      exit;
    }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $originalFileName = $file['name'];
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    
    // Check if the file extension is in the list of programming languages
    $programmingLanguages = ['html', 'js', 'php', 'py', 'java', 'cpp', 'xml']; // Add more if needed
    if (in_array($fileExtension, $programmingLanguages)) {
      $fileExtension = 'txt'; // Change the extension to .txt
    }
    
    $fileName = str_replace(' ', '-', $_POST['filename']) . '.' . $fileExtension;

    if ($file['error'] === UPLOAD_ERR_OK) {

      $uploadPath = 'uploads/' . $fileName;

      if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo '<p>File uploaded successfully.</p>';

        // Log user information
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        logUpload($ip, $userAgent, $fileName);

        $discordWebhookURL = DISCORD_WEBHOOK_URL;
        $discordMessage = 'New file uploaded: ' . $fileName . "\n\n" . 'Download link: [Download](https://vanceperrymadethis.mrcoolblox.repl.co/download.php?file=' . $fileName . ')' . "\n" . 'View it here (only works for some files): [View](https://vanceperrymadethis.mrcoolblox.repl.co/uploads/' . $fileName . ')';

        $payload = json_encode([
          'content' => $discordMessage
        ]);

        $headers = [
          'Content-Type: application/json'
        ];

        $ch = curl_init($discordWebhookURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        exit;
      } else {
        echo '<p>Failed to move uploaded file.</p>';
      }
    } else {
      echo '<p>Error during file upload.</p>';
    }
  }

  // Check and block certain IP addresses
  $ip = $_SERVER['REMOTE_ADDR'];
  blockIP($ip);
  ?>

  <form id="upload-form" action="index.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" id="file-input" required />
    <label for="filename-input">File Name:</label>
    <input type="text" name="filename" id="filename-input" required />
    <button type="submit" id="upload-button">Upload</button>
  </form>

  <div id="progress-bar">
    <div id="progress"></div>
  </div>
  <hr>
  <h2>Shared Files</h2>
  <ul>
    <?php
    $uploadedFiles = glob('uploads/*');
    foreach ($uploadedFiles as $file) {
      $fileName = basename($file);
      $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
      echo '<li>' . htmlspecialchars($fileName);

      if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo '<br><img src="' . htmlspecialchars($file) . '" width="300" height="auto" />';
      } elseif (in_array($fileExtension, ['mp4', 'webm', 'ogg', 'mov'])) {
        echo '<br><video src="' . htmlspecialchars($file) . '" width="400" height="300" controls></video>';
      } elseif (in_array($fileExtension, ['pdf'])) {
        echo '<br><embed src="' . htmlspecialchars($file) . '" width="500" height="700" type="application/pdf">';
      } elseif (in_array($fileExtension, ['mp3', 'wav', 'ogg', 'm4a'])) {
        echo '<br><audio src="' . htmlspecialchars($file) . '" controls></audio>';
      }

      echo '<a href="download.php?file=' . urlencode($fileName) . '" class="download-button">Download</a></li>';
    }
    ?>
  </ul>
  <script>
    document.getElementById('upload-form').addEventListener('submit', function(e) {
      e.preventDefault();

      var fileInput = document.getElementById('file-input');
      var file = fileInput.files[0];
      var filenameInput = document.getElementById('filename-input');
      var filename = filenameInput.value.trim();

      if (filename === "") {
        alert("Please enter a file name.");
        return;
      }

      var fileExtension = file.name.split('.').pop();
      var sanitizedFilename = filename.replace(/\s+/g, '-');

      var formData = new FormData();
      formData.append('file', file);
      formData.append('filename', sanitizedFilename);

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'index.php', true);

      xhr.upload.addEventListener('progress', function(event) {
        if (event.lengthComputable) {
          var progressPercent = (event.loaded / event.total) * 100;
          var progressBar = document.getElementById('progress');
          progressBar.style.width = progressPercent + '%';
        }
      });

      xhr.onload = function() {
        if (xhr.status === 200) {
          console.log('File uploaded successfully.');
          window.location.reload();
        } else {
          console.log('Failed to upload file.');
        }
      };

      xhr.send(formData);
    });
  </script>
</body>
</html>
