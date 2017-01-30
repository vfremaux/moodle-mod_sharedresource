<?php
include "../../../config.php";
/**
* TODO : Rewrite this old code with JQuery
*
*/

?>

function multiMenu(eltId,nbr) {
    arrLinkId = new Array();
    for (i = 0; i <= nbr ; i++) {
        arrLinkId[i]='_'+i;
    }
    intNbLinkElt = new Number(arrLinkId.length);
    arrClassLink = new Array('current','ghost');
    strContent = new String();
    for (i = 0; i < intNbLinkElt ; i++) {
        strTab = "menu"+arrLinkId[i];
        strContent = "tab"+arrLinkId[i];
        if ( arrLinkId[i] == eltId ) {
            document.getElementById(arrLinkId[i]).className = arrClassLink[0];
            document.getElementById(strTab).className = 'tabon content';
            document.getElementById(strContent).className = 'on content';
        } else {
            document.getElementById(arrLinkId[i]).className = arrClassLink[1];
            document.getElementById(strTab).className = 'taboff content';
            document.getElementById(strContent).className = 'off content';
        }
    }
}

/*
* Using XmlHttpRequest for adding fields
*/
function getXhr(){
    var xhr = null; 
    if(window.XMLHttpRequest) // Firefox et autres
        xhr = new XMLHttpRequest(); 
    else if(window.ActiveXObject){ // Internet Explorer 
        try {
            xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    else { // XMLHttpRequest not handled by browser
        alert("XMLHTTPRequest not dupported"); 
        xhr = false; 
    } 
    return xhr
}

/**
 * On button clic
 */
function go(pluginchoice,fieldnum,islist,numoccur,name,type,keyid,listchildren,capability,realoccur){
    if (type != 'category') {
        if((type == 'text' || type == 'codetext') && document.getElementById(keyid).value == '') {
            alert('<?php echo get_string('fillprevious','sharedresource'); ?>');
        }
        else if(type == 'select' && document.getElementById(keyid).options[document.getElementById(keyid).selectedIndex].value == 'basicvalue'){
            alert('<?php echo get_string('fillprevious','sharedresource'); ?>');
        }
        else if(type == 'date' && document.getElementById(keyid+"_dateyear").options[document.getElementById(keyid+"_dateyear").selectedIndex].value=='- Year -'){
            alert('<?php echo get_string('fillprevious','sharedresource'); ?>');
        }
        else if(type == 'duration' && (document.getElementById(keyid+"_Day").value == '' || document.getElementById(keyid+"_Day").value == '0') && (document.getElementById(keyid+"_Hou").value == '' || document.getElementById(keyid+"_Hou").value == '0') && (document.getElementById(keyid+"_Min").value == '' || document.getElementById(keyid+"_Min").value == '0') && (document.getElementById(keyid+"_Sec").value == '' || document.getElementById(keyid+"_Sec").value == '0') ){
            alert('<?php echo get_string('fillprevious','sharedresource'); ?>');
        }
        else if(type == 'vcard' && document.getElementById(keyid).value.replace(new RegExp("(\r\n|\r|\n)", "g" ),'').replace(/ /g, '') == 'BEGIN:VCARDVERSION:FN:N:END:VCARD'){
            alert('<?php echo get_string('fillprevious','sharedresource'); ?>');
        }
        else{
            document.getElementById("add_"+keyid).style.display = 'none';
            numoccur2 = parseInt(numoccur) + 1;
            var xhr = getXhr()
            // What to do when we get answer
            xhr.onreadystatechange = function(){
                // Only process on success
                if(xhr.readyState == 4 && xhr.status == 200){
                    document.getElementById("zone_"+name+"_"+numoccur).innerHTML = xhr.responseText;
                }
            }
            /*
            xhr.open("POST","<?php echo $CFG->wwwroot ?>/mod/sharedresource/ajax/addformelement.php",true);
            var data = "fieldnum=" + fieldnum +  "&islist=" + islist + "&numoccur=" + numoccur2 + "&pluginchoice=" + pluginchoice + "&name=" + name + "&capability=" + capability + "&realoccur=" + realoccur;
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send(data);
            */
            var data = "fieldnum=" + fieldnum +  "&islist=" + islist + "&numoccur=" + numoccur2 + "&pluginchoice=" + pluginchoice + "&name=" + name + "&capability=" + capability + "&realoccur=" + realoccur;
            xhr.open("GET","<?php echo $CFG->wwwroot ?>/mod/sharedresource/ajax/addformelement.php?"+data,true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send();
        }
    }
    if (type == 'category') {
        var occur = keyid.substr(keyid.indexOf(':', 0) + 1); // get parent IDs
        var listtab = listchildren.split(';');
        var lengthlist = new Number(listtab.length);
        var nbremptyfield = 0;
        var splitparent = keyid.split('_');
        var depthparent = (splitparent.length - 1);
        for (i = 0 ; i < lengthlist ; i++) {
            var splitchild=listtab[i].split('_');
            var depthchild=(splitchild.length - 1) * 2;
            var depth = (depthchild - depthparent) / 2;
            var child = listtab[i].concat(':',occur);
            for(j = 1 ; j <= depth ; j++){
                child = child.concat('_0');
            }
            try{
                if(document.getElementById(child).value == ''){
                    nbremptyfield++;
                }
            }
            catch(error){
            }
            try{
                if(document.getElementById(child).options[document.getElementById(child).selectedIndex].value == 'basicvalue'){
                    nbremptyfield++;
                }
            }
            catch(error){
            }
            try{
                if(document.getElementById(child+"_dateyear").options[document.getElementById(child+"_dateyear").selectedIndex].value == '-year-'){
                    nbremptyfield++;
                }
            }
            catch(error){
            }
            try{
                if((document.getElementById(child+"_Day").value == '' || document.getElementById(child+"_Day").value == '0') && (document.getElementById(child+"_Hou").value == '' || document.getElementById(child+"_Hou").value == '0') && (document.getElementById(child+"_Min").value == '' || document.getElementById(child+"_Min").value == '0') && (document.getElementById(child+"_Sec").value == '' || document.getElementById(child+"_Sec").value == '0') ){
                    nbremptyfield++;
                }
            }
            catch(error){
            }
            try{
                if(document.getElementById(child).value.replace(new RegExp("(\r\n|\r|\n)", "g" ),'').replace(/ /g, '') == 'BEGIN:VCARDVERSION:FN:N:END:VCARD'){
                    nbremptyfield++;
                }
            }
            catch(error){
            }
        }
        if(nbremptyfield == lengthlist){
            alert('<?php echo get_string('fillcategory','sharedresource'); ?>');
        } else {
            document.getElementById('add_'+keyid).style.display='none';
            numoccur2 = parseInt(numoccur) + 1;
            var xhr = getXhr()
            // What to be done on answer
            xhr.onreadystatechange = function(){
                // Only process when everything received
                if(xhr.readyState == 4 && xhr.status == 200){
                    document.getElementById("zone_"+name+"_"+numoccur).innerHTML = xhr.responseText;
                }
            }
            xhr.open("POST","<?php echo $CFG->wwwroot ?>/mod/sharedresource/ajax/addformelement.php",true);
            var data = "fieldnum=" + fieldnum +  "&islist=" + islist + "&numoccur=" + numoccur2 + "&pluginchoice=" + pluginchoice + "&name=" + name + "&capability=" + capability + "&realoccur=" + realoccur;
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send(data);
        }
    }
}