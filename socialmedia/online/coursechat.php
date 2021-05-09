<?php require("../php/conn.php") ?>
<?php require("../php/verifylogin.php") ?>
<?php require("../php/collectcmchat.php") ?>
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
                <div class="col redirect_button" onclick='window.open("groupchat.php","_self");'>
                    <p>Group</p>
                </div>
                <div class="col redirect_button_off" onclick='window.open("coursechat.php","_self");'>
                    <p>Course</p>
                </div>
            </div>
            <div class="row" id="flexRow">
                <div id="contentwrapper" class="col">
                    <div class="row" id="users">
                    <div class='cmplacemarker'>
                        <p>Course</p>
                    </div>
                        <!-- Show user course and modules -->
                        <?php
                        $setPlacemarker=0;
                        if(isset($cm_id)){
                            for($x=0;$x<count($cm_id);$x++){

                                if($cm_type[$x] == "module" && $setPlacemarker==0){

                                    echo "<div class='cmplacemarker'><p>Module</p></div>";
                                    $setPlacemarker=1;
                                }
                                include("../php/cm-template.php");
                            }
                        }else{
                            echo "<p style='text-align:center;width:100%'>No users</p>";
                        }
                        ?>
        
                    </div>
                </div>
                    <!-- Message area -->        
                <div id="msgArea" class="col" style="width:100%">
                    <div id="chatroomHeader" class="row" style='height:50px'>
                        <div class="col">
                           <!-- Switch from chatroom to chat options when in mobile view -->
                            <img id="backButton" onclick="toggleChatroom('hide')" src='../img/back_icon.png' style='width:50px;display:none'>
                        </div>
                         <!-- Chat header shows informaiton on person being messaged -->
                        <div class="col-6">
                            <div class="row float-right pr-2">
                                <p class="pl-1">
                                <span id="cmName"></span><br>
                                <span style="font-size:12px" id="cmInchat"></span>              
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="chatroomWrapper">
                    <!-- Chat area-->
                    <p id="chatRoom">
                    <?php 
                    if(isset($cm_id)){
                        echo "Select a course or module to message";
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

//All course and module Ids
var all_users = <?php if(!empty($cm_id)){ echo json_encode($cm_id);}else{ echo "[]"; }; ?>;
var user_types = <?php if(!empty($cm_type)){ echo json_encode($cm_type);}else{ echo "[]"; }; ?>;
//Current user talked to
var receiver_id;
//Total messages shown
var totalmessages = 20;
//Total messages sent
var sentmessages = 0;
//Colours of users name
var colors = [];
//Screen swidth
var screen_width = $(window).width();
//Preselected user in URL to talk to
var preselecteduser = <?php if(isset($_GET["cm"])){echo "'".$_GET["cm"]."'";}else{echo "'none'";} ?>;
//Sets URL depending on the host location
let url = location.host == 'localhost' ?
    'ws://localhost:8080' : location.host == 'ws://192.168.8.100' ?
    'ws://192.168.8.100:8080' :
    'ws://localhost:8080';

var conn = new WebSocket(url);


//When a connection is opened, the client notifies the server
conn.onopen = function(e) {
    console.log("Connection established!");
    var data = {
        'sender_id' : <?php echo json_encode($details[1]); ?>,
        'session_code': <?php echo json_encode($details[0]); ?>,
        'type' : 'log',
        'chat': 'course'
    };
    conn.send(JSON.stringify(data));
};

//Actiosn to take depending on the message sent to the client
conn.onmessage = function(e) {
    console.log(e.data);
        var json = JSON.parse(e.data);
        switch(json.type) {
            case 'cm':
                if(receiver_id==json.cm){
                    var date = createDate(json.timestamp);
                    var showdate = 'showDate("message'+json.message_id+'")';
                    var hidedate = 'hideDate("message'+json.message_id+'")';

                    if(colors[json.sender_id] === undefined){
                        colors[json.sender_id] = getRndInteger(50,125)+","+getRndInteger(50,125)+","+getRndInteger(50,125);
                    }

                    var msg = "<span onmouseover='"+showdate+"' onmouseout='"+hidedate+"'><a href='profile.php?user="+json.sender_id+"' style='color:rgb("+colors[json.sender_id]+")'><b>"+json.fname+" "+json.lname+"<a/></b><span id='message"+json.message_id+"' style='display:none;color:rgb("+colors[json.sender_id]+")'> "+date+"</span><br>"+json.msg+"</span><br><br>";
                    
                    $('#chatRoom').append(msg);
                }

                break;
                //Confirm message is sent
            case 'confirm':
                var sent_to_id = json.cm;
                
                if(receiver_id==sent_to_id){
                    //Changes the text to black as the message has successfully been sent from this client, else the messaged is load for other clients
                    if(json.from=="current"){
                        $("[msf-for='"+sent_to_id+"']").css("color","black");
                        $("[msf-for='"+sent_to_id+"']").css("opacity","1");
                    }else if(json.from=="other"){
                        //If the message was not sent from this client then paste it
                        var msg = json.msg;
                        var currentTime = Math.round(new Date().getTime()/1000);
                        var date = createDate(currentTime);
                        var showdate = 'showDate("messagesent'+sentmessages+'")';
                        var hidedate = 'hideDate("messagesent'+sentmessages+'")';
                        var msgFormat = "<span msf-for='"+sent_to_id+"' onmouseover='"+showdate+"' onmouseout='"+hidedate+"' style='text-align: right;display: block;'><span id='messagesent"+sentmessages+"' style='display:none;'>"+date+"</span><b> You</b><br>"+msg+"</span><br>";
                        
                        $('#chatRoom').append(msgFormat);
                        $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);

                        sentmessages++;
                        totalmessages++;
                    }
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
                //Notifies client that someone joined a chat
            case 'joined_chat':

                if(json.joined==receiver_id){
                    $("#cmInchat").text("Active users: "+json.joined_total);
                }else if(json.left==receiver_id){
                    $("#cmInchat").text("Active users: "+json.left_total);
                }
                
                if(json.hasOwnProperty('joined')){
                    $("#inChat"+json.joined).attr("in-chat",json.joined_total)
                    $("#inChat"+json.joined).text("Active users: "+json.joined_total);
                }

                if(json.left!='none'){
                    $("#inChat"+json.left).attr("in-chat",json.left_total);
                    $("#inChat"+json.left).text("Active users: "+json.left_total);

                }

                break;
            }
    $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);
    totalmessages++;
};

//send message 
function sendMessage(){
     //Get message
    var msg = $("#newMessage").val();

    //If message isn't empty continue
    if(msg.length!=0){

        sentmessages++;
        //Change message intohtml entity
        $.post("../php/htmlentities_msg.php",{
            msg:msg
        },
        function(data){
            var altered_msg = data; 
                
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
                'type':'cm',
                'chat': 'course'
            };

            totalmessages++;
            $("#newMessage").val('');

            conn.send(JSON.stringify(data));
        })
    }
}

//Opens chat of user that was preselected
$(document).ready(function(){
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

//If the user scrolls to the top of the chatroom then load more messages
$('#chatroomWrapper').scroll(function() {
    var pos = $('#chatroomWrapper').scrollTop();
    var scrollHeight =  $("#chatroomWrapper")[0].scrollHeight;
    if (pos == 0) {
        totalmessages +=20;
        $('#chatRoom').load('../php/fetchcmchat.php',{
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

    //Notifies clients that they joined the chat
    var data = {
        'sender_id' : <?php echo json_encode($details[1]); ?>,
        'session_code': <?php echo json_encode($details[0]); ?>,
        'type' : 'joinedchat',
        'chat_id': b,
        'chat': 'course'
        };

    conn.send(JSON.stringify(data));

    if(screen_width<=640){
        toggleChatroom("show");
    }

    $('#chatRoom').empty();

    receiver_id = b;
    //Fetch the messages
    $('#chatRoom').load('../php/fetchcmchat.php',{
        receiver_id: receiver_id
    },function(data){
        $("#chatroomWrapper").scrollTop($("#chatroomWrapper")[0].scrollHeight);
    });

    //Changes message header
    $.post("../php/cmheaderinfo.php",{
        receiver_id: receiver_id
    },function(data){

        var name = data.name;
        $("#cmName").text(name);
        $("#cmInchat").text("Active users: "+data.total);

    },"json");
        //Remove the message input and send from being disabled
    $('#newMessage').removeAttr("disabled");
    $('#sendButton').removeAttr("disabled");
    $('#inputRow').css("background-color","white");
    $('#sendButton').css("background-color","#C7DDF8");

    totalmessages=20;

    $('#lastMessage' + b).text($('#lastMessage' + b).text());
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
</script>

<?php include("../php/footer.php") ?>