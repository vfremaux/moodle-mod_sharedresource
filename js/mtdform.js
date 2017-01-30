
//

function selectall(typemtd, mtd){
	for (i = 0 ; i <  document.forms['mtdconfigurationform'].elements.length ; i++){
		fieldobj = document.forms['mtdconfigurationform'].elements[i];
		if (fieldobj.tagName == 'INPUT' && fieldobj.name.match('config_'+mtd+'_'+typemtd)){
			fieldobj.disabled = false;
			fieldobj.checked = true;
		}
	}
}

function selectnone(typemtd, mtd){
	for (i = 0 ; i <  document.forms['mtdconfigurationform'].elements.length ; i++){
		fieldobj = document.forms['mtdconfigurationform'].elements[i];
		if (fieldobj.tagName == 'INPUT' && fieldobj.name.match('config_'+mtd+'_'+typemtd)){
			if (fieldobj.name.length != 'config'.length + mtd.length + typemtd.length + 4){
					fieldobj.disabled = true;
			}
			fieldobj.checked = false;
		}
	}
}

function toggle_childs(mtd, fieldtype, node_id){
	parentfieldobj = document.getElementById(mtd+'_'+fieldtype+'_'+node_id);
	if (parentfieldobj){
		for (i = 0 ; i <  document.forms['mtdconfigurationform'].elements.length ; i++){
			fieldobj = document.forms['mtdconfigurationform'].elements[i];
			if (parentfieldobj.checked == true){
				if (fieldobj.tagName == 'INPUT' && fieldobj.id.match('^'+mtd+'_'+fieldtype+'_'+node_id+'_') && fieldobj.id.indexOf('_',parentfieldobj.id.length+1)==-1){
					fieldobj.disabled = false;
				}
			} else {
				if (fieldobj.tagName == 'INPUT' && fieldobj.id.match('^'+mtd+'_'+fieldtype+'_'+node_id+'_')){
					fieldobj.disabled = true;
					fieldobj.checked = false;
				}
			}
		}
	}
}