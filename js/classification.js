/**
 * TODO: Old Code. Should be JQueried
 * Utilisation de l'objet XmlHttpRequest
 */

function classif(wwwroot, name, num, key, classif, value) {
    var url = wwwroot+"/mod/sharedresource/classiftest.php";

    var params = {name: name,
                  num: num,
                  key: key,
                  classif: classif,
                  value: value};

    $.post(url, params, function(data){
        $('#classif'+num).html(data)
        nextattribute = document.createElement("div");
        num2 = num + 1;
        nextattribute.id = 'classif' + num2;
        $('#classif'+num).append(nextattribute);
    });
}

function classif_old(wwwroot, name, num, key, classif, value) {
    var ajaxRequest; 

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }
    // Create a function that will receive data sent from the server
    ajaxRequest.onreadystatechange = function(){
        if(ajaxRequest.readyState == 4){
            var ajaxDisplay = document.getElementById('classif'+num);
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
            maDiv = document.createElement("div");
            num2=num+1;
            maDiv.id = 'classif'+num2;
            document.getElementById('classif'+num).appendChild(maDiv);
        }
    }
    ajaxRequest.open("POST", wwwroot+"/mod/sharedresource/classiftest.php", true);
    var data = "name=" + name +"&num=" + num +"&key=" + key +"&classif=" + classif+"&value=" + value;
    ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxRequest.send(data); 
}
