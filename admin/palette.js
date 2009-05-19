bloc = true;
function hexa(couleur)
{
	if(bloc)
		document.form.hexval.value = couleur;
}
function palette() { 
document.write("<TABLE border='0' cellpadding='0' cellspacing='0' ><TR>"); 
var h=new Array('00','33','66','99','CC','FF'); 
var col=""; 
for(var i=0;i<6;i++) { 
for(var j=0;j<6;j++) { 
for(var k=0;k<6;k++) { 
col="#"+h[i]+h[j]+h[k]; 
document.write("<TD width='10' height='10' bgcolor='"+col+"' onMouseOver=\"hexa('"+col+"')\" onClick=\"if(bloc) { bloc = false; } else { bloc = true; }\"></TD>"); 
} 
} 
document.write("</tr>"); 
} 
document.write("</TABLE>"); 
} 
