function selection_champs(champs,champs_affiche){

	selection = champs.selectedIndex;
	if(selection != -1){

		while(champs_affiche.selectedIndex != -1){
			champs_affiche.options[champs_affiche.selectedIndex].selected = false;
		}
		while(champs.selectedIndex > -1){
			if(champs.options[champs.selectedIndex].value == "Id_type_bien"){
				champs.options[champs.selectedIndex] = null;
				champs.form.Id_categorie_bien.options[0].select= true;
			}else{
				//on cherche la place de notre champ
				for(place=0;place<champs_affiche.length;place++){
					if(champs_affiche.options[place].text > champs.options[champs.selectedIndex].text){
						break;
					}
				}

				for(i=champs_affiche.length;i>place;i--){
					champs_affiche.options[i] = new Option(champs_affiche.options[(i-1)].text,champs_affiche.options[(i-1)].value);
				}
		
				champs_affiche.options[place] = new Option(champs.options[champs.selectedIndex].text,champs.options[champs.selectedIndex].value);
				champs.options[champs.selectedIndex] = null;
				champs_affiche.options[place].selected = true;
			}
		}
	
		if(champs.length > 0){
			if(selection >= champs.length ){
				selection = champs.length-1;
			}
			champs.options[selection].selected = true;
		}
	}
}

function select_all(frm){
	for(i=0;i<frm.liste_champs.length;i++){
		frm.liste_champs.options[i].selected = true;
	}
	frm.liste_champs.name = "liste_champs[]";
	for(i=0;i<frm.selection.length;i++){
		frm.selection.options[i].selected = true;
	}
	frm.selection.name = "selection[]";
}

function priorite_champ(selection,mode){
	if(selection.length < 2 ){return;}
	old_place = selection.selectedIndex;
	if(mode == 'up' && old_place > 0){
		new_place = old_place-1;
	}else if(mode == 'down' && old_place < selection.length-1){
		new_place = old_place+1;
	}
	tmp = new Option(selection.options[new_place].text,selection.options[new_place].value);
	selection.options[new_place] = new Option(selection.options[old_place].text,selection.options[old_place].value);
	selection.options[old_place] = new Option(tmp.text,tmp.value);
	selection.options[new_place].selected = true;
}