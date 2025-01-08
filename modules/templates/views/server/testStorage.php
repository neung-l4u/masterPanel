<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <div class="container pt-5">
        <h2 id="header">Menu</h2>
        <div class="mt-3">
            <b>Status : </b>
            <ul>
                <li>Home - <span id="txtHomeInfo" class="text-secondary">Not Yet</span></li>
                <li>About - <span id="txtAboutInfo" class="text-secondary">Not Yet</span></li>
                <li>Contact - <span id="txtContactInfo" class="text-secondary">Not Yet</span></li>
            </ul>
        </div>
        <div class="mt-3">
        <a href="#" class="btn btn-primary" onclick="checkPage('home');">Check Home</a>
        <a href="#" class="btn btn-primary" onclick="checkPage('about');">Check About</a>
        <a href="#" class="btn btn-primary" onclick="checkPage('contact');">Check Contact</a>
        </div>
        <div class="mt-3">
            <a href="#" class="btn btn-primary" onclick="setPage('home');">Set Home</a>
            <a href="#" class="btn btn-primary" onclick="setPage('about');">Set About</a>
            <a href="#" class="btn btn-primary" onclick="setPage('contact');">Set Contact</a>
        </div>
        <br><br>
        <div class="mt-3">
            <a href="#" class="btn btn-primary" onclick="readPage('about');">Read Key</a>
        </div>
        <br><br>
        <div class="mt-3">
            <a href="#" class="btn btn-primary" onclick="clearKey();">Clear</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  </body>
</html>


<script>
let pages = [];
const projectID = 35;
const ketTxt = "sendStatus";
const key = ketTxt+projectID;

$(function() {
    setAllPageStatus();
});//ready

function readPage(param){ //about
  let readStatus = localStorage.getItem(key);
  let arrStatus = JSON.parse(readStatus);
  console.log("arrStatus = "+arrStatus);

  return true;
}//readPage

function setPage(param){
    let currentPage = localStorage.getItem(key);
    let findPage = pages.includes(param);
    if(!findPage){
        pages.push(param);
        let val = JSON.stringify(pages); //arr to text
        localStorage.setItem(key,val);
    }
    return true;
}//setPage

function clearKey(){
  localStorage.clear();
  return true;
}

function checkPage(param){
    let currentPage = localStorage.getItem(key);
    let currentPageArr =  JSON.parse(currentPage);
    let findPage = false;

    if (currentPageArr != null){
        findPage = currentPageArr.includes(param);
    }

    console.log("Result = "+findPage);
    return findPage;
}//checkPage

function setAllPageStatus(){
    const txtHomeInfo = $("#txtHomeInfo");
    const txtAboutInfo = $("#txtAboutInfo");
    const txtContactInfo = $("#txtContactInfo");
    
    if(checkPage('home')){
        txtHomeInfo.removeClass( "text-secondary" ).addClass( "text-success" ).empty().text("Send !!");
    }

    if(checkPage('about')){
        txtAboutInfo.removeClass( "text-secondary" ).addClass( "text-success" ).empty().text("Send !!");
    }

    if(checkPage('contact')){
        txtContactInfo.removeClass( "text-secondary" ).addClass( "text-success" ).empty().text("Send !!");
    }
}
</script>