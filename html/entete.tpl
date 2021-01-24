<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="/" class="nav-link">Tableau de bord</a>
      </li>
      {*<li class="nav-item d-none d-sm-inline-block">
        <a href="/support" class="nav-link">Contacter le support</a>
      </li>*}
    </ul>

    <ul class="navbar-nav ml-auto">


      <li class="nav-item d-sm-inline-block mr-2">
        <a class="btn btn-navbar bg-success nav-link bg-cp-green" href="{$smarty.env.APP_HOST}" role="button">
          <i class="fas fa-mobile-alt"></i><span class="d-none d-sm-inline"> &nbsp; Retourner à l'application</span>
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block mr-2">
        <a class="btn btn-navbar bg-success nav-link bg-cp-red" href="/ballot" role="button">
          <i class="fas fa-plus-square"></i><span> &nbsp; Nouvelle consultation</span>
        </a>
      </li>
      <li class="nav-item d-sm-inline-block">
        <a class="bg-cp-black btn btn-navbar bg-danger nav-link" href="/logout" role="button">
          <i class="fas fa-power-off"></i>
        </a>
      </li>


      <li class="nav-item d-sm-inline-block">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>

{include file="menu-left.tpl"}

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1  class="m-0 html_title">{$HTML_TITLE}</h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">