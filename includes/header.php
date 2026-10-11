<?php
/*
Learn: This file contains everything
that goes in <head> tag.
We include it at top of every page.
The $pageTitle variable is set in each
page before including this file.
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0">
    
    <!-- Dynamic page title -->
    <!-- Each page sets $pageTitle before including header -->
    <title><?= isset($pageTitle) 
              ? $pageTitle . ' | InternLink' 
              : 'InternLink' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" 
          href="../assets/images/logo.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" 
          rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" 
          rel="stylesheet">

    <!-- Your Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <!-- Extra CSS if page needs it -->
    <?php if(isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>