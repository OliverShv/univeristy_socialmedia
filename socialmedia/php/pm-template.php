<!--  Message option template for personal chats-->
<div id="<?php echo 'user'.$talkedto_ordered[$x] ?>" class="message" member='user' contextmenu='<?php echo 'menu'.$talkedto_ordered[$x] ?>' onclick="<?php echo 'pm_messages('.$talkedto_ordered[$x] .')' ?>">

    <div class="row">
        <div class="col-3">
            <div class="profile-img" style="background-image: url('../img/profilepics/<?php echo $pm_picture[$x] ?>');"></div>
        </div>
        <div class="col-9">
            <p>
            <span style="font-size: 16px"><?php echo ucfirst($pm_fname[$x]) . " " . ucfirst($pm_lname[$x]) ?></span>
            <span id="<?php echo 'onlineStatus'.$talkedto_ordered[$x] ?>" class="<?php echo $onlineStatus[$x]; ?>"></span><br> 
            <span style="font-size: 12px"><?php echo $pm_course[$x]; if(($pm_cyear[$x]!=null&&$pm_cyear[$x]!= 0)||$pm_course[$x]!=null){ echo ", ";}; if($pm_cyear[$x]!=null&&$pm_cyear[$x]!= 0){ echo $pm_cyear[$x].$pm_indicator[$x];}; ?></span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <p id="<?php echo 'lastMessage'.$talkedto_ordered[$x] ?>">
            <?php 

            if($totalunseen[$x]!=0){echo "<b>";}

            if($msg_available[$x]==1){
                echo $pm_message[$x];
            }
            
            if($totalunseen[$x]!=0){echo "</b>";}

            ?>
            </p>
        </div>
    </div>

    <div class="row">
            <div class="col-6">
                <p total-messages="<?php echo $totalunseen[$x] ?>" style="font-size:12px" id="<?php echo 'unseenMessages'.$talkedto_ordered[$x] ?>"> 
                <?php 
                if($totalunseen[$x]==1){
                    echo "1 new message";
                }else if($totalunseen[$x]>1){
                    echo $totalunseen[$x]." new messages";
                }
                ?>
                </p>
            </div>
            <div class="col-6">
                <p style="font-size:12px;text-align:right" id="<?php echo 'timeSinceMessage'.$talkedto_ordered[$x] ?>"><?php echo $displayedTime[$x] ?></p>
            </div>
    </div>
    <!-- Creates a option to open the chat in a new tab for support broswers when right clicked -->            
    <menu type="context" id="<?php echo 'menu'.$talkedto_ordered[$x] ?>">
    <menuitem label="Open in new tab" onclick="window.open(<?php echo '\'privatechat.php?pm='.$talkedto_ordered[$x].'\''  ?>);" icon="ico_reload.png"></menuitem>
    </menu>

</div>
<!--  message end -->
