
//Changes the picture preview to the one the user selected
function picture(event) {

    var image_destination = document.getElementById('profileImage');
    var image_path = URL.createObjectURL(event.target.files[0]);
    image_destination.style.backgroundImage = "url('" + image_path + "')";

    document.getElementById("image-status").value ="changed";
  };

//Changes the image to default when it is removed temporarily
function deleteImage(){
    document.getElementById("upload").value="";

    var image_destination = document.getElementById('profileImage');
    image_destination.style.backgroundImage = "url('../img/profilepics/default.png')";

    document.getElementById("image-status").value ="deleted";
  }

//Checks the settings form before it is submitted
function formChecker(){
    var currentPsw = document.getElementById("pass1").value;
    var newPsw = document.getElementById("pass2").value;
    var bio = document.getElementById("bio").value;

    var errors = "";
    //Makes sure passwords match, are not empty and bio is no more than 160 characters
    if(((newPsw == null && currentPsw == null) || currentPsw == newPsw) && bio.length<=160){
      return true;
    }else{
      if(currentPsw != newPsw){
        errors += 'Passwords must match';
      }
      if(bio.length>=160){
        if(errors.length>0){
          errors += ' and ';
        }
        errors += 'Bio must be 160 characters maximum';
      }
    $("#message-field").text(errors);
    $("#message-field").removeClass("success-field");
    $("#message-field").addClass("error-field");

    //Scroll to the top to show error/s
    $(window).scrollTop(0);
    return false;
  }
}
//Changes the class of a bio to show whether it is vlaid or not
function textAreaError(){
  var bio = document.getElementById("bio");

  if(bio.value.length>160){
    bio.classList.add("is-invalid");
  }else{
    bio.classList.remove("is-invalid");
  }
}

//Displays supported image types
function supportedTypes(){
  alert("Only png, jpeg and jpg image types are accepted");
}