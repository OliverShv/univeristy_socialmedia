
<!-- Template is used to display each result from a search-->
<a style="color:black" href="profile.php?user=<?php echo $user[$x]["user_id"] ?>">
  <div class="search-result mt-1">
    <div class="row">

      <div class="col-3">
        <div class="profile-pic" style="background-image: url('../img/profilepics/<?php echo $user[$x]["picture"] ?>');"></div>
      </div>

      <div class="col pt-4">
        <p style="font-size: 20px;"><?php echo ucfirst($user[$x]["fname"]) . " " . ucfirst($user[$x]["lname"]) ?><br>
        <?php

        if($user[$x]["course"]!=null){
          echo '<span style="font-size: 16px;">'. $user[$x]["course"] . ', ' . $user[$x]["cyear"].$user[$x]["indicator"].' year </span></p>';
        }else{
          echo '<span style="font-size: 16px;">User hasn\'t completed course setup</span></p>';
        }

        ?>
      </div>

    </div>
  </div>
</a>      