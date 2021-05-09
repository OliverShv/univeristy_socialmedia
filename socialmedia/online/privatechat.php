<?php require("../php/conn.php") ?>
<?php require("../php/verifylogin.php") ?>
<?php require("../php/collectpersonalchat.php") ?>
<?php include("../php/header.php") ?>
<link rel="stylesheet" type="text/css" href="../css/navbar.css">
<link rel="stylesheet" type="text/css" href="../css/chat.css">

    <title>Chat</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="../js/chat.js"></script>

  </head>
  <!-- Gives the user a random background -->
  <body class ='background_img' style='background-image: url("../img/<?php echo "chatroom-bg-".rand(1,5).".jpg" ?>")'>

  <?php include("../php/navbar.php") ?>

    <!-- Page content -->
    <div class="d-flex justify-content-center">
        <div id="container">
            <div class="row" id="chatNavbar">
                <!-- Option to switch between different chat types -->
                <div class="col redirect_button_off" onclick='window.open("privatechat.php","_self");'>
                    <p>Personal</p>
                </div>
                <div class="col redirect_button" onclick='window.open("groupchat.php","_self");'>
                    <p>Group</p>
                </div>
                <div class="col redirect_button" onclick='window.open("coursechat.php","_self");'>
                    <p>Course</p>
                </div>
            </div>
            <div class="row" id="flexRow">
                <div id="contentwrapper" class="col">
                    <!-- Search for new user to message -->
                    <div class="row" id='newMessageButtonWrapper' onclick='window.open("search.php","_self");'>
                        <p>New message</p>
                    </div>
                    <!-- Search all messaged users for chat -->
                    <div class="row">
                        <input id='searchUser' oninput="searchUser()" placeholder='Search name'>
                    </div>
                    <!-- Show messaged users -->
                    <div class="row" id="users">
                        
                        <?php
                        if(!empty($talkedto_ordered)){
                            for($x=0;$x<sizeof($talkedto_ordered);$x++){
                                include("../php/pm-template.php");
                            }
                        }
                        ?>
        
                    </div>
                </div>
                <!-- Message area -->        
                <div id="msgArea" class="col">
                    <div id="chatroomHeader" class="row" style='height:50px'>
                        <div class="col">
                            <!-- Switch from chatroom to chat options when in mobile view -->
                            <img id="backButton" onclick="toggleChatroom('hide')" src='../img/back_icon.png' style='width:50px;display:none'>
                        </div>
                            <!-- Chat header shows informaiton on person being messaged -->
                        <div class="col-6">
                            <div class="row float-right pr-2">
                                <div id="talkedToPicture"></div>
                                <p class="pl-1">
                                <span id="talkedToName"></span>
                                <span id="talkedToOnline"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="chatroomWrapper">
                    <!-- Chat area-->
                    <p id="chatRoom">
                    <?php 
                    if(!empty($talkedto_ordered)){
                        echo "Select a person to message";
                    }else{
                        echo "Use the new message button to talk to someone"; 
                    }
                    ?>
                    
                    </p>
                    </div>
                    <!-- Input message and send message -->
                    <div class="row" id="inputRow">
                        <input id="newMessage" name='newMessage' placeholder="Enter a message..." disabled>
                        <button id="sendButton" type='button' disabled></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>

//All talked to users
var all_users = <?php if(!empty($talkedto_ordered)){ echo json_encode($talkedto_ordered);}else{ echo "[]"; }; ?>;
//Current user talked to
var receiver_id;
//Colour of user name
var color;
//Total messages shown
var totalmessages = 20;
//Total messages sent
var sentmessages = 0;
//New message audio
var msg_audio = new Audio('../audio/newmessage.mp3');
//Screen swidth
var screen_width = $(window).width();
//Preselected user in URL to talk to
var preselecteduser = <?php if(isset($_GET["pm"])){echo "'".$_GET["pm"]."'";}else{echo "'none'";} ?>;

//Opens chat of user that was preselected
$(window).on('load', function(){
    var preselecteduserinchat = all_users.includes(preselecteduser);
    if(preselecteduserinchat == true){
        pm_messages(preselecteduser);
    }
});

//Change view to desktop mode when over 640px width
$(window).resize(function() {
    screen_width = $(window).width();
    if(screen_width>640){
        $("#msgArea").css("display","initial");
        $("#contentwrapper").css("display","initial");
        $("#backButton").css("display","none");
    }
});

//Used to switch between the chatroom and message previews
function toggleChatroom(type){
    if(type=='show'){
        $("#msgArea").css("display","initial");
        $("#contentwrapper").css("display","none");
        $("#backButton").css("display","initial");
    }else if(type=='hide'){
        $("#msgArea").css("display","none");
        $("#contentwrapper").css("display","initial");
    }
}

//Searches for users
function searchUser(){
    if(all_users.length != 0){
        var searched_name = $("#searchUser").val();
        $.post('../php/searchchat.php',{
            name: searched_name, 
            },function(data){
                var users = all_users.filter(x => data.includes(x));

                $("[member='user']").css("display","none");

                for($x=0;$x<users.length;$x++){
                    $("#user"+users[$x]).css("display","initial");
                }
            }
        );
    }
}

//Used to dislay a preview of a message
function previewMessageDisplay(id,msg,type){
    //Checks if user preview is already displayed or not
    if($('#user' + id).length != 0){
        var lastMessageId = 'lastMessage'+id;
        var lastMessage;
        var totalUnseen = parseInt($('#unseenMessages' + id).attr("total-messages")) || 0;

        if(totalUnseen==0){
            var unseenPlaceholder = " new message";
        }else{
            var unseenPlaceholder = " new messages";
        }

        //Type options: sent, receivedSeen,  receivedNotSeen
        
        if(msg.length>20){
            if(type=="receivedSeen"){
                lastMessage = msg.slice(0, 20)+"...";
            }else if(type=="receivedNotSeen"){
                lastMessage = "<b>"+msg.slice(0, 20)+"...</b>";

                //increases unseen messaeg count
                totalUnseen++;
                $('#unseenMessages' + id).attr("total-messages",totalUnseen);
                $('#unseenMessages' + id).text(totalUnseen + unseenPlaceholder);

            }else if(type=="sent"){
                lastMessage = "You: "+msg.slice(0, 20)+"...";;
            }
        }else{
            if(type=="receivedSeen"){
                lastMessage = msg;
            }else if(type=="receivedNotSeen"){
                lastMessage = "<b>"+msg+"</b>";
                
                //increases unseen messaeg count
                totalUnseen++;
                $('#unseenMessages' + id).attr("total-messages",totalUnseen);
                $('#unseenMessages' + id).text(totalUnseen + unseenPlaceholder);

            }else if(type=="sent"){
                lastMessage = "You: "+msg;
            }
        }

        //Change Last message time to now
        $('#timeSinceMessage' + id).text("now");

        //Replace last message sent
        $('#' + lastMessageId).html(lastMessage);

        //Put message to top
        var content = $('#user' + id).clone()
        $('#user' + id).remove();
        content.prependTo('#users');
    }else{

        //Adds new id to list of user ids this person is talking to
        all_users.push(id);
        
        $.post('../php/new_pm_template.php',{
            id: id,
            msg: msg,
            type: "receiver",
            },function(data){
                    $("#users").prepend(data);
                }
            );
    }
}

//If the user scrolls to the top of the chatroom then load more messages
$('#chatroomWrapper').scroll(function() {
    var pos = $('#chatroomWrapper').scrollTop();
    var scrollHeight =  $("#chatroomWrapper")[0].scrollHeight;
    if (pos == 0) {
        totalmessages +=20;
        $('#chatRoom').load('../php/fetchpersonalchat.php',{
            receiver_id: receiver_id,
            color: color,
            totalmessages: totalmessages
        },function(){
            previousPos = $("#chatroomWrapper")[0].scrollHeight-scrollHeight;
            $("#chatroomWrapper").scrollTop(previousPos);
    })
    }
});

//Open chat when a message preview is clicked
function pm_messages(b){

    if(screen_width<=640){
        toggleChatroom("show");
    }

    $('#chatRoom').empty();
    
    receiver_id = b;

    //Fetch the messages
    $('#chatRoom').load('../php/fetchpersonalchat.php',{
        receiver_id: receiver_id
    },function(){
        $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);
        color = $('#color').html();
    });

        //Changes message header
    $.post("../php/chatroomheaderinfo.php",{
        receiver_id: receiver_id
    },function(data){

        var picture = data.picture;
        var name = data.name;
        var status = data.status;
        $("#talkedToPicture").addClass("profile-img");
        $("#talkedToPicture").css("background-image","url('../img/profilepics/"+picture+"')");
        $("#talkedToName").text(name);
        if(status=="online"){
            $("#talkedToOnline").addClass("online");
            $("#talkedToOnline").removeClass("offline");
        }else{
            $("#talkedToOnline").addClass("offline");
            $("#talkedToOnline").removeClass("online");
        }

    },"json");

    //Remove the message input and send from being disabled
    $('#newMessage').removeAttr("disabled");
    $('#sendButton').removeAttr("disabled");
    $('#inputRow').css("background-color","white");
    $('#sendButton').css("background-color","#C7DDF8");
    
    totalmessages=20;

    $('#lastMessage' + b).text($('#lastMessage' + b).text());

    //Changes unseen messages to 0
    $('#unseenMessages' + b).attr("total-messages",0);
    $('#unseenMessages' + b).text("");
    
    //Change messages to seen
    $.post("../php/seenmessages.php",{
        sender_id: receiver_id, 
        user_id: <?php echo $details[1] ?>
    });
}

//When a connection is opened, the client notifies the server
$(document).ready(function(){
    //Sets URL depending on the host location
    let url = location.host == 'localhost' ?
    'ws://localhost:8080' : location.host == 'ws://192.168.8.100' ?
    'ws://192.168.8.100:8080' :
    'ws://localhost:8080';

    var conn = new WebSocket(url);
    
    //Tell server info about the conencted client
    conn.onopen = function(e) {
        console.log("Connection established!");
        var data = {
            'sender_id' : <?php echo json_encode($details[1]); ?>,
            'session_code': <?php echo json_encode($details[0]); ?>,
            'type' : 'log',
            'chat': 'personal'
        };
        conn.send(JSON.stringify(data));
    };
    
    //Function to deal with messages received
    conn.onmessage = function(e) {
        console.log(e.data);
            var json = JSON.parse(e.data);
            switch(json.type) {
                //When a private message is received
                case 'pm':
                    //Paste the message if the user has the senders chat opened
                    if(receiver_id==json.sender_id){
                        var date = createDate(json.timestamp);
                        var showdate = 'showDate("message'+json.message_id+'")';
                        var hidedate = 'hideDate("message'+json.message_id+'")';
                        var msg = "<span onmouseover='"+showdate+"' onmouseout='"+hidedate+"'><a href='profile.php?user="+json.sender_id+"' style='color:rgb("+color+")'><b>"+json.fname+" "+json.lname+"<a/></b><span id='message"+json.message_id+"' style='display:none;color:rgb("+color+")'> "+date+"</span><br>"+json.msg+"</span><br><br>";
                        
                        previewMessageDisplay(json.sender_id,json.msg,"receivedSeen");
                        
                        $('#chatRoom').append(msg);
                        //If the user is already viewing the messages, they are set to seen
                        $.post("../php/seenmessages.php",{
                            sender_id: receiver_id, 
                            user_id: <?php echo $details[1] ?>
                        });
                    }else{
                        previewMessageDisplay(json.sender_id,json.msg,"receivedNotSeen");
                        msg_audio.play();
                    }
                    break;
                    //Sets a users online and offline status
                case 'user_online_status':
                        if(json.status=="offline"){
                            $("#onlineStatus"+json.user).addClass("offline");
                            $("#onlineStatus"+json.user).removeClass("online");

                            if(json.user==receiver_id){
                                $("#talkedToOnline").addClass("offline");
                                $("#talkedToOnline").removeClass("online");
                            }
                        }else if(json.status=="online"){
                            $("#onlineStatus"+json.user).addClass("online");
                            $("#onlineStatus"+json.user).removeClass("offline");

                            if(json.user==receiver_id){
                                $("#talkedToOnline").addClass("online");
                                $("#talkedToOnline").removeClass("offline");
                            }
                        }
                    break;
                    //Confirm message is sent
                case 'confirm':
                    var sent_to_id = json.receiver_id;
                    previewMessageDisplay(sent_to_id,json.msg,"sent");
                    if(receiver_id==sent_to_id){
                         //Changes the text to black as the message has successfully been sent from this client, else the messaged is load for other clients
                        if(json.from=="current"){
                            $("[msf-for='"+sent_to_id+"']").css("color","black");
                            $("[msf-for='"+sent_to_id+"']").css("opacity","1");

                        //If the message was not sent from this client then paste it
                        }else if(json.from=="other"){
                            
                            var msg = json.msg;
                            var currentTime = Math.round(new Date().getTime()/1000);
                            var date = createDate(currentTime);
                            var showdate = 'showDate("messagesent'+sentmessages+'")';
                            var hidedate = 'hideDate("messagesent'+sentmessages+'")';
                            var msgFormat = "<span msf-for='"+sent_to_id+"' onmouseover='"+showdate+"' onmouseout='"+hidedate+"' style='text-align: right;display: block;'><span id='messagesent"+sentmessages+"' style='display:none;'>"+date+"</span><b> You</b><br>"+msg+"</span><br>";
                            
                            $('#chatRoom').append(msgFormat);
                            $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);

                            previewMessageDisplay(sent_to_id,msg,"sent");
                            sentmessages++;
                            totalmessages++;
                        }
                    }
                    //If the user messages someone new it will appear on all their client screens
                    if($('#user' + sent_to_id).length == 0){
                        //Adds new id to list of user ids this person is talking to
                        all_users.push(sent_to_id);

                        $.post('../php/new_pm_template.php',{
                            id: sent_to_id,
                            type: "sender",
                            msg: json.msg
                        },function(data){
                                $("#users").prepend(data);
                            }
                        );
                    }
                    break;
                    //Error responds
                case 'error':
                    if(json.msg == "invalid_user"){
                        alert("error invalid user");
                    }else if( json.msg == "empty_message"){
                        alert("Message cannot be empty");
                    }
                    break;
                }
        $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);
        totalmessages++;
    };
    
    //Sends message
    function sendMessage(){
        //Get message
        var msg = $("#newMessage").val();

        //If message isn't empty continue
        if(msg.length!=0){

            sentmessages++;

            //Change message into html entity
            $.post("../php/htmlentities_msg.php",{
                msg:msg
            },
            function(data){
                var altered_msg = data; 
                //Preview message sent
                previewMessageDisplay(receiver_id,altered_msg,"sent");
                    
                var currentTime = Math.round(new Date().getTime()/1000);
                var date = createDate(currentTime);
                var showdate = 'showDate("messagesent'+sentmessages+'")';
                var hidedate = 'hideDate("messagesent'+sentmessages+'")';

                var msgFormat = "<span msf-for='"+receiver_id+"' onmouseover='"+showdate+"' onmouseout='"+hidedate+"' style='text-align: right;display: block;color : red;opacity: 0.5;'><span id='messagesent"+sentmessages+"' style='display:none;'>"+date+"</span><b> You</b><br>"+altered_msg+"</span><br>";
                $('#chatRoom').append(msgFormat);

                $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);

                //Send message to other clients
                var data = {
                    'sender_id': <?php echo json_encode($details[1]); ?>,
                    'session_code': <?php echo json_encode($details[0]); ?>,
                    'receiver_id': receiver_id,
                    'chat_msg': msg,
                    'type':'pm',
                    'chat': 'personal'
                };

                totalmessages++;
                $("#newMessage").val('');

                conn.send(JSON.stringify(data));
            })
        }
    }
    
    //Enter is pressed to send message
    $('#newMessage').on('keyup',function(e){
        if(e.keyCode==13 && !e.shiftKey){
            sendMessage();
        }
    })

    //Button is pressed to send message
    $("#sendButton").click(function(){
        sendMessage();
    });
    
});

</script>

<?php include("../php/footer.php") ?>