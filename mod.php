<?php

function printActMain($mes){

global $game;

$game->player->turnlog = $game->readGameLog($game->player->id);

if(preg_match('/^k.*/',$mes)) {
	$mes = "<font color=\"blue\"><b>".trim($mes,"k")."</b></font><br>\n";
} else if($mes != ""){
	$mes = "<font color=\"red\"><b>ERROR��".$mes."</b></font><br>\n";
}

print <<< DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<script type="text/javascript" src="cauldron.js"></script>
<script type="text/javascript">

DOC_END;
$i=0;
print("matname = new Array(7);\n");
foreach($game->matdata as $data){
	print("matname[".$i."] = \"".$data["name"]."\";\n");
	$i++;
}
print("matnum = new Array(");
$str = "";
foreach($game->player->material as $mat){
	$str = $str.$mat.",";
}
print(rtrim($str,",").");\n");

print <<< DOC_END
</script>
</head>
<body>
<form name="logoutbox" action="main.php" method="POST">
<input type="hidden" name="mode" value="logout">
</form>
<a href="javascript:void(0)" onclick="document.logoutbox.submit();">��������</a><br>
<a href="./readme.txt" target="_">readme!</a><br><br>

DOC_END;

if($game->player->pagemode == "perspective"){
print("<form name=\"actbox\" action=\"main.php\" method=\"POST\">");
print <<< DOC_END
<input type="submit" value="���"><br>
<input type="hidden" name="mode" value="select">
<input type="hidden" name="act" value="back">
</form>

DOC_END;
printPerspective();
print "</body>\n</html>";
exit;
}

print $game->day."����";
if($game->rainy) print "(��)";
else print "(����)";
print "������:".$game->moon;
if($game->moon == 0) print "(����)";
else if($game->moon == 4) print "(����)";
print "<br>\n";
print "�����:".$game->player->gold."G<br>\n";
$dp = $game->daily[0];
print "���������ؤ�ꡧ".$game->potdata[$dp]["name"]."�Υݡ������(".$game->potdata[$dp]["buy"]."G)<br><br>\n";
if($mes != "") print $mes."<br>\n";

if(preg_match('/end([0-9&]+)/', $game->status, $match)){
	$winners = preg_split('/,/', $match[1]);
	$winflag = FALSE;
	foreach($winners as $w){
		if($w == $game->player->no) $winflag = TRUE;
	}
	if($winflag){
		print("<h2>���ʤ��ϡإ��ꥯ�����٤�ȯ���ԤȤ���ǧ����ޤ�����</h2>\n");
	} else {
		print("<h2>".$game->userdata[$match[1]]->name."���إ��ꥯ�����٤�Ĵ����������ޤ�����</h2>\n");
	}
} else {

if($game->player->pagemode == "usepotion"){
	printActPotion();
} else {
switch($game->player->action){
	case "action":
		printActNone();
		break;
	case "gather":
		printActGather();
		break;
	case "compound":
		printActCompound();
		break;
	case "shop":
		printActShop();
		break;
	case "end":
		printActEnd();
		break;
	default:
		print "error:printActMain<br>\n";
		break;
}
}

	print("�������λ�����ץ쥤�䡼��");
	foreach($game->userdata as $u){
		if($u->action == "end") print $u->name."��";
	}
	print("<br>\n�������λ���Ƥ��ʤ��ץ쥤�䡼��");
	foreach($game->userdata as $u){
		if($u->action != "end") print $u->name."��";
	}
}

print <<< DOC_END
<br><br>
<table border=1>
<tr>
<td><b>�Ǻ�</b></td>
<td><b>�ݡ������</b></td>
</tr><tr>
<td valign="top">

DOC_END;
printMaterials();
print("</td>\n<td valign=\"top\">\n");
printPotions();

print <<< DOC_END
</td>
</tr>
</table>
DOC_END;
printPotionTable();
print <<< DOC_END
<table>
<tr><td>����</td><td>����</td></tr>
<tr>
<td valign="top">
DOC_END;

printPlayerLog("gamelog.log");
print "</td><td valign=\"top\">";
printPlayerLog("prevlog.log");

print <<< DOC_END
</td>
</tr>
</table>
DOC_END;

print("</body>\n</html>");

}

function printActNone(){

global $game;

print("<form name=\"actbox\" action=\"main.php\" method=\"POST\">");
if(!$game->checkUgly()) print "<input type=\"radio\" name=\"act\" value=\"shop\">���عԤ�<br>";
else print "<input type=\"radio\" name=\"act\" disabled>(�����μ����Τ����ǳ��˹Ԥ��ޤ���)<br>";
print "<input type=\"radio\" name=\"act\" value=\"compound\">Ĵ��<br>";
if(!$game->rainy) print "<input type=\"radio\" name=\"act\" value=\"gather\">�Ǻླྀ��˹Ԥ�<br>";
else print "<input type=\"radio\" name=\"act\" disabled>(���������Ǻླྀ��ϤǤ��ޤ���)<br>";

printOption();

print <<< DOC_END
<input type="radio" name="act" value="end">������λ<br>
<input type="submit" value="�¹�">
<input type="hidden" name="mode" value="select">
</form>
DOC_END;

}

function printActGather(){

global $game;

print("�Ǻླྀ��˽ФƤ��ޤ�...<br><br>");
print("<form name=\"actbox\" action=\"main.php\" method=\"POST\">");

printOption();

print <<< DOC_END
<input type="radio" name="act" value="end">������λ<br>
<input type="submit" value="�¹�">
<input type="hidden" name="mode" value="select">
</form>
DOC_END;

}

function printActShop(){

global $game;

print <<< DOC_END
<form name="actbox" action="main.php" method="POST">
<input type="radio" name="act" value="buy">�㤤ʪ�򤹤�<br>
<select id="buybox" name="buybox">
<option value="none">--����--

DOC_END;

$buy = "buy";
$sell = "sell";
if($game->player->nego) {$buy = "sprice"; $sell = "sprice";}
if($game->moon == 0) $buy = "sprice";
for($i=0;$i<4;$i++){
	print("<option value=5".$i.">".$game->matdata[$i]["name"]."(".$game->matdata[$i][$buy]."G)\n");
}
for($i=0;$i<2;$i++){
	$dp[$i] = $game->daily[$i];
	print "<option value=".$dp[$i].">".$game->potdata[$dp[$i]]["name"]."�Υݡ������(".$game->potdata[$dp[$i]][$buy]."G)\n";
}

for($i=0;$i<2;$i++){
	if($game->player->caul[$i] < MAX_CAULDRON){
		if($game->player->caul[$i] == 0){
			print "<option value=\"10".$i."\">�������ĥ����(".PRICE_CAULDRON1."G)\n";
		} else if($game->player->caul[$i] == 1) {
			print "<option value=\"10".$i."\">��".($i+1)."���礭������(".PRICE_CAULDRON2."G)\n";
		} else {
			print "<option value=\"10".$i."\">��".($i+1)."���礭������(".PRICE_CAULDRON3."G)\n";
		}
	}
}
print <<< DOC_END
</select>
<input type="text" name="buyint" size="2" value="1">��<br>
<input type="radio" name="act" value="sell">����ʪ�����<br>
<select id="sellbox" name="sellbox">
<option value="none">--����--

DOC_END;

for($i=0;$i<MATERIAL_NO;$i++){
	if($game->player->material[$i] > 0){
		if($game->matdata[$i][$sell] == 0) continue;
		print("<option value=5".$i.">".$game->matdata[$i]["name"]."(".$game->matdata[$i][$sell]."G)\n");
	}
}
for($i=0;$i<POTION_NO;$i++){
	if($game->player->potion[$i] > 0){
		if($game->potdata[$i][$sell] == 0) continue;
		print("<option value=".$i.">".$game->potdata[$i]["name"]."�Υݡ������(".$game->potdata[$i][$sell]."G)\n");
	}
}

print <<< DOC_END
</select>
<input type="text" name="sellint" size="2" value="1">��<br>
<input type="radio" name="act" value="order">�Ǻླྀ������<br>
������=������*5G ������:
<input type="text" name="order" size="2" value="1"><br>
DOC_END;

printOption();

print <<<DOC_END
<input type="radio" name="act" value="end">������λ<br>
<input type="submit" value="�¹�">
<input type="hidden" name="mode" value="select">
</form>

DOC_END;
}

function printActCompound(){

global $game;

$mats = $game->player->getMaterial();

print "<form name=\"actbox\" action=\"main.php\" method=\"POST\">\n";

if($game->player->checkCauldronNum()){

if($game->player->pagemode == "mixpotion"){
	$type = array("��","��","��","��");
	$value = array("1","2","3");
	$potnum = 0;
	
	print "<select id=\"selectcaul\" name=\"selectc\">\n<option value=\"0\">��1";
	if($game->player->caul[1] > 0) print("<option value=\"1\">��2");
	print("</select>\n<br>\n");

	for($i=0;$i<POTION_NO;$i++){
		if($game->player->potion[$i] > 0){
			print("<input type=\"checkbox\" name=\"pot".$i."\">".$game->potdata[$i]["name"]."(".$type[$game->potdata[$i]["type"]]."-".$value[$game->potdata[$i]["value"]].")<br>\n");
			$potnum++;
		}
	}
	if($potnum == 0) print("<input type=\"radio\" name=\"act\" disabled>�ݡ�����󤬤ҤȤĤ⤢��ޤ���<br>\n");
	else print("<input type=\"radio\" name=\"act\" value=\"comp_pot\" checked>�����Υݡ�������Ĵ�礹��<br>\n");
	print("<input type=\"radio\" name=\"act\" value=\"back\">���<br>");
} else {
print <<<DOC_END
<input type="radio" name="act" value="comp_mat" checked>��������Ĵ�礹��<br>
<select id="selectcaul" name="selectc">
<option value="0">��1

DOC_END;

if($game->player->caul[1] > 0) {
	print("<option value=\"1\">��2");
}

print("</select>\n<br>\n");
print <<<DOC_END
<select id="mat1" name="mat1">
<option value="none">�Ǻ�����򤷤Ƥ�������

DOC_END;
	
	for($i=0;$i<MATERIAL_NO;$i++){
		if($i != MIMIZU && $mats[$i] > 0){
			print("<option value=".$i.">".$game->matdata[$i]["name"]."\n");
		}
	}
	print("</select>\n<br><select id=\"mat2\" name=\"mat2\">\n<option value=\"none\">�Ǻ�����򤷤Ƥ�������\n");

	for($i=0;$i<MATERIAL_NO;$i++){
		if($i != MIMIZU && $mats[$i] > 0){
			print("<option value=".$i.">".$game->matdata[$i]["name"]."\n");
		}
	}
	if($mats[5] > 0){
		for($i=0;$i<5;$i++){
			print("<option value=1".$i.">".$game->matdata[5]["name"]."(".$game->matdata[$i]["name"]."�Ȥ���)\n");
		}
	}
	print("</select>\n");

	if($game->player->caul[0] == 3 || $game->player->caul[1] == 3){
		print "<input type=\"checkbox\" name=\"double\">���ܤ�ʬ�̤�Ĵ��<br>\n";
	} else {print "<br>\n";}

	if($game->hasPotion())
	print("\n\n<input type=\"radio\" name=\"act\" value=\"mixpotion\">�ݡ������Ʊ�Τ�Ĵ��<br>");
}
} else {
	print ("<input type=\"radio\" name=\"act\" disabled>������ƻ�����Ǥ�<br>");
}

printOption();

print <<< DOC_END
<input type="radio" name="act" value="end">������λ<br>
<input type="submit" value="�¹�">
<input type="hidden" name="mode" value="select">
</form>
DOC_END;

printCauldron();
print "<br>\n";

}

function printActPotion() {

global $game;

print "<form name=\"actbox\" action=\"main.php\" method=\"POST\">\n";
print "<select name=\"potno\">\n<option value=\"none\">--����--\n";

for($i=0;$i<POTION_NO;$i++){
	if($game->player->potion[$i] > 0) {
		print "<option value=".$i.">".$game->potdata[$i]["name"]."\n";
	}
}

print <<<DOC_END
</select><br>
<input type="radio" name="act" value="potion" checked>���Ѥ���<br>
<input type="radio" name="act" value="back">���<br>
<input type="submit" value="�¹�">
<input type="hidden" name="mode" value="select">
</form>

DOC_END;

}

function printActEnd(){

global $game;

switch($game->player->selectact){
	case "gather":
		$action = "�������Ǻླྀ���Ԥ��ޤ�����<br>\n";
		break;
	case "shop":
		$action = "�����ϳ��˽гݤ��ޤ�����<br>\n";
		break;
	case "compound":
		$action = "������Ĵ���Ԥ��ޤ�����<br>\n";
		break;
	case "none":
		$action = "�����ϲ��⤷�ޤ���Ǥ�����<br>\n";
		break;	
	default:
		$action = "error printActEnd (".$game->player["selectact"].")<br>\n";
		break;

}

print($action."<br>\n");
print <<<DOC_END
�ʴ��˥������λ���Ƥ��ޤ���<br><br>
<form name="actbox" action="main.php" method="POST">
<input type="submit" value="����">
<input type="hidden" name="mode" value="reload">
</form>
DOC_END;
}

#���ɽ��
function printCauldron(){

global $game;

$size = array("","��","��","����");
print("<table border=1>\n<tr>\n");
print("<td><b>��1(".$size[$game->player->caul[0]].")</b></td>\n");
if($game->player->caul[1] > 0){
	print("<td><b>��2(".$size[$game->player->caul[1]].")</b></td>\n");
}
print "</tr>\n<tr>\n";
$i=0;
$str = array("","");
foreach($game->player->turnlog as $l){
	if($i >= 2) break;
	if(preg_match('/compound\(([0-9]),([01]),([0-9]+),([0-9]+)\)/',$l,$match)){
		$str[$match[1]] = $str[$match[1]]."<td>\n".$game->matdata[$match[3]]["name"]."<br>";
		if($match[4] >= 10){
			$str[$match[1]] = $str[$match[1]]."(".$game->matdata[intval($match[4])-10]["name"].")\n";
		} else {
			$str[$match[1]] = $str[$match[1]].$game->matdata[$match[4]]["name"]."\n";
		}
		if($match[2] == "1"){
			$str[$match[1]] = $str[$match[1]]."<br>(����)\n";
		}
		$str[$match[1]] = $str[$match[1]]."</td>\n";
		$i++;
	} else if(preg_match('/compound\(([0-9]),(.+)\)/',$l,$match)){
		switch($match[2]){
			case "beat":
				$matno = BEAT;
				break;
			case "primeval":
				$matno = PRIMEVAL;
				break;
			case "elixir":
				$matno = ELIXIR;
		}
		$str[$match[1]] = $str[$match[1]]."<td>\n(".$game->matdata[$matno]["name"].")<br>\n</td>\n";
		$i++;
	}
}


for($i=0;$i<2;$i++){
	if($str[$i] == "" && $game->player->caul[$i] > 0) print("<td>(����)</td>\n");
	else if($game->player->caul[$i] <= 0) {}
	else print($str[$i]);
}
print("</tr>\n</table>\n");

}

#Ĵ��ɽ��ɽ��
function printPotionTable(){
	
global $game;

$type = array("��","��","��","��");
$value = array("1","2","3");

print("\n<br>\n<b>Ĵ��ɽ</b>\n<br>\n<table border=2>\n<tr>\n<td></td>\n");
for($i=0;$i<5;$i++){
	print("<td>".$game->matdata[$i]["name"]."</td>\n");
}
print("</tr>\n");
for($i=0;$i<5;$i++){
	print("<tr>\n<td>".$game->matdata[$i]["name"]."</td>\n");
	for($j=0;$j<5;$j++){
		if(!isset($game->player->ptable[$i][$j])){
			print("<td>��</td>\n");
			continue;
		}
		if($game->potiontable[$i][$j] == 100){
			print("<td>����</td>\n");
		} else {
			$potno = $game->potiontable[$i][$j];
			print("<td>".$game->potdata[$potno]["name"]."(".$type[$game->potdata[$potno]["type"]]."-".$value[$game->potdata[$potno]["value"]].")</td>\n");
		}
	}
	print("</tr>\n");
}
print("</table>\n");

}

#�Ǻ��ɽ��
function printMaterials(){

	global $game;

	for($i=0;$i<MATERIAL_NO;$i++){
		if(isset($game->player->material[$i]) && $game->player->material[$i] != 0){
			echo($game->matdata[$i]["name"]."*".$game->player->material[$i]."<br>\n");
		}
	}
}

#�ݡ�������ɽ��
function printPotions(){

	global $game;

	$type = array("��","��","��","��");
	$value = array("1","2","3");
	
	for($i=0;$i<POTION_NO;$i++){
		if(isset($game->player->potion[$i]) && $game->player->potion[$i] != 0){
			echo($game->potdata[$i]["name"]."(".$type[$game->potdata[$i]["type"]]."-".$value[$game->potdata[$i]["value"]].")*".$game->player->potion[$i]."<br>\n");
		}
	}
}

#Ʃ��⡼��
function printPerspective(){

global $game;
$larray = array();
foreach($game->readGameLogAll() as $l) {
	if(preg_match('/^(.+):(.+)$/',$l,$match)){
		$tarray = makeLog($match[2],"",array());
		if($tarray[0] != "") array_push($larray,$game->getUserName($match[1]).":".$tarray[0]);
	}
}
foreach($larray as $l) print $l."<br>\n";

}

#�ץ쥤�䡼��
function printPlayerLog($file){

global $game;

if($file == "prevlog.log") $st = "yes";
else $st = "today";

$fp = fopen($file, "r");
$logdata = array();
while($line = fgets($fp)) {
	array_push($logdata, $line);
}
fclose($fp);
$printlog = exLog($logdata,$game->player->id,$st);
foreach($printlog as $l){print $l."<br>\n";}

}

#���������
function printLogin($err){

global $game;

if($err != ""){
	$err = "<font color=\"red\"><b>ERROR��".$err."</b></font><br>\n";
}

print <<<DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<body>
<a href="./readme.txt" target="_">�Ϥ���ˤ��ɤߤ�������</a><br><br>

DOC_END;

print $err."<br>\n";

print <<<DOC_END
������
<form name="ibox" action="./main.php" method="POST">
ID<input type="text" name="id"><br>
pass<input type="password" name="pass"><br>
<input type="hidden" name="mode" value="login">
<input type="submit" value="������">
</form><br>

DOC_END;

if($game->status == "0") {

print <<<DOC_END
������Ͽ
<form name="sbox" action="./main.php" method="POST">
ID(Ⱦ�ѱѿ�4��16ʸ��)<br><input type="text" name="id"><br>
̾��(1��16ʸ��)<br><input type="text" name="name"><br>
pass(Ⱦ�ѱѿ�4��16ʸ��)<br><input type="password" name="pass"><br>
<input type="hidden" name="mode" value="signup">
<input type="submit" value="��Ͽ">
</form>

DOC_END;
} else {
print ("�����ߡ�������Ͽ�ϼ����դ��Ƥ��ޤ���\n");
}

print "</body>\n</html>\n";
return;
}

#�����ԥ��������
function printAdmin(){

print <<<DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<body>
<form name="adbox" action="./main.php" method="POST">
<input type="password" name="pass"><br>
<input type="hidden" name="mode" value="admin">
<input type="submit" value="�����ԥ⡼��">
</form><br>
</body>
</html>

DOC_END;
}

#�����ԥ⡼�ɲ���
function printAdminMode(){

print <<<DOC_END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<body>
<form name="adbox" action="./main.php" method="POST">
��������<input type="radio" name="act" value="start"><br>
�ꥻ�å�<input type="radio" name="act" value="reset"><br>
<input type="hidden" name="mode" value="adminmode">
<input type="submit" value="�¹�">
</form><br>
</body>
</html>

DOC_END;
}

#���򤢤줳�줹��
function exLog($logdata,$id,$st){

global $game;

$rarray = array();
$mats = array();
foreach($logdata as $l){
	$temp = "";
	#���蘆�Υ�
	if(preg_match('/^uwasa>(.+)\.(.+)\(([0-9]+)\)$/', $l, $match)) {
		switch($match[2]){
			case "table":
				$temp = "����".$game->getUserName($match[1])."��Ĵ��ɽ��".$match[3]."�ս���ޤäƤ���";
				break;
			case "gold":
				$temp = "����".$game->getUserName($match[1])."��".$match[3]."G���äƤ���";
				#echo $temp;
				break;
			case "use":
				$temp = "����".$game->getUserName($match[1])."�Ϻ�����".$game->potdata[$match[3]]["name"]."�Υݡ�������Ȥä�";
				break;
		}
		$action = "";
	#��ʬ�ʳ��Υ�
	} else if(!preg_match('/^'.$id.':(.+)/', $l, $match)){
		if($l == "result\n"){
			$temp = "-----";
		} else if(preg_match('/^(.+):action\.(.+)/', $l, $match) && $st != "today") {
			$uname = $game->getUserName($match[1]);
			switch($match[2]){
				case "gather":
					$temp = $uname."���Ǻླྀ��˹Ԥä��褦�Ǥ�";
					break;
				case "shop":
					$temp = $uname."�ϳ��ؽгݤ����褦�Ǥ�";
					break;
				case "compound":
					$temp = $uname."��Ĵ���Ԥä��褦�Ǥ�";
					break;
			}
		} else if(preg_match('/^(.+):get material\(([0-9])\)/', $l, $match) && $st != "today") {
			$uname = $game->getUserName($match[1]);
			if($match[2] == PRIMEVAL || $match[2] == BEAT || $match[2] == ELIXIR){
				$temp = $uname."��".$game->matdata[$match[2]]["name"]."��Ĵ������������褦�Ǥ�";
			}
		} else if(preg_match('/^(.+):use potion\(([0-9]+)\)/', $l, $match) && $st != "today") {
			if($match[2] == BUKIYOU || $match[2] == SYUAKU) $temp = "ï����".$game->potdata[$match[2]]["name"]."�Υݡ���������Ѥ����褦�Ǥ�";
		}
		$action = "";
		
	#��ʬ�Υ�
	} else {
		$action = $match[1];
	}
	
	if(isset($action) && $action != ""){
		$tarray = makeLog($action,$st,$mats);
		$temp = $tarray[0];
		$mats = $tarray[1];
	}
	if($temp == "") continue;
	array_push($rarray,$temp);
}

if(sizeof($mats) > 0){
	for($i=0;$i<MATERIAL_NO;$i++){
		if(isset($mats[$i])){
			array_push($rarray,$game->matdata[$i]["name"]."*".$mats[$i]."������");
		}
	}
}

return $rarray;
}

#������
function makeLog($action,$st,$mats){

global $game;
	$temp = "";
	if(preg_match('/get material\(([0-9]+)\)/',$action,$match)){
		if(isset($mats[$match[1]])) {$mats[$match[1]] += 1;}
		else  {$mats[$match[1]] = 1;}
	} else if(preg_match('/get potion\(([0-9]+),([0-9]+)\)/',$action,$match)){
		$temp = $game->potdata[$match[1]]["name"]."�Υݡ������*".$match[2]."������";
	} else if(preg_match('/compound (.+)\(([0-9]+),([0-9]+)\)/',$action,$match)){
		$mat1 = $game->matdata[$match[2]]["name"];
		if($match[3] >= 10){
			$mat2 = "(".$game->matdata[$match[3]-10]["name"].")";
		} else {
			$mat2 = $game->matdata[$match[3]]["name"];
		}
		if($match[1] == "success"){$mes = "����";}
		else if($match[1] == "fail"){$mes = "����";}
		$temp = $mes.":".$mat1."+".$mat2;
	} else if(preg_match('/order\(([0-9]+)\)/',$action,$match)) {
		$temp = "�Ǻླྀ������(".$match[1].")";
	} else if(preg_match('/^action\.(.+)/',$action,$match)) {
		switch($match[1]){
			case "gather":
				if($st == "today") $temp = "�Ǻླྀ��˹Ԥ����Ȥˤ��ޤ���";
				else $temp = "�Ǻླྀ��˹Ԥ��ޤ���";
				break;
			case "shop":
				$temp = "���ؽгݤ��ޤ���";
				break;
			case "compound":
				if($st == "today") $temp = "Ĵ���Ԥ����Ȥˤ��ޤ���";
				else $temp = "Ĵ���Ԥ��ޤ���";
				break;
		}
	} else if(preg_match('/^use potion\(([0-9]+)\)/',$action,$match)) {
		$temp = $game->potdata[$match[1]]["name"]."�Υݡ����������";
	} else if(preg_match('/^open\(([0-9])([0-9])\)/',$action,$match)) {
		$temp = $game->matdata[$match[1]]["name"]."��".$game->matdata[$match[2]]["name"]."��Ĵ���פ��Ĥ��ޤ���";
	} else if(preg_match('/^open\(none\)/',$action,$match)) {
		$temp = "����פ��Ĥ��ޤ���Ǥ���";
	}
	return array($temp,$mats);
}

function printOption() {

global $game;

if($game->hasPotion())
print("<input type=\"radio\" name=\"act\" value=\"potion\">�ݡ������λ���<br>");

foreach($game->readGameLog($game->player->id) as $l) {
	if(preg_match('/use potion\('.TOSI.'\)/',$l)) {
		print("<input type=\"radio\" name=\"act\" value=\"perspective\">Ʃ�뤹��<br>");
		break;
	}
}

}

function array_flatten($array){
	$tmp = array();
	if(is_array($array)) foreach($array as $val) $tmp = array_merge($tmp, array_flatten($val));
	else $tmp[] = $array;
	return $tmp;
}



?>