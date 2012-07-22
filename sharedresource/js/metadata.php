<?php
require_once("../../../config.php");
header('Content-type: text/javascript');
?>

function multiMenu(eltId,nbr) {
	arrLinkId = new Array();
	for (i = 0; i < = nbr ; i++) {
		arrLinkId[i]='_'+i;
	}
	intNbLinkElt = new Number(arrLinkId.length);
	arrClassLink = new Array('current','ghost');
	strContent = new String();
	for (i = 0; i < intNbLinkElt ; i++) {
		strContent = "menu"+arrLinkId[i];
		if ( arrLinkId[i] == eltId ) {
			document.getElementById(arrLinkId[i]).className = arrClassLink[0];
			document.getElementById(strContent).className = 'on content';
		} else {
			document.getElementById(arrLinkId[i]).className = arrClassLink[1];
			document.getElementById(strContent).className = 'off content';
		}
	}	
}

/*
Utilisation de l'objet XmlHttpRequest pour ajouter des champs.
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
	else { // XMLHttpRequest non supporté par le navigateur 
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		xhr = false; 
	} 
	return xhr
}

		
/**
* Méthode qui sera appelée sur le click du bouton
*/
function go(pluginchoice,fieldnum,islist,numoccur,name,type,keyid,listchildren,capability,realoccur){
	if(type != 'category'){
		if((type == 'text' || type == 'codetext') && document.getElementById(keyid).value == '') {
			alert('<?php echo get_string('fillprevious','sharedresource'); ?>');
		}
		else if(type == 'select' && document.getElementById(keyid).options[document.getElementById(keyid).selectedIndex].value=='basicvalue'){
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
			// On défini ce qu'on va faire quand on aura la réponse
			xhr.onreadystatechange = function(){
				// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
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
	if(type == 'category'){
		var occur = keyid.substr(keyid.indexOf(':',0)+1); //on récupère le numéro d'occurence des parents
		var listtab = listchildren.split(';');
		var lengthlist = new Number(listtab.length);
		var nbremptyfield = 0;
		var splitparent=keyid.split('_');
		var depthparent=(splitparent.length-1);
		for (i=0; i<lengthlist; i++) {
			var splitchild=listtab[i].split('_');
			var depthchild=(splitchild.length-1)*2;
			var depth = (depthchild-depthparent)/2;
			var child = listtab[i].concat(':',occur);
			for(j=1;j<=depth;j++){
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
				if(document.getElementById(child).options[document.getElementById(child).selectedIndex].value=='basicvalue'){
					nbremptyfield++;
				}
			}
			catch(error){
			}
			try{
				if(document.getElementById(child+"_dateyear").options[document.getElementById(child+"_dateyear").selectedIndex].value=='-year-'){
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
			// On défini ce qu'on va faire quand on aura la réponse
			xhr.onreadystatechange = function(){
				// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
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