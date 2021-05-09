<!--  Message option template for groups-->
<div id="<?php echo $group_id[$x] ?>" admin="<?php echo $admin_id[$x] ?>" member="group" class="message" contextmenu='<?php echo 'menu'.$group_id[$x] ?>' onclick="<?php echo 'pm_messages(\''.$group_id[$x] .'\')' ?>">

    <div class="row">
        <div class="col-12">
            <p>
            <span style="font-size: 16px"><?php echo $group_name[$x] ?></span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <p>
                <span style="font-size: 12px" in-chat=<?php echo $group_inchat[$x] ?> id="<?php echo 'inChat'.$group_id[$x] ?>"><?php echo "Active users: ".$group_inchat[$x] ?></span>
            </p>
        </div>
        <div class="col-6">
            <p style='text-align:right'>
                <span style="font-size:12px;" id="<?php echo 'timeSinceMessage'.$group_id[$x] ?>"><?php if($msg_available[$x]==1){echo $displayedTime[$x];} ?></span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <p id="<?php echo 'lastMessage'.$group_id[$x] ?>">
            <?php 
                if($msg_available[$x]==1){
                    echo $group_message[$x];
                }
            ?>
            </p>
        </div>
    </div>
    <!-- Creates a option to open the chat in a new tab for support broswers when right clicked -->
    <menu type="context" id="<?php echo 'menu'.$group_id[$x] ?>">
        <menuitem label="Open in new tab" onclick="window.open(<?php echo '\'groupchat.php?group='.$group_id[$x].'\''  ?>);" icon="ico_reload.png"></menuitem>
    </menu>

</div>
<!--  message end -->
