showconn=function(connid,name,bookmark){
	addtab('conn_'+connid,name,'showconn&connid='+connid,null,null,{bookmark:bookmark});	
}

_inline_lookupconn=function(d){
	var soundex='';
	if (d.soundex) soundex='&soundex=1';

	if (d.lastkey!=null&&d.lastkey==d.value) return;
	d.lastkey=d.value;
			
	if (d.timer) clearTimeout(d.timer);
	d.timer=setTimeout(function(){
		ajxpgn('connlist',document.appsettings.codepage+'?cmd=slv_codegen__conns&mode=embed&key='+encodeHTML(d.value)+soundex);
	},200
	);	
}

setconntype=function(d){
	gid('newconn_host').style.display='none';
	gid('newconn_dbname').style.display='none';
	gid('newconn_apiport').style.display='none';
	gid('newconn_connuser').style.display='none';
	gid('newconn_connpass').style.display='none';
	gid('newconn_appdomain').style.display='none';
	gid('newconn_appredirurl').style.display='none';
	gid('newconn_appclientid').style.display='none';
	gid('newconn_appsecret').style.display='none';
	gid('newconn_apptoken').style.display='none';

	switch (d.value){
		case 'mysqli':
			gid('newconn_host').style.display='block';
			gid('newconn_connuser').style.display='block';
			gid('newconn_connpass').style.display='block';
		break;
		case 'mysql-noprep':
			gid('newconn_host').style.display='block';
			gid('newconn_connuser').style.display='block';
			gid('newconn_connpass').style.display='block';
		break;		
		case 'sqlsrv':
			gid('newconn_host').style.display='block';
			gid('newconn_dbname').style.display='block';
			gid('newconn_connuser').style.display='block';
			gid('newconn_connpass').style.display='block';
		break;
		case 'clickhouse':
			gid('newconn_host').style.display='block';
			gid('newconn_apiport').style.display='block';
			gid('newconn_connuser').style.display='block';
			gid('newconn_connpass').style.display='block';
		break;
		case 'mongodb':
			gid('newconn_host').style.display='block';
			gid('newconn_dbname').style.display='block';
			gid('newconn_connuser').style.display='block';
			gid('newconn_connpass').style.display='block';
		break;
		case 'sfdx':
			gid('newconn_connuser').style.display='block';
		break;
		case 'sfapi':
			gid('newconn_appdomain').style.display='block';
			gid('newconn_appredirurl').style.display='block';
			gid('newconn_appclientid').style.display='block';
			gid('newconn_appsecret').style.display='block';		
			gid('newconn_apptoken').style.display='block';
		break;
		default: return;
	}//switch
}


addconn=function(gskey){

	var suffix='new';
	var oconnname=gid('connname_'+suffix);
	var oconntype=gid('conntype_'+suffix);
	var oconnhost=gid('connhost_'+suffix);
	var oconndbname=gid('conndbname_'+suffix);
	var oconnapiport=gid('connapiport_'+suffix);
	var oconnuser=gid('connuser_'+suffix);
	var oconnpass=gid('connpass_'+suffix);
	
	var oappdomain=gid('appdomain_'+suffix);
	var oappclientid=gid('appclientid_'+suffix);
	var oappsecret=gid('appsecret_'+suffix);
	var oapptoken=gid('apptoken_'+suffix);

	
	var valid=1;
	var offender=null;
	
	//delete the excessive validate rules
	if (!valstr(oconnname)) {valid=0; offender=offender||oconnname;}
	if (!valstr(oconntype)) {valid=0; offender=offender||oconntype;}
	//if (!valstr(oconnhost)) {valid=0; offender=offender||oconnhost;}
	//if (!valstr(oconndbname)) {valid=0; offender=offender||oconndbname;}
	//if (!valstr(oconnapiport)) {valid=0; offender=offender||oconnapiport;}
	//if (!valstr(oconnuser)) {valid=0; offender=offender||oconnuser;}
	//if (!valstr(oconnpass)) {valid=0; offender=offender||oconnpass;}

	//add more validation rules
	
	if (!valid) {
		if (offender&&offender.focus) offender.focus();
		return;
	}

	var connname=encodeHTML(oconnname.value);
	var conntype=encodeHTML(oconntype.value);
	var connhost=encodeHTML(oconnhost.value);
	var conndbname=encodeHTML(oconndbname.value);
	var connapiport=encodeHTML(oconnapiport.value);
	var connuser=encodeHTML(oconnuser.value);
	var connpass=encodeHTML(oconnpass.value);
	
	var appdomain=encodeHTML(oappdomain.value);
	var appclientid=encodeHTML(oappclientid.value);
	var appsecret=encodeHTML(oappsecret.value);
	var apptoken=encodeHTML(oapptoken.value);
	
	var params=[];
	params.push('connname='+connname);
	params.push('conntype='+conntype);
	params.push('connhost='+connhost);
	params.push('conndbname='+conndbname);
	params.push('connapiport='+connapiport);
	params.push('connuser='+connuser);
	params.push('connpass='+connpass);
	
	params.push('appdomain='+appdomain);
	params.push('appclientid='+appclientid);
	params.push('appsecret='+appsecret);
	params.push('apptoken='+apptoken);

	reloadtab('conn_new','','addconn',function(req){
		var connid=req.getResponseHeader('newrecid');		
		reloadview('codegen.conns','connlist');
	},params.join('&'),null,gskey);
	
}

updateconn=function(connid,gskey){
	var suffix=connid;
	var oconnname=gid('connname_'+suffix);
	var oconntype=gid('conntype_'+suffix);
	var oconnhost=gid('connhost_'+suffix);
	var oconndbname=gid('conndbname_'+suffix);
	var oconnapiport=gid('connapiport_'+suffix);
	var oconnuser=gid('connuser_'+suffix);
	var oconnpass=gid('connpass_'+suffix);

	
	var valid=1;
	var offender=null;
	
	//delete the excessive validate rules
	if (!valstr(oconnname)) {valid=0; offender=offender||oconnname;}
	if (!valstr(oconntype)) {valid=0; offender=offender||oconntype;}
	if (!valstr(oconnhost)) {valid=0; offender=offender||oconnhost;}
	//if (!valstr(oconndbname)) {valid=0; offender=offender||oconndbname;}
	//if (!valstr(oconnapiport)) {valid=0; offender=offender||oconnapiport;}
	if (!valstr(oconnuser)) {valid=0; offender=offender||oconnuser;}
	if (!valstr(oconnpass)) {valid=0; offender=offender||oconnpass;}

	//add more validation rules
	
	if (!valid) {
		if (offender&&offender.focus) offender.focus();
		return;
	}
	
	var connname=encodeHTML(oconnname.value);
	var conntype=encodeHTML(oconntype.value);
	var connhost=encodeHTML(oconnhost.value);
	var conndbname=encodeHTML(oconndbname.value);
	var connapiport=encodeHTML(oconnapiport.value);
	var connuser=encodeHTML(oconnuser.value);
	var connpass=encodeHTML(oconnpass.value);
	
	var params=[];
	params.push('connname='+connname);
	params.push('conntype='+conntype);
	params.push('connhost='+connhost);
	params.push('conndbname='+conndbname);
	params.push('connapiport='+connapiport);
	params.push('connuser='+connuser);
	params.push('connpass='+connpass);

	
	reloadtab('conn_'+connid,'','updateconn&connid='+connid,function(){
		reloadview('codegen.conns','connlist');
		flashstatus(document.dict['statusflash_updated']+oconnname.value,5000);
	},params.join('&'),null,gskey);
	
}


delconn=function(connid,gskey){
	if (!sconfirm(document.dict['confirm_conn_delete'])) return;
	
	reloadtab('conn_'+connid,null,'delconn&connid='+connid,function(){
		closetab('conn_'+connid);
		reloadview('codegen.conns','connlist');
	},null,null,gskey);
}


setactiveconn=function(connid){
	ajxpgn('statusc',document.appsettings.codepage+'?cmd=setactiveconn&connid='+connid,0,0,null,function(){
		//reloadview('codegen.conns','connlist');
		showview('sqldash.databases');
		refreshtab('welcome',1);
	});	
}

makeconnbutton=function(){
	var c=gid('newconnbuttons');
	c.innerHTML='';
	gid('newconnhelp').innerHTML='';
	
	var appdomain=gid('appdomain_new').value;
	var appclientid=gid('appclientid_new').value;
	var appredirurl=gid('appredirurl_new').value;
	
	if (appdomain==''||appclientid==''||appredirurl=='') return;
	
	var url='https://'+appdomain+'/services/oauth2/authorize?client_id='+appclientid+'&redirect_uri='+encodeHTML(appredirurl)+'&response_type=code';
	
	c.innerHTML=' &nbsp; &nbsp; <a class="button" target=_blank href="'+url+'">Authorize</a> &nbsp;';
	c.innerHTML+=' <a class="button" onclick="showconnhelp();">Template</a>';

}

showconnhelp=function(){
	
	var conntype=gid('conntype_new').value;
	
	var appdomain=encodeHTML(gid('appdomain_new').value);
	var appclientid=encodeHTML(gid('appclientid_new').value);
	var appsecret=encodeHTML(gid('appsecret_new').value);
	var appredirurl=encodeHTML(gid('appredirurl_new').value);
	
	var params=[];
	params.push('appdomain='+appdomain);
	params.push('appclientid='+appclientid);
	params.push('appsecret='+appsecret);
	params.push('appredirurl='+appredirurl);
	
	
	ajxpgn('newconnhelp',document.appsettings.codepage+'?cmd=showconnhelp&conntype='+conntype,0,0,params.join('&'));
	
}