<?php
if (!isset($page_title)) {
	$page_title = "AS-Stats";
}
if (!isset($meta_refresh)) {
	$meta_refresh = "";
}
if (!isset($body_attrs)) {
	$body_attrs = "";
}
?>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <?php echo $meta_refresh; ?>
  <title><?php echo $page_title; ?></title>
  <link rel="icon" href="favicon.ico" />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/font-awesome/font-awesome.min.css">
  <link rel="stylesheet" href="plugins/ionicons/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="css/custom.css">
</head>
<body class="hold-transition skin-black-light sidebar-collapse layout-top-nav fixed" <?php echo $body_attrs; ?>>

<div class="wrapper">
