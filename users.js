showuser=function(userid,name,bookmark){
	addtab('user_'+userid,'<img src="imgs/t.gif" class="ico-user">'+name,'showuser&userid='+userid,function(){
		if (gid('cardsettings_'+userid)){
			if (!document.smartcard){
				if (!gid('needcert_'+userid).checked) gid('cardsettings_'+userid).style.display='none';
				gid('smartcardloader_'+userid).style.display='none';
			}
		}
	},null,{fastlane:1,bookmark:bookmark});	
}

_inline_lookupuser=function(d){
	if (d.lastkey!=null&&d.lastkey==d.value) return;
	d.lastkey=d.value;	
	if (d.timer) clearTimeout(d.timer);
	d.timer=setTimeout(function(){
		ajxpgn('userlist',document.appsettings.fastlane+'?cmd=slv_core__users&mode=embed&key='+encodeHTML(d.value)+gid('searchfilter_user').value);
	},200
	);	
}

adduser=function(roles,gskey){

	var suffix='new';
	var ologin=gid('login_'+suffix);
	var odispname=gid('dispname_'+suffix);

	var active=0;
	var virtual=0;
	if (gid('active_'+suffix).checked) active=1;
	if (gid('virtual_'+suffix).checked) virtual=1;

	var passreset=0;
	
	if (gid('passreset_'+suffix).checked) passreset=1;	
	
	valid=1;
	
	var opass=gid('newpass_'+suffix);
	var opass2=gid('newpass2_'+suffix);
	
	//delete the excessive validate rules
	if (!valstr(ologin)) valid=0;
	if (!valstr(odispname)) valid=0;
	
	if (!virtual){
		if (!valstr(opass)) valid=0;
		if (!valstr(opass2)) valid=0;

		//add more validation rules
		
		if (opass.value!=opass2.value){
			valid=0;
			salert(document.dict.mismatching_password);	
		}
	}
	
	if (!valid) return;
	
	var newpass=encodeHTML(opass.value);

	var login=encodeHTML(ologin.value);
	var dispname=encodeHTML(odispname.value);
	
	var groupnames=['users'];
	if (!virtual){
		for (var i=0;i<roles.length;i++){
			if (!gid('userrole_'+roles[i]+'_'+suffix)) {salert('Settings outdated; please reload your screen to continue;');return;}
			if (gid('userrole_'+roles[i]+'_'+suffix).checked) groupnames.push(roles[i]);
		}	
	}
	
	groupnames=groupnames.join('|');	
	
	var params=[];
	params.push('login='+login);
	params.push('dispname='+dispname);
	params.push('active='+active);
	params.push('virtual='+virtual);
	params.push('passreset='+passreset);
	params.push('groupnames='+groupnames);	
	
	
	reloadtab('user_new','','adduser&'+params.join('&'),null,'newpass='+newpass,null,gskey);
	
}


updateuser=function(userid,roles,gskey){
	var suffix=userid;
	var ologin=gid('login_'+suffix);
	var odispname=gid('dispname_'+suffix);
	
	var active=0;
	var virtual=0;
	
	var unlockga=0;
	//if (gid('unlockga_'+suffix)&&gid('unlockga_'+suffix).checked) unlockga=1;

	if (gid('active_'+suffix).checked) active=1;
	if (gid('virtual_'+suffix).checked) virtual=1;

	var passreset=0;
	if (gid('passreset_'+suffix).checked) passreset=1;
		

	var newpass=gid('newpass_'+suffix).value;
	var newpass2=gid('newpass2_'+suffix).value;
	
	
	valid=1;
	var offender=null;
	
	if (!valstr(ologin)) {valid=0;offender=offender||ologin;}
	if (!valstr(odispname)) {valid=0;offender=offender||odispname;}
	
	if (!virtual){
		if (newpass!=newpass2){
			valid=0;
			salert('New passwords must match\nOr you may leave them blank');
			return;	
		}
	}
		
	
	if (!valid) {
		if (offender&&offender.focus) offender.focus();
		return;
	}
	
	
	var login=encodeHTML(ologin.value);
	var dispname=encodeHTML(odispname.value);
	
	var groupnames=['users'];

	newpass=encodeHTML(newpass);

		
	if (!virtual){
		for (var i=0;i<roles.length;i++){
			if (!gid('userrole_'+roles[i]+'_'+suffix)) {salert('Settings outdated; please reload your screen to continue;');return;}
			if (gid('userrole_'+roles[i]+'_'+suffix).checked) groupnames.push(roles[i]);
		}
	}
	
	groupnames=groupnames.join('|');
	
	
	var params=[];
	params.push('login='+login);
	params.push('dispname='+dispname);
	params.push('active='+active);
	params.push('virtual='+virtual);
	params.push('passreset='+passreset);
	params.push('groupnames='+groupnames);
	//params.push('unlockga='+unlockga);
	
	
	reloadtab('user_'+userid,ologin.value,'updateuser&userid='+userid+'&'+params.join('&'),function(rq){
		reloadview('core.users','userlist',true);
		if (rq.getResponseHeader('newlogin')!=null&&rq.getResponseHeader('newlogin')!='') gid('labellogin').innerHTML=decodeURIComponent(rq.getResponseHeader('newdispname'));
		if (rq.getResponseHeader('newdispname')!=null&&rq.getResponseHeader('newdispname')!='') gid('labeldispname').innerHTML=decodeURIComponent(rq.getResponseHeader('newdispname'));
		flashstatus('User '+ologin.value+' has been updated', 2000);
	},"pass="+newpass,{fastlane:1},gskey);
	
}


deluser=function(userid,gskey){
	if (!sconfirm(document.dict['confirm_user_delete'])) return;
	
	reloadtab('user_'+userid,null,'deluser&userid='+userid,function(rq){
		//vendor auth

		closetab('user_'+userid);
		reloadview('core.users','userlist',true);
	},null,{fastlane:1},gskey);
}
