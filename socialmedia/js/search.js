var collapsed = "no";

//Used to hide the search menu for when the user is in the mobile view
function hideSearch(){

    var search = document.getElementById("searchOptions");
    var designArea = document.getElementById("design-area");
    var width = window.innerWidth;

    if(width<=576){
        if(collapsed == "no"){
            search.style.display = "none";
            designArea.style.border = "0px";
            collapsed = "yes";
        }else{
            search.style.display = "inline";
            designArea.style.border = "solid 1px #707070";
            collapsed = "no";
        }
    }
}

//Resets the search to desktop view when exiting mobile view
function reset(){

    var search = document.getElementById("searchOptions");
    var designArea = document.getElementById("design-area");
    var width = window.innerWidth;
    
    if(width>576){
        search.style.display = "inline";
        designArea.style.border = "solid 1px #707070";
        collapsed = "no";
    }
}

window.onresize = reset;
reset();