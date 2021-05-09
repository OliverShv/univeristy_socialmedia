<!-- Navbar -->
<nav id="nav" class="navbar navbar-expand-md navbar-dark">
  <a class="navbar-brand" href="profile.php"><img id="logo" src="../img/uow-logo.png"></a>
  <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navb">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Page options and logout-->
  <div class="collapse navbar-collapse" id="navb">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <a class="nav-link link-style" style="color:white" href="profile.php">Profile</a>
      </li>
      <li class="nav-item">
        <a class="nav-link link-style" style="color:white" href="privatechat.php">Chats</a>
      </li>
      <li class="nav-item">
        <a class="nav-link link-style" style="color:white" href="settings.php">Settings</a>
      </li>
      <li class="nav-item">
        <a class="nav-link link-style" style="color:white" href="../php/signout.php">Sign out</a>
      </li>
    </ul>

    <!--  User search -->
        <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
            <input id="search-input" name="studentname" placeholder="Student's name">
            <button id="search-button"><img style="width: 25px" src="../img/search_icon.png"></button>
        </form>

  </div>
</nav>