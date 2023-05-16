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