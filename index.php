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

  ini_set('post_max_size', '0');
  ini_set('upload_max_filesize', '0');
  ini_set('max_execution_time', '0');

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $originalFileName = $file['name'];
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    $fileName = str_replace(' ', '-', $_POST['filename']) . '.' . $fileExtension; // Convert spaces to dashes and append the original file extension

    // Check for errors during file upload
    if ($file['error'] === UPLOAD_ERR_OK) {
      $uploadPath = 'uploads/' . $fileName; // Use the modified file name for the upload path

      // Move the uploaded file to the uploads directory with the new file name
      if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // File uploaded successfully
        echo '<p>File uploaded successfully.</p>';

        // Send file details to Discord webhook
        $discordWebhookURL = 'https://discord.com/api/webhooks/1107841371747909642/cH6Vs0ynBt9V1IDdGmd8ijhHJGqgoYGVSc8BzT3DwTl4JK5txLMWfh7-SlCkQ1a9deNi';

        $discordMessage = 'New file uploaded: ' . $fileName . "\n\n" . 'Download link: [Download](https://vanceperrymadethis.mrcoolblox.repl.co/download.php?file=' . $fileName . ')' . "\n" . 'View it here: [view](https://vanceperrymadethis.mrcoolblox.repl.co)';

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
        // Failed to move uploaded file
        echo '<p>Failed to move uploaded file.</p>';
      }
    } else {
      // Error during file upload
      echo '<p>Error during file upload.</p>';
    }
  }
  ?>

  <form id="upload-form" action="index.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" id="file-input" />
    <label for="filename-input">File Name:</label>
    <input type="text" name="filename" id="filename-input" />
    <input type="submit" value="Upload" id="upload-button" />
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
      echo '<li>' . $fileName;

      // Display preview for supported file types
      if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo '<br><img src="' . $file . '" width="300" height="auto" />';
      } elseif (in_array($fileExtension, ['mp4', 'webm', 'ogg', 'mov'])) {
        echo '<br><video src="' . $file . '" width="400" height="300" controls></video>';
      } elseif (in_array($fileExtension, ['pdf'])) {
        echo '<br><embed src="' . $file . '" width="500" height="700" type="application/pdf">';
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
      var filename = filenameInput.value.trim(); // Get the value of the filename input

      if (filename === "") {
        alert("Please enter a file name.");
        return;
      }

      var fileExtension = file.name.split('.').pop();
      var sanitizedFilename = filename.replace(/\s+/g, '-');

      var formData = new FormData();
      formData.append('file', file);
      formData.append('filename', sanitizedFilename); // Include the desired file name in the form data

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'index.php', true);

      // Track upload progress
      xhr.upload.addEventListener('progress', function(event) {
        if (event.lengthComputable) {
          var progressPercent = (event.loaded / event.total) * 100;
          var progressBar = document.getElementById('progress');
          progressBar.style.width = progressPercent + '%';
        }
      });

      // Handle upload completion
      xhr.onload = function() {
        if (xhr.status === 200) {
          // File uploaded successfully
          console.log('File uploaded successfully.');
          window.location.reload(); // Refresh the page after upload
        } else {
          // Failed to upload file
          console.log('Failed to upload file.');
        }
      };

      xhr.send(formData);
    });

    // Initiate file download
    <?php
    if (isset($_GET['file'])) {
      $fileName = basename($_GET['file']);
      $filePath = 'uploads/' . $fileName;

      if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        readfile($filePath);
        exit;
      } else {
        echo 'File not found.';
      }
    }
    ?>
  </script>
</body>
</html>
