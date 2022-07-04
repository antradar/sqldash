_inline_lookuptable=function(d){
	if (d.timer) clearTimeout(d.timer);
	d.timer=setTimeout(function(){
		ajxpgn('tablelist',document.appsettings.codepage+'?cmd=slv_sqldash__tables&mode=embed&key='+encodeHTML(d.value));
	},300
	);	
}


showtable=function(tablename,dbname){
	addtab('table_'+dbname+'_'+tablename,'<img src="imgs/t.gif" class="ico-table">'+tablename, 'showtable&tablename='+tablename+'&dbname='+dbname);	
}