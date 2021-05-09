<!--  Message option template for course / modules-->
<div id="<?php echo $cm_id[$x] ?>" class="message" contextmenu='<?php echo 'menu'.$cm_id[$x] ?>' onclick="<?php echo 'pm_messages(\''.$cm_id[$x] .'\')' ?>">

    <div class="row">
        <div class="col-12">
            <p>
            <span style="font-size: 16px"><?php echo ucfirst($cm_name[$x]) ?></span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <p>
            <span style="font-size: 12px"><?php echo ucfirst($cm_id[$x]) ?></span>
            </p>
        </div>
        <div class="col-6">
            <p>
            <span style="font-size: 12px" in-chat=<?php echo $cm_inchat[$x] ?> id="<?php echo 'inChat'.$cm_id[$x] ?>"><?php echo "Active users: ".$cm_inchat[$x] ?></span>
            </p>
        </div>
    </div>

    <!-- Creates a option to open the chat in a new tab for support broswers when right clicked -->
    <menu type="context" id="<?php echo 'menu'.$cm_id[$x] ?>">
        <menuitem label="Open in new tab" onclick="window.open(<?php echo '\'coursechat.php?cm='.$cm_id[$x].'\''  ?>);" icon="ico_reload.png"></menuitem>
    </menu>

</div>
<!--  message end -->
