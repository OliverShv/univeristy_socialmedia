//Shows the date of a message
function showDate(id){
    document.getElementById(id).style.display ="initial";
}

//Hides the date of a message
function hideDate(id){
    document.getElementById(id).style.display ="none";
}

//Formates the date of a message from the timestamp given
function createDate(a){
    var timestamp = a;

    var date = new Date(timestamp * 1000);
    
    //Gets year
    var year = date.getFullYear();

    //Gets month
    var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var month = months[date.getMonth()];

    var day = date.getDate();

    //Gets the remainder of a number when diving by 10 to see what indicator to use
    switch (day % 10) {
        case 1:
          var indicator = "st";
          break;
        case 2:
            var indicator = "nd";
          break;
        case 3:
            var indicator = "rd";
          break;
        default:
            var indicator = "th";
          break;
      }
    
      //Gets hour in AM or PM
    if(date.getHours()>12){
        var hour = date.getHours()-12;
        if(hour<10){
          hour = "0"+hour;
        }
        var end = "PM";
    }else{
        var hour = date.getHours();
        if(hour<10){
          hour = "0"+hour;
        }
        var end = "AM";
    }

    //Gets minute
    if(date.getMinutes()>=10){
      var minute = date.getMinutes();
    }else{
      var minute = "0"+date.getMinutes();
    }

    //combines all variables and returns full date
    var fulldate = day+indicator+" "+month+" "+year+", "+hour+":"+minute+" "+end;
    return fulldate;

}

//Gets a random integer from a min to max value
function getRndInteger(min, max) {
  return Math.floor(Math.random() * (max - min + 1) ) + min;
}