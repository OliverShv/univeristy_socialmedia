<?php require("../php/conn.php") ?>
<?php require("../php/verifylogin.php") ?>
<?php require("../php/collectgroupchat.php") ?>
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
                <div class="col redirect_button" onclick='window.open("privatechat.php","_self");'>
                    <p>Personal</p>
                </div>
                <div class="col redirect_button_off" onclick='window.open("groupchat.php","_self");'>
                    <p>Group</p>
                </div>
                <div class="col redirect_button" onclick='window.open("coursechat.php","_self");'>
                    <p>Course</p>
                </div>
            </div>
            <div class="row" id="flexRow">
                <div id="contentwrapper" class="col">
                    
                    <!-- Pop-up box to create new group -->
                    <div id='newgroup' class='promptbox' style='display:none; width: 220px;'>
                        <button onclick='groupBox()' class='promptSmallButton'>X</button>
                        <h2 style='width:100%;text-align:center'>Create group</h2>
                        <input id='newGroupName' oninput="validName()" placeholder='Group name' style='width:100%;'>
                        <div id='createGroupButton' class='redirect_button_off'><p>Create</p></div>
                    </div>
                    
                    <div class="row" id='newMessageButtonWrapper' onclick='groupBox()'>
                        <p>New Group</p>
                    </div>
                    <!-- Search all messaged groups -->
                    <div class="row">
                        <input id='searchUser' oninput="searchUser()" placeholder='Search group name'>
                    </div>
                    <!-- Show messaged groups -->
                    <div class="row" id="users">

                        <?php
                        if(isset($group_id)){
                            for($x=0;$x<count($group_id);$x++){
                                include("../php/group-template.php");
                            }
                        }
                        ?>
                        
                    </div>
                </div>

                <div id="msgArea" class="col" style="width:100%">
                    <div id="chatroomHeader" class="row" style='height:50px'>
                        
                        <!-- Options for a group -->
                        <div id='userGroup' class='promptbox' style='display:none; width: 300px;'>
                            <button onclick='userBox()' class='promptSmallButton'\>X</button>
                                <h2>Controls</h2>
                                <div id='admin_controls' style='display:none'>
                                    <input id='user_id' oninput="validId()" type='number' min="10000000" max="99999999" placeholder="User id">
                                    <button id='addUserButton' class='redirect_button_2_off'>Add user</button>
                                    <button onclick='deleteGroup()' class='redirect_button_2'>Delete group</button>
                                </div>
                                <button onclick='leaveGroup()' class='redirect_button_2'>Leave group</button>
                                <hr>
                            <h2 style='width:100%;text-align:center'>Users</h2>
                            <div id='allUsers' class='container'></div>
                        </div>
                        <!-- Switch from chatroom to chat options when in mobile view -->
                        <div class="col">
                            <img id="backButton" onclick="toggleChatroom('hide')" src='../img/back_icon.png' style='width:50px;display:none'>
                        </div>
                        <!-- Chat header shows informaiton on person being messaged -->
                        <div class="col-6">
                            <div id="header_settings" style="display:none" class="row float-right pr-2">
                                <p style="display:inline" class="pl-1">
                                <span id="talkedToName"></span>
                                </p>
                                <button id='usersAndSettingsButton' onclick="userBox();"><img style='width:30px;' src="../img/settings_icon.png"></button>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="chatroomWrapper">
                    <!-- Chat area-->
                    <p id="chatRoom">
                    <?php 
                    if(!empty($group_id)){
                        echo "Select a group to message";
                    }else{
                        echo "Create a new group and add users to start"; 
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
var all_users = <?php if(!empty($group_id)){ echo json_encode($group_id);}else{ echo "[]"; }; ?>;
//Current user talked to
var receiver_id;
//Total messages shown
var totalmessages = 20;
//Total messages sent
var sentmessages = 0;
//New message audio
var msg_audio = new Audio('../audio/newmessage.mp3');
//Screen swidth
var screen_width = $(window).width();
//Preselected user in URL to talk to
var preselecteduser = <?php if(isset($_GET["group"])){echo "'".$_GET["group"]."'";}else{echo "'none'";} ?>;

//Sets URL depending on the host location
let url = location.host == 'localhost' ?
    'ws://localhost:8080' : location.host == 'ws://192.168.8.100' ?
    'ws://192.168.8.100:8080' :
    'ws://localhost:8080';

var conn = new WebSocket(url);

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

//Unselects a group
function unselectGroup(){
    $("#userGroup").css("display","none");
    $("#admin_controls").css("display","none");
    $("#header_settings").css("display","none");
    $("#user_id").val("");
    $("#chatRoom").empty();

    receiver_id = "";

    $('#newMessage').attr("disabled","disabled");
    $('#sendButton').attr("disabled","disabled");
    $('#inputRow').css("background-color","#e3e3e3");
    $('#sendButton').css("background-color","grey");
}

//searches for groups
function searchUser(){
    if(all_users.length != 0){
        var searched_name = $("#searchUser").val();
        $.post('../php/searchgroup.php',{
            name: searched_name, 
            },function(data){
                var users = all_users.filter(x => data.includes(x));

                $("[member='group']").css("display","none");

                for($x=0;$x<users.length;$x++){
                    $("#"+users[$x]).css("display","initial");
                }
            }
        );
    }
}

//Used to dislay a preview of a message
function previewMessageDisplay(id,msg,type){
    //Checks if user preview is already displayed or not
    if($("#"+id).length != 0){
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

                //increases unseen messages count
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
        var content = $('#' + id).clone()
        $('#' + id).remove();
        content.prependTo('#users');
    }else{

        //Adds new id to list of user ids this person is talking to
        all_users.push(id);
        
        $.post('../php/new_group_template.php',{
            id: id
            },function(data){
                $("#users").prepend(data);
            });
    }
}

//If the user scrolls to the top of the chatroom then load more messages
$('#chatroomWrapper').scroll(function() {
    var pos = $('#chatroomWrapper').scrollTop();
    var scrollHeight =  $("#chatroomWrapper")[0].scrollHeight;
    if (pos == 0) {
        totalmessages +=20;
        $('#chatRoom').load('../php/fetchgroupchat.php',{
            receiver_id: receiver_id,
            totalmessages: totalmessages,
            colors: colors
        },function(){
            previousPos = $("#chatroomWrapper")[0].scrollHeight-scrollHeight;
            $("#chatroomWrapper").scrollTop(previousPos);
    })
    }
});

//Open chat when a message preview is clicked
function pm_messages(b){

    //Notify clients that user has joined the chat
    var data = {
        'sender_id' : <?php echo json_encode($details[1]); ?>,
        'session_code': <?php echo json_encode($details[0]); ?>,
        'type' : 'joinedchat',
        'chat_id': b,
        'chat': 'group'
        };

    conn.send(JSON.stringify(data));

    if(screen_width<=640){
        toggleChatroom("show");
    }

    //Empty chatroom and hide group settings/users
    $('#chatRoom').empty();
    $("#userGroup").css("display","none");

    receiver_id = b;
    
    $('#chatRoom').load('../php/fetchgroupchat.php',{
        receiver_id: receiver_id
    },function(){
        $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);
    });

    //Changes message header
    $.post("../php/groupheaderinfo.php",{
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
    $("#header_settings").css("display","initial");
    $('#newMessage').removeAttr("disabled");
    $('#sendButton').removeAttr("disabled");
    $('#inputRow').css("background-color","white");
    $('#sendButton').css("background-color","#C7DDF8");
    
    totalmessages=20;

    $('#lastMessage' + b).text($('#lastMessage' + b).text());
}

//When a connection is opened, the client notifies the server
conn.onopen = function(e) {
    console.log("Connection established!");
    var data = {
        'sender_id' : <?php echo json_encode($details[1]); ?>,
        'session_code': <?php echo json_encode($details[0]); ?>,
        'type' : 'log',
        'chat': 'group'
    };
    conn.send(JSON.stringify(data));
};

    //Function to deal with messages received
conn.onmessage = function(e) {
    console.log(e.data);
        var json = JSON.parse(e.data);
        console.log(json);
        switch(json.type) {
            //When a grop message is received
            case 'gm':
                //Paste the message if the user has the senders chat opened
                if(receiver_id==json.group){
                    var date = createDate(json.timestamp);
                    var showdate = 'showDate("message'+json.message_id+'")';
                    var hidedate = 'hideDate("message'+json.message_id+'")';

                    //User is assigned color if he doesn't have any
                    if(colors[json.sender_id] === undefined){
                        colors[json.sender_id] =getRndInteger(50,125)+","+getRndInteger(50,125)+","+getRndInteger(50,125);
                    }

                    var msg = "<span onmouseover='"+showdate+"' onmouseout='"+hidedate+"'><a href='profile.php?user="+json.sender_id+"' style='color:rgb("+colors[json.sender_id]+")'><b>"+json.fname+" "+json.lname+"<a/></b><span id='message"+json.message_id+"' style='display:none;color:rgb("+colors[json.sender_id]+")'> "+date+"</span><br>"+json.msg+"</span><br><br>";
                    
                    previewMessageDisplay(json.group,json.msg,"receivedSeen");
                    
                    $('#chatRoom').append(msg);
                }else{
                    previewMessageDisplay(json.group,json.msg,"receivedNotSeen");
                    msg_audio.play();
                }
                break;
                //Sets a users online and offline status
            case 'user_online_status':
                    //In user list
                    if(receiver_id == json.left){
                        $("#onlineStatus"+json.user_id).addClass("offline");
                        $("#onlineStatus"+json.user_id).removeClass("online");
                    }

                    if(receiver_id == json.joined){
                        $("#onlineStatus"+json.user_id).addClass("online");
                        $("#onlineStatus"+json.user_id).removeClass("offline");
                    }

                    //Active users
                    if(json.hasOwnProperty('joined')){
                        $("#inChat"+json.joined).attr("in-chat",json.joined_total)
                        $("#inChat"+json.joined).text("Active users: "+json.joined_total);
                    }

                    if(json.left!='none'){
                        $("#inChat"+json.left).attr("in-chat",json.left_total);
                        $("#inChat"+json.left).text("Active users: "+json.left_total);
                    }
                    
                break;
                //Confirm message is sent
            case 'confirm':
                var sent_to_id = json.group;
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

                        previewMessageDisplay(sent_to_id,msg,"sent")
                        sentmessages++;
                        totalmessages++;
                    }
                }
                //If the user messages someone new it will appear on all their client screens
                if($('#' + sent_to_id).length == 0){
                    //Adds new id to list of user ids this person is talking to
                    all_users.push(sent_to_id);

                    $.post('../php/new_group_template.php',{
                        id: sent_to_id,
                        type: "sender",
                        msg: json.msg
                    },function(data){
                            $("#users").prepend(data);
                        }
                    );
                }
                break;
                //If user was added to a group then show the group preview
            case 'added_to_group':
                
                all_users.push(json.group);
        
                $.post('../php/new_group_template.php',{
                    id: json.group
                    },function(data){
                        $("#users").prepend(data);
                    });

                break;
                //If user left a group then delete group preview
            case 'left_group':

                for(var x = 0;x < all_users.length; x++){

                    if(all_users[x]==json.group){
                        all_users.splice(x, 1);
                    }

                }

                if(receiver_id==json.group){
                    unselectGroup();
                }
        
                $('#' + json.group).remove();

                break;
                //Refresh the users in the group settings
            case 'refresh_users':

                if(json.group==receiver_id){
                    getUsers();
                }

                break;
                //Show admin commands and refresh the users in teh group settigns when made a admin
            case 'new_admin':

                if(json.group==receiver_id){
                    $("#"+json.group).attr("admin",json.admin_id);

                    if($("#"+receiver_id).attr("admin")==<?php echo $details[1]; ?>){
                        $("#admin_controls").css("display","block");
                    }else{
                        $("#admin_controls").css("display","none");
                    }
    
                    getUsers();

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
                'type': 'gm',
                'chat': 'group'
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

//Controls group creating box display
function groupBox(){
    var display = $("#newgroup").css("display");

    if(display=="block"){
        $("#newgroup").css("display","none");
    }else{
        $("#newgroup").css("display","block");
    }
}
//Checks that the group name is valid
function validName(){
    let name = $("#newGroupName").val();
    let pattern = new RegExp("^[A-Za-z0-9 ]{1,20}$");
    let res = pattern.test(name);
    let button =  $("#createGroupButton");

    if(res==true){
        button.addClass("redirect_button");
        button.removeClass("redirect_button_off");
        button.attr("onclick","createGroup()");
    }else{
        button.addClass("redirect_button_off");
        button.removeClass("redirect_button");
        button.removeAttr("onclick");
    }

    return res;
}

//Checks that the user ID added is valid
function validId(){
    let id = $("#user_id").val();
    let button =  $("#addUserButton");

    if(id>=10000000 && id<=99999999){
        button.addClass("redirect_button_2");
        button.removeClass("redirect_button_2_off");
        button.attr("onclick","addUser()");

        return true;
    }else{
        button.addClass("redirect_button_2_off");
        button.removeClass("redirect_button_2");
        button.removeAttr("onclick");

        return false;
    }

}


//Creates the group
function createGroup(){
    let name = $("#newGroupName").val();

    if(validName()==true){

        $.post("../php/creategroup.php",{
            name:name
        },function(data){
            let json = JSON.parse(data);
            console.log(data);
            if(json.type=="error"){
                alert(json.msg)
            }else if(json.type=="success"){
                //Tells other clients of the user that they created a group
                var data = {
                    'sender_id' : <?php echo json_encode($details[1]); ?>,
                    'session_code': <?php echo json_encode($details[0]); ?>,
                    'type' : 'created_group',
                    'chat_id': json.group_id,
                    'chat': 'group',
                    };
                    
                conn.send(JSON.stringify(data));

                $("#newgroup").css("display","none");
            }
        });

    }else{
        alert("Group name can only contain letters, numbers and spaces with a maximum of 20 characters");
    }
    
}

//Controls user box display
function userBox(){
    var display = $("#userGroup").css("display");

    if(display=="block"){
        $("#userGroup").css("display","none");
        $("#admin_controls").css("display","none");
        $("#user_id").val("");
        $("allUsers").empty();
    }else{
        $("#userGroup").css("display","block");
        $("#user_id").val("");
        if($("#"+receiver_id).attr("admin")==<?php echo $details[1]; ?>){
            $("#admin_controls").css("display","block");
        }else{
            $("#admin_controls").css("display","none");
        }

        getUsers();
    }
}

//Get group users
function getUsers(){
    $("#allUsers").load("../php/getgroupusers.php",{
        group_id: receiver_id,
    });
}

//Add user to group
function addUser(){
    let confirm = window.confirm("Are you sure you want to add this user?");

    if(confirm==true){
        let user_id = $("#user_id").val();
        $.post("../php/addusertogroup.php",{
            group_id: receiver_id,
            user_id: user_id
        },function(data){
            var json = JSON.parse(data);

            if(json.type=="success"){

                getUsers();
                $("#user_id").val("");

                //Tells other clients that a user was added to a group
                var data = {
                'sender_id' : <?php echo json_encode($details[1]); ?>,
                'session_code': <?php echo json_encode($details[0]); ?>,
                'type' : 'added_to_group',
                'chat_id': receiver_id,
                'user_id': user_id,
                'chat': 'group'
                };
                conn.send(JSON.stringify(data));

            }else if(json.type="error"){
                alert(json.msg)
            }

        });
    }
}
//Remove user or make them the admin
function controlGroup(u,g,t){

    if(t=="admin"){
        var confirmMsg = "Are you sure you want to make this user the admin?";
    }else if(t=="remove"){
        var confirmMsg = "Are you sure you want to remove this user?";
    }

    let confirm = window.confirm(confirmMsg);

    //Tells other clients that a user was made admin or removed
    if(confirm==true){
        $.post("../php/controlGroup.php",{
            group_id: g,
            user_id: u,
            type: t
        },function(data){
            var json = JSON.parse(data);

            if(json.type=="success"){
                if(json.msg=="admin"){

                    var data = {
                    'sender_id' : <?php echo json_encode($details[1]); ?>,
                    'session_code': <?php echo json_encode($details[0]); ?>,
                    'type' : 'new_admin',
                    'chat_id': g,
                    'chat': 'group',
                    'admin_id': u
                    };
                    conn.send(JSON.stringify(data));

                    $("#"+receiver_id).attr("admin",u);
                    $("#admin_controls").css("display","none");
                }else if(json.msg=="remove"){

                    var data = {
                    'sender_id' : <?php echo json_encode($details[1]); ?>,
                    'session_code': <?php echo json_encode($details[0]); ?>,
                    'type' : 'removed_user',
                    'chat_id': g,
                    'chat': 'group',
                    'removed_id': u
                    };
                    conn.send(JSON.stringify(data));
                }
                getUsers();
            }else if(json.type="error"){
                alert(json.msg)
            }

        });
    }
}

//Leave group
function leaveGroup(){

    let confirm = window.confirm("Are you sure you want to leave this group?");

    //Tells other clients that user left a group
    if(confirm==true){
        $.post("../php/exitgroup.php",{
            group_id: receiver_id,
            type: "leave"

        },function(data){
            var json = JSON.parse(data);

            if(json.type=="success"){
                var data = {
                'sender_id' : <?php echo json_encode($details[1]); ?>,
                'session_code': <?php echo json_encode($details[0]); ?>,
                'type' : 'left_group',
                'chat_id': receiver_id,
                'chat': 'group',
                };
                conn.send(JSON.stringify(data));

                $("#"+receiver_id).remove();
                unselectGroup();

            }else if(json.type="error"){
                alert(json.msg)
            }

        });
    }
}

//Delete group
function deleteGroup(){
    let confirm = window.confirm("Are you sure you want to delete this group?");

    //Tells other clietns that user deleted a group
    if(confirm==true){
        $.post("../php/exitgroup.php",{
            group_id: receiver_id,
            type: "delete"

        },function(data){
            console.log(data);
            var json = JSON.parse(data);

            if(json.type=="success"){

                var data = {
                'sender_id' : <?php echo json_encode($details[1]); ?>,
                'session_code': <?php echo json_encode($details[0]); ?>,
                'type' : 'deleted_group',
                'chat_id': receiver_id,
                'chat': 'group',
                };

                conn.send(JSON.stringify(data));

                $("#"+receiver_id).remove();
                unselectGroup();

            }else if(json.type="error"){
                alert(json.msg)
            }
        });
    }
}

</script>

<?php include("../php/footer.php") ?>