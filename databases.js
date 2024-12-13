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

showdbprocesses=function(){
	addtab('dbprocesses','DB Processes', 'showdbprocesses');	
}

_inline_lookupdbprocess=function(d,forced){
	var delay=500;
	if (forced) {
		d.lastkey=null;
		delay=0;
	}
	if (d.lastkey!=null&&d.lastkey==d.value) return;
	d.lastkey=d.value;
	if (d.timer) clearTimeout(d.timer);
	d.timer=setTimeout(function(){
		ajxpgn('dbprocesses',document.appsettings.codepage+'?cmd=showdbprocesses&mode=embed&key='+encodeHTML(d.value));
	},delay
	);	
}

killdbprocess=function(procid){
	if (!sconfirm('Are you sure you want to terminate Process #'+procid+'?')) return;
	
	ajxpgn('dbprocesses',document.appsettings.codepage+'?cmd=killdbprocess&mode=embed&procid='+procid+'&key='+encodeHTML(gid('dbprocesskey').value));	
}

killalldbprocesses=function(){
	if (!sconfirm('Are you sure you want to terminate ALL the matching processes?')) return;
	var procids=[];
	var os=gid('dbprocesses').getElementsByClassName('dbprocid');
	for (var i=0;i<os.length;i++) procids.push(os[i].value);
	
	ajxpgn('dbprocesses',document.appsettings.codepage+'?cmd=killalldbprocesses&mode=embed&key='+encodeHTML(gid('dbprocesskey').value),0,0,'procids='+procids.join(','));	
		
}

setlitedb=function(dbfn){
	addtab('litedb_'+dbfn,'<img src="imgs/t.gif" class="ico-sqlite">'+dbfn,'slite_showdb&dbfn='+dbfn);	
}