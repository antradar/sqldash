_inline_lookupdatabase=function(d){
	if (d.timer) clearTimeout(d.timer);
	d.timer=setTimeout(function(){
		ajxpgn('databaselist',document.appsettings.codepage+'?cmd=slv_sqldash__databases&mode=embed&key='+encodeHTML(d.value));
	},300
	);	
}

setdatabase=function(dbname){
	var c=gid('topicons');

	document.dbname=dbname;
	ajxpgn('statusc',document.appsettings.codepage+'?cmd=setdatabase&dbname='+dbname,0,0,'',function(){
		settabtitle('welcome','<img src="imgs/t.gif" class="ico-database">'+dbname,{noclose:1});
		reloadtab('welcome',null,'wk');
		showview('sqldash.tables');
	});
}

setlitedb=function(dbfn){
	addtab('litedb_'+dbfn,dbfn,'slite_showdb&dbfn='+dbfn);	
}