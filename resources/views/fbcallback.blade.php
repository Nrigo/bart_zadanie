


<script>

var access_token = null;

var hash = window.location.hash.substr(1);
var tokenParts = hash.split('&');

tokenParts.forEach(function(element){
    var splitted = element.split('=');
    if(splitted[0]=='access_token'){
        access_token = splitted[1];
    }
});
if(access_token!=null){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == XMLHttpRequest.DONE) {
           if (xmlhttp.status == 200) {

           }
        }
    };
    xmlhttp.open("GET", "/saveAccessToken?access_token="+access_token, true);
    xmlhttp.send();
}



</script>