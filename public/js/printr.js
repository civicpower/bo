function printr_switch(obj){
	try{
		if(obj.parentNode.className=='printr_cl'){
			obj.parentNode.className='printr_op';
		}else{
			obj.parentNode.className='printr_cl';
		}
	}catch(nerr){alert(nerr);}
}