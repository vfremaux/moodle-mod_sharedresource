<?php
ob_start();
require_once("../../../config.php");
ob_end_clean();
header('Content-type: text/javascript');
?>

function multiMenu(eltId, tabcount) {

	arrLinkId = new Array();
	for (i = 0; i <= tabcount ; i++) {
		arrLinkId[i] = '_'+i;
	}
	
	intNbLinkElt = new Number(arrLinkId.length);
	for (i = 0 ; i < intNbLinkElt ; i++) {
		strContent = new String();
		strContent = "tab"+arrLinkId[i];
		if ( arrLinkId[i] == eltId ) {
			document.getElementById(arrLinkId[i]).parentNode.className = 'active';
			document.getElementById(strContent).className = 'on content';
		} else {
			document.getElementById(arrLinkId[i]).parentNode.className = '';
			document.getElementById(strContent).className = 'off content';
		}
	}	
}
		
/**
* Méthode qui sera appelée sur le click du bouton
*/
function go(pluginchoice, fieldnum, islist, numoccur, name, type, keyid, listchildren, capability, realoccur){
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
		} else {
			addlistitem(keyid, fieldnum, islist, numoccur, pluginchoice, name, capability, realoccur)		
		}
	}
	if(type == 'category'){
		var occur = keyid.substr(keyid.indexOf(':', 0) + 1); //on récupère le numéro d'occurence des parents
		var listtab = listchildren.split(';');
		var lengthlist = new Number(listtab.length);
		var nbremptyfield = 0;
		var splitparent = keyid.split('_');
		var depthparent = (splitparent.length - 1);
		for (i = 0 ; i < lengthlist ; i++) {
			var splitchild = listtab[i].split('_');
			var depthchild = (splitchild.length - 1) * 2;
			var depth = (depthchild-depthparent) / 2;
			var child = listtab[i].concat(':', occur);
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
			addlistitem(keyid, fieldnum, islist, numoccur, pluginchoice, name, capability, realoccur)
		}
	}
}

// sends ajax query to refresh some form parts.

function addlistitem(keyid, fieldnum, islist, numoccur, pluginchoice, name, capability, realoccur){

	document.getElementById("add_"+keyid).style.display = 'none';
	numoccur2 = parseInt(numoccur) + 1;

	var params = "fieldnum=" + fieldnum +  "&islist=" + islist + "&numoccur=" + numoccur2 + "&pluginchoice=" + pluginchoice + "&name=" + name + "&capability=" + capability + "&realoccur=" + realoccur;
    var url = "<?php echo $CFG->wwwroot ?>/mod/sharedresource/ajax/addformelement.php?"+params;
    
    $.get(url, function(data, status){
    	zonename = "#zone_"+name+"_"+numoccur;
    	$(zonename).html(data); 
    }); 
} 