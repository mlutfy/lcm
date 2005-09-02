<?
/*

    This file is part of Trad-Lang.

    Trad-Lang is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Trad-Lang is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Trad-Lang; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

    Copyright 2003 
        Florent Jugla <florent.jugla@eledo.com>, 
        Philippe Rivière <fil@rezo.net>

*/
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

 <HTML>
  <HEAD>
    <TITLE><?php echo $titre; ?></TITLE>
    <META HTTP-EQUIV="Expires" CONTENT="0">
    <META HTTP-EQUIV="cache-control" CONTENT="no-cache,no-store">
    <META HTTP-EQUIV="pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">

<style>
A {
text-decoration : none;
}
A:Hover {color:#FF9900; text-decoration: underline;}
.forml {width: 100%; background-color: #FFCC66; background-position: center bottom; float: none;color: #000000}
.formo {width: 100%; background-color: #970038; background-position: center bottom; float: none;color: #FFFFFF}
.fondl {background-color: #FFCC66; background-position: center bottom; float: none; color: #000000}
.fondo {background-color: #970038; background-position: center bottom; float: none; color: #FFFFFF}
.fondf {background-color: #FFFFFF; border-style: solid ; border-width: 1; border-color: #E86519;color: #E86519}

.t {
font-size: 9pt;
text-align : center;
font-family: Verdana;
}
.t2 {
font-size: 8pt;
text-align : center;
font-family: Verdana;
}
.n {
  font-family: Fixedsys
}
.s {
font-size: 10pt;
text-align : right;
font-family: Verdana;
}
.sy {
font-family: Fixedsys;
}
.s2 {
font-family: Fixedsys;
color: red;
}
.tab {
font-size: 10pt;
text-align : center;
font-family: Verdana;
background: #cccccc;
}
.tr {
background: #ffffff;
}
</style><style>
.title {
color: 'black';
background: #D4D0C8;
text-align: 'center';
BORDER-RIGHT:   #888888 1px outset;
BORDER-TOP:     #ffffff 2px outset;
BORDER-LEFT:    #ffffff 1px outset;
BORDER-BOTTOM:  #888888 1px outset;
}
.window {
BORDER-RIGHT:  buttonhighlight 2px outset;
BORDER-TOP:    buttonhighlight 2px outset;
BORDER-LEFT:   buttonhighlight 2px outset;
BORDER-BOTTOM: buttonhighlight 2px outset;
FONT: 8pt Tahoma, Verdana, Geneva, Arial, Helvetica, sans-serif;
BACKGROUND-COLOR: #D4D0C8;
CURSOR: default;
}
.window1 {
BORDER-RIGHT:  #eeeeee 1px solid;
BORDER-TOP:    #808080 1px solid;
BORDER-LEFT:   #808080 1px solid;
BORDER-BOTTOM: #eeeeee 1px solid;
FONT: 8pt Tahoma, Verdana, Geneva, Arial, Helvetica, sans-serif;
}
.line {
BORDER-RIGHT:   #cccccc 1px solid;
BORDER-TOP:     #ffffff 1px solid;
BORDER-LEFT:    #ffffff 1px solid;
BORDER-BOTTOM:  #cccccc 1px solid;
/*background: #eeeeee;*/
font: 9pt Tahoma;
}

.line_pres {
BORDER-RIGHT:   #cccccc 1px solid;
BORDER-TOP:     #ffffff 1px solid;
BORDER-LEFT:    #ffffff 1px solid;
BORDER-BOTTOM:  #cccccc 1px solid;
/*background: #eeeeee;*/
font: 11pt Tahoma;
}

.line_aff {
font: 12pt Tahoma;
}
.line2 {
background: #ffffcc;
}
.black {color: black}
a:link.black {color: black}
a:active.black {color: black}
a:visited.black {color: black}
a:hover.black {color: #0000ff}

.white {color: white}
a:link.white{color: white}
a:active.white{color: white}
a:visited.white{color: white}
a:hover.white{color: #ffff77}

a:link     {color: #000000;}
a:active   {color: #000000;}
a:visited  {color: #000000;}
a:hover    {color: #000000;}
a {
CURSOR: default;
}

.windowtitle {
font-size: 12pt; 
font: Tahoma, Verdana, Geneva, Arial, Helvetica, sans-serif;
font-weight: bold;
color: white;
}
.bleu {
# background: #fbfbff;
}
.vert {
# background: #fbfffb;
}
.or {
# background: #ffedd8;
}
</style>

<script language="javascript">
<!--
function ouvrirfen(x,y,url)
{
  window.open(url,'_blank','toolbar=0,location=0,directories=0,status=0,scrollbars=1,resizable=0,copyhistory=0,menuBar=0,width='+x+',height='+y);
  return(false);
}
-->
</script>

</HEAD>

<body dir="<?php echo $direction; ?>">

  <CENTER>
