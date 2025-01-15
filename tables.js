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

update_table_comment=function(tablename,dbname){
	var comment=encodeHTML(gid('tablecomment_'+dbname+'_'+tablename).value);
	reloadtab('table_'+dbname+'_'+tablename,'','update_table_comment&dbname='+dbname+'&tablename='+tablename,null,'comment='+comment);
}

update_col_comment=function(d,dbname,tablename,colname){
	var comment=encodeHTML(d.value);
	reloadtab('table_'+dbname+'_'+tablename,'','update_col_comment&dbname='+dbname+'&tablename='+tablename+'&colname='+colname,null,'comment='+comment);	
}
