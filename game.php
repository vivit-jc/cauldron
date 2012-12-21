<?php

class Game{

	var $userdata = array();
	var $matdata = array();
	var $player = array();
	var $potiontable = array();
	var $day;
	var $daily = array();
	var $rainy;
	var $status;
	var $winner;
	var $moon;
	
	function __construct(){
		$this->matdata = $this->readMaterialData();
		$this->potdata = $this->readPotionData();
		$this->userdata = $this->setPlayerData();
		$this->readGameData();
		$this->checkTurnEnd();
	}

	#�����ॹ�����Ȼ��ν���
	function startGame(){
	
		$this->status = "started";
		$fp = fopen("gamedata.log", "r");
		$gamedata = array();
		while($line = fgets($fp)) {
			array_push($gamedata, $line);
		}
		fclose($fp);
		
		$fp = fopen("gamedata.log", "w");
		fwrite($fp, "started,1,".$this->daily[0].",".$this->daily[1].",".$this->rainy.",".$this->moon."\n");
		fwrite($fp, $gamedata[1]);
		fwrite($fp, $gamedata[2]);
		fclose($fp);
	
	}
	
	#������ꥻ�åȻ��ν���
	function resetGame(){
	
		$flat = "";
		$table = $this->makePotionTable();
		foreach($table as $t){
			$flat = $flat.$t.",";
		}
		$flat = rtrim($flat,",");
		
		$fp = fopen("user.log", "w");
		fclose($fp);
		$fp = fopen("prevlog.log", "w");
		fclose($fp);
		$fp = fopen("logall.log", "w");
		fclose($fp);
		$fp = fopen("gamelog.log", "w");
		fclose($fp);
	
		$dp = $this->getDailyPotion();
		
		$fp = fopen("gamedata.log", "w");
		fwrite($fp, "0,1,".$dp[0].",".$dp[1].",0,".mt_rand(0,7)."\n");
		fwrite($fp, $flat."\n");
		fwrite($fp, "\n");
		fclose($fp);
		
		$this->addLog("day:1");
	
	}

	#�Ǻ�ǡ����ɤ߹���
	function readMaterialData(){
		$fp = fopen("material.dat", "r");
		$matnum=0;
		while($line = fgets($fp)) {
			$temparray = preg_split('/,/', rtrim($line));
			$materials[$matnum]["name"] = $temparray[0];
			$materials[$matnum]["gathere"] = $temparray[1];
			$materials[$matnum]["ordere"] = $temparray[2];
			$materials[$matnum]["buy"] = $temparray[3];
			$materials[$matnum]["sell"] = $temparray[4];
			$materials[$matnum]["sprice"] = $temparray[4];
			$materials[$matnum]["no"] = $matnum;
			$matnum++;
		}
		fclose($fp);
		define("MATERIAL_NO",$matnum);
		return $materials;
	}
	
	#�ݡ������ǡ����ɤ߹���
	function readPotionData(){
		$fp = fopen("potion.dat", "r");
		$potnum=0;
		while($line = fgets($fp)) {
			$temparray = preg_split('/,/', rtrim($line));
			$potions[$potnum]["name"] = $temparray[0];
			$potions[$potnum]["buy"] = $temparray[1];
			$potions[$potnum]["sprice"] = $temparray[2];
			$potions[$potnum]["sell"] = $temparray[3];
			$potions[$potnum]["type"] = $temparray[4];
			$potions[$potnum]["value"] = $temparray[5];
			$potions[$potnum]["no"] = $potnum;
			$potnum++;
		}
		fclose($fp);
		define("POTION_NO",$potnum);
		return $potions;
	}

	#Ĵ��ɽ�κ����ʥ����೫�ϻ���
	function makePotionTable(){
		$flat = array();
		for($i=0;$i<15;$i++){
			if($i < POTION_NO){
				array_push($flat, $i);
			} else {
				array_push($flat, 100);
			}
		}
		for($i=0;$i<15;$i++){
			$rnd = mt_rand(0, 14-$i);
			$t = $flat[$i];
			$flat[$i] = $flat[$i+$rnd];
			$flat[$i+$rnd] = $t;
		}
		return $flat;
	}

	#�ץ쥤�䡼�ǡ������ɤ߹���
	function setPlayerData() {
		$fp = fopen("user.log", "r");
		$usernum=0;
		$userdata = array();
		while($line = fgets($fp)) {
			$tempusers[$usernum] = $line;
			$usernum++;
		}
		fclose($fp);
	
		define("USERNUM", $usernum);
	
		for($i=0;$i<USERNUM;$i++){
			$pl = new Player();
			$temp = preg_split('/,/', rtrim($tempusers[$i]));
			$pl->no = $i;
			$pl->id = $temp[0];
			$pl->name = $temp[1];
			$pl->pass = $temp[2];
			$pl->action = $temp[3];
			$tempmat = preg_split('/&/', $temp[4]);
			$temppot = preg_split('/&/', $temp[5]);
			$pl->caul[0] = $temp[7];
			$pl->caul[1] = $temp[8];
			$pl->gold = $temp[9];
			$pl->material = array();
			$pl->potion = array();
			
			for($j=0;$j<MATERIAL_NO;$j++){
				array_push($pl->material,0);
			}
			for($j=0;$j<POTION_NO;$j++){
				array_push($pl->potion,0);
			}
			foreach($tempmat as $mt){
				if(preg_match('/([0-9]+)=([0-9]+)/',$mt,$match)){
					$pl->material[intval($match[1])] = intval($match[2]);
				}
			}
			foreach($temppot as $pr){
				if(preg_match('/([0-9]+)=([0-9]+)/',$pr,$match)){
					$pl->potion[intval($match[1])] = intval($match[2]);
				}
			}
			foreach(preg_split('/&/',$temp[6]) as $tablexy){
				if(rtrim($tablexy) == "") continue;
				$x = $tablexy[0];
				$y = $tablexy[1];
				for($tx=0;$tx<5;$tx++){
					for($ty=0;$ty<5;$ty++){
						if($tx == $x && $ty == $y){
							$pl->ptable[$x][$y] = TRUE;
							$pl->ptable[$y][$x] = TRUE;
						}
					}
				}
			}
			array_push($userdata, $pl);
		}
		return $userdata;
	}

	#�ץ쥤�䡼�ǡ������鼫ʬ�Υǡ����򥳥ԡ�����
	function setPlayer($id) {
		$_SESSION["id"] = $id;
		foreach($this->userdata as $data){
			if($data->id == $id){
				$this->player = $data;
			}
		}
		$this->player->turnlog = $this->readGameLog($id);
		foreach($this->player->turnlog as $l){
			if(preg_match('/use potion\('.KOUSYOUJUTSU.'\)/',$l)) {
				$this->player->nego = 1;
				break;
			}
		}
	}
	
	#�����ƥ�ǡ������ɤ߹���
	function readGameData(){
		$fp = fopen("gamedata.log", "r");
		$i=0;
		$gamedata = array();
		while($line = fgets($fp)) {
			array_push($gamedata, $line);
		}
		fclose($fp);
		
		$sptemp = preg_split('/,/', rtrim($gamedata[0]));
		$this->status = $sptemp[0];
		$this->day = intval($sptemp[1]);
		$this->daily[0] = intval($sptemp[2]);
		$this->daily[1] = intval($sptemp[3]);
		$this->rainy = $sptemp[4];
		$this->moon = $sptemp[5];
		$sptemp = preg_split('/,/', rtrim($gamedata[2]));
		for($i=0;$i<USERNUM;$i++){
			$this->userdata[$i]->selectact = $sptemp[$i];
		}
		$flat = preg_split('/,/', rtrim($gamedata[1]));
		$k=0;
		for($i=0;$i<5;$i++){
			for($j=0;$j<5;$j++){
				if(!isset($this->potiontable[$i][$j])){
					$this->potiontable[$i][$j] = $flat[$k];
					$this->potiontable[$j][$i] = $flat[$k];
					$k++;
				}
			}
		}
	}
	
	#�����ɤ߹���ʥץ쥤�䡼ID����
	function readGameLog($id){
		$i=0;
		$gamelog = array();
		$pldata = array();
		$fp = fopen("gamelog.log", "r");
		while($line = fgets($fp)) {
			array_push($gamelog, $line);
		}
		fclose($fp);
		
		foreach($gamelog as $l){
			if(preg_match('/'.$id.':(.*)/', $l, $match)){
				array_push($pldata, $match[1]);
			}
		}
		return $pldata;
	}
	
	#���������ɤ�
	function readGameLogAll(){
		$i=0;
		$gamelog = array();
		$fp = fopen("gamelog.log", "r");
		while($line = fgets($fp)) {
			array_push($gamelog, rtrim($line));
		}
		fclose($fp);
		
		return $gamelog;
	}

	#�����󤬽�λ�������ɤ��������å�������äƤ������
	function checkTurnEnd() {
		if(!$this->status) return;
		$count = 0;
		foreach($this->userdata as $data){
			if($data->action == "end"){$count++;}
		}
		if($count != USERNUM) return;
		
		$this->addLog("result");
		foreach($this->userdata as &$data){
			$log = $this->readGameLog($data->id);
			$this->checkResistance($data->no,$log);
			foreach($log as $plog){
				$addlog = array();
				$this->resolvePotion($data->no,$plog);
				if(!preg_match('/action\.(.+)/', $plog, $match)){continue;}
				switch($match[1]){
					case "compound":
						$addlog = $data->resolveCompound($log,$this->potiontable);
						break;
					case "gather":
						$addlog = $data->resolveGather($log,$this->matdata,$this->moon);
						break;
					case "shop":
						$addlog = $data->resolveShop($log,$this->matdata);
						break;
				}
				foreach($addlog as $l){
					if($l == "") continue;
					$this->addLog($l);
				}
			}
		}
		
		$this->setWeather();
		$this->day++;
		$tempstr = "";
		for($i=0;$i<USERNUM;$i++){
			$tempstr = $tempstr."none,";
			if($this->userdata[$i]->winflag) $this->winner = $i;
		}
		
		$fp = fopen("gamedata.log", "r");
		$gamedata = array();
		while($line = fgets($fp)) {
			array_push($gamedata, $line);
		}
		fclose($fp);
		
		if(isset($this->winner)){
			$this->status = "end".$this->winner;
		} else {
			$this->status = "started";
		}
		
		$this->daily = $this->getDailyPotion();
		$this->moon = ($this->moon+1)%8;
		
		$fp = fopen("gamedata.log", "w");
		fwrite($fp, $this->status.",".$this->day.",".$this->daily[0].",".$this->daily[1].",".$this->rainy.",".$this->moon."\n");
		fwrite($fp, $gamedata[1]);
		fwrite($fp, rtrim($tempstr,",")."\n");
		fclose($fp);
		
		foreach($this->userdata as &$data){
			$data->action = "action";
		}
		$this->savePlayerData();
		
		#�������Υ���ꥻ�å�
		copy( "./gamelog.log", "./prevlog.log");
		$fp = fopen("gamelog.log", "w");
		fclose($fp);
		$this->addLog("day:".$this->day);
	}
	
	#���ؤ��ݡ��������֤�������Ȥ����֤����Ȥ����
	function getDailyPotion(){
		while(TRUE){
			$daily1 = mt_rand(0,POTION_NO-1);
			if($this->potdata[$daily1]["buy"] != 0) break;
		}
		while(TRUE){
			$daily2 = mt_rand(0,POTION_NO-1);
			if($this->potdata[$daily2]["buy"] != 0 && $daily1 != $daily2) break;
		}
		return array($daily1,$daily2);
	}

	#�ݡ������θ��̤��褹��(checkTurnEnd��������)
	function resolvePotion($no,$log) {
		if(!preg_match('/^use potion\(([0-9]+)\)$/',$log,$match)) return FALSE;
		switch($match[1]){
			case SYUCHU:
				$this->userdata[$no]->conse += 1;
				break;
			case TANSAKU:
				$this->userdata[$no]->search += 1;
				break;
			case BUKIYOU:
				foreach($this->userdata as $u){
					if(!$u->resist && $u->no != $no) $u->awkward += 1;
				}
				break;
			case BOKYAKU:
				foreach($this->userdata as $u){
					if(!$u->resist && $u->no != $no) $u->lostRecipe();
				}
				break;
		}
	}
	
	#�м��Υݡ�����������å�
	function checkResistance($no,$log){
		foreach($log as $l) {
			if(preg_match('/use potion\('.TAIJU.'\)/',$l)){
				$this->userdata[$no]->resist = 1;
				break;
			}
		}
	}
	
	#�����μ��������å�
	function checkUgly(){
	
		$resist = 0;
		$ugly = 0;
		$log = array();
		$fp = fopen("prevlog.log", "r");
		while($line = fgets($fp)) {
			array_push($log, rtrim($line));
		}
		fclose($fp);
		
		foreach($log as $l){
			if(preg_match('/(.+):use potion\(([0-9]+)\)/',$l,$match)){
				if($match[1] == $this->player->id && $match[2] == TAIJU){
					$resist = 1;
				} else if($match[1] != $this->player->id && $match[2] == SYUAKU) {
					$ugly = 1;
				}
			}
		}
		
		if(!$resist && $ugly) return TRUE;
		return FALSE;
	}
	
	#ŷ������
	function setWeather() {
		$gamelog = $this->readGameLogAll();
		$rainycount=0;
		$sunnycount=0;
		foreach($gamelog as $l){
			if(preg_match('/use potion\('.AMAGUMO.'\)/',$l)){
				$rainycount++;
			} else if(preg_match('/use potion\('.SEITEN.'\)/',$l)) {
				$sunnycount++;
			}
		}
		if($sunnycount > $rainycount) $this->rainy = 0;
		else if($sunnycount < $rainycount) $this->rainy = 1;
		else if($this->rainy){
			if(mt_rand(0,9) > 2) $this->rainy = 0;
			else $this->rainy = 1;
		} else {
			if(mt_rand(0,9) == 0) $this->rainy = 1;
			else $this->rainy = 0;
		}
	}

	#������Ͽ�����å�
	function checkSignUp($id,$pass,$name){
		if(strlen($id) == 0){
			return "ID�����Ϥ���Ƥ��ޤ���";
		} else if(strlen($pass) == 0){
			return "�ѥ���ɤ����Ϥ���Ƥ��ޤ���";
		} else if(strlen($name) == 0){
			return "̾�������Ϥ���Ƥ��ޤ���";
		} else if(strlen($id) < 4) {
			return "ID��û�����ޤ�";
		} else if(strlen($id) > 16) {
			return "ID��Ĺ�����ޤ�";
		} else if(strlen($pass) < 4) {
			return "�ѥ���ɤ�û�����ޤ�";
		} else if(strlen($pass) > 16) {
			return "�ѥ���ɤ�Ĺ�����ޤ�";
		} else if(strlen($name) > 16){
			return "̾����Ĺ�����ޤ�";
		} else if(!preg_match('/^[0-9a-zA-Z]+$/',$id)) {
			return "ID�˻��ѤǤ���Τ�Ⱦ�ѱѿ��ΤߤǤ�";
		} else if(!preg_match('/^[0-9a-zA-Z]+$/',$pass)) {
			return "�ѥ���ɤ˻��ѤǤ���Τ�Ⱦ�ѱѿ��ΤߤǤ�";
		}
		
		foreach($this->userdata as $u){
			if($name == $u->name){
				return "����̾���ϴ��˻Ȥ��Ƥ��ޤ�";
			} else if($id == $u->id) {
				return "����ID�ϴ��˻Ȥ��Ƥ��ޤ�";
			}
		}
		return "ok";
		
	}
	
	#����������å�
	function checkLogin($id, $pass){

		if(strlen($_POST["id"]) == 0){
			return "ID�����Ϥ���Ƥ��ޤ���";
		} else if(strlen($_POST["pass"]) == 0){
			return "�ѥ���ɤ����Ϥ���Ƥ��ޤ���";
		}
		
		foreach($this->userdata as $data){
			if($data->id == $id){
				if($data->pass == $pass){
					return "ok";
				} else {
					return "�ѥ���ɤ��ְ�äƤ��ޤ�";
				}
			}
		}
		return "����ID��¸�ߤ��ޤ���";
	}

	#�ץ쥤�䡼�ο�����Ͽ
	function makeNewPlayer($id,$pass,$name) {
	
		if(get_magic_quotes_gpc()) {
			$name = stripslashes($name);
		}
		$name = htmlspecialchars($name);
		
		$newuser = new Player();
		$newuser->setNewData($id,$pass,$name);
		$this->userdata[USERNUM] = $newuser;
		$this->savePlayerData();
		
		$fp = fopen("gamedata.log", "r");
		$gamedata = array();
		while($line = fgets($fp)) {
			array_push($gamedata, $line);
		}
		fclose($fp);
		
		$tempstr = trim(rtrim($gamedata[2]).",none\n",",");
		
		$fp = fopen("gamedata.log", "w");
		fwrite($fp, $gamedata[0]);
		fwrite($fp, $gamedata[1]);
		fwrite($fp, $tempstr);
		fclose($fp);
	
	}

	#�ڡ������ܻ������Υڡ�����������줿�͡ʥ饸���ܥ���ˤ������äƽ�������
	function setAction($mode){
		$mes = array("");
		$saveflag = FALSE;
		
		if($mode == "potion"){
			$mes = $this->setUsingPotion(isset($_POST["potno"]),$this->potdata);
			if(isset($mes[1])) $mes[1] = $this->usePotionInTurn($mes[1]);
		} else if($mode == "end") {
			$temp = $mode;
		} else if($mode == "back") {
			#���⤷�ʤ�
		} else if($mode == "perspective") {
			$this->player->pagemode = "perspective";
		} else {
			switch($this->player->action){
				case "action":
					switch($mode){
						case "gather":
						case "compound":
						case "shop":
							$temp = $mode;
							break;
						default:
							echo "error:setAction(action,".$mode.")";
							exit;
					}
					break;
				case "gather":
					echo "error:setAction(gather,".$mode.")";
					exit;
					break;
				case "compound":
					switch($mode){
						case "comp_mat":
							$mes = $this->player->compoundMaterial($_POST["selectc"],isset($_POST["double"]),$_POST["mat1"], $_POST["mat2"],$this->matdata);
							break;
						case "comp_pot":
							$mes = $this->player->compoundPotion($_POST["selectc"],$this->matdata,$this->potdata);
							$this->player->pagemode = "mixpotion";
							break;
						case "mixpotion":
							$this->player->pagemode = "mixpotion";
							break;
						default:
							echo "error:setAction(compound,".$mode.")";
							exit;
					}
					break;
				case "shop":
					switch($mode){
						case "buy":
							$mes = $this->player->buyItem($_POST["buybox"],intval($_POST["buyint"]),$this->matdata,$this->potdata,$this->moon);
							break;
						case "sell":
							$mes = $this->player->sellItem($_POST["sellbox"],intval($_POST["sellint"]),$this->matdata,$this->potdata);
							break;
						case "order":
							$mes = $this->player->orderGathering(intval($_POST["order"]));
							break;
						default:
							echo "error:setAction(shop,".$mode.")";
							exit;
					}
					break;
			}
		}
		
		if(isset($temp)){
			$this->addLog($this->player->id.":action.".$temp);
			$this->player->action = $temp;
			if($temp != "end"){
				$this->saveAction();
			}
			$this->savePlayerData();
		} else if($saveflag || isset($mes[2])) {
			$this->savePlayerData();
		}
		
		if(isset($mes[1])) $this->addLog($mes[1]);
		return $mes[0];
	}
	
	#�ݡ��������ѻ��ν�����setAction�������֡�
	function setUsingPotion($pot,$potdata){
		if($pot) $mes = $this->player->usePotion($_POST["potno"],$this->potdata);
		else $mes = array("");
		$this->player->pagemode = "usepotion";
		return $mes;
	}
	
	#¨�����̥ݡ������ν���
	function usePotionInTurn($str){
		preg_match('/use potion\(([0-9]+)\)/',$str,$match);
		switch($match[1]){
			case KAZENOUWASA:
				$this->addLog($str);
				return $this->getUwasa();
			case CHOKKAN:
				$this->addLog($str);
				return $this->getChokkan();
		}
		return $str;
	}
	
	#���Υ�����ι�ư�����ꤷ���Ȥ��˥����ƥ�ǡ����˽񤭤���
	function saveAction(){
		$tempstr = "";
		for($i=0;$i<USERNUM;$i++){
			if($i == $this->player->no){
				$tempstr = $tempstr.$this->player->action.",";
			} else {
				$tempstr = $tempstr.$this->userdata[$i]->selectact.",";
			}
		}
		$tempstr = rtrim($tempstr,",");
		
		$fp = fopen("gamedata.log", "r");
		$gamedata = array();
		while($line = fgets($fp)) {
			array_push($gamedata, $line);
		}
		fclose($fp);
		
		$fp = fopen("gamedata.log", "w");
		fwrite($fp, $gamedata[0]);
		fwrite($fp, $gamedata[1]);
		fwrite($fp, $tempstr."\n");
		fclose($fp);
	}
	
	#user.log�ؤν񤭹���
	function savePlayerData(){
		#TODO �ե������å�������Ȥ���
		$savedata = array();
		foreach($this->userdata as $data) {
			if(isset($this->player->id) && $data->id == $this->player->id){
				$temppl = $this->player;
			} else {
				$temppl = $data;
			}
			$tempdata = $temppl->id.",".$temppl->name.",".$temppl->pass.",".$temppl->action.",";
			for($i=0;$i<MATERIAL_NO;$i++){
				if(isset($temppl->material[$i]) && $temppl->material[$i] != 0){
					$tempdata = $tempdata.$i."=".$temppl->material[$i]."&";
				}
			}
			$tempdata = preg_replace('/&$/', "", $tempdata);
			$tempdata = $tempdata.",";
			for($i=0;$i<POTION_NO;$i++){
				if(isset($temppl->potion[$i]) && $temppl->potion[$i] != 0){
					$tempdata = $tempdata.$i."=".$temppl->potion[$i]."&";
				}
			}
			$tempdata = preg_replace('/&$/', "", $tempdata);
			$tempdata = $tempdata.",";
			for($i=0;$i<5;$i++){
				for($j=0;$j<5;$j++){
					if($i >= $j && isset($temppl->ptable[$i][$j])){
						$tempdata = $tempdata.$i.$j."&";
					}
				}
			}
			$tempdata = preg_replace('/&$/', "", $tempdata);
			$tempdata = $tempdata.",".$temppl->caul[0].",".$temppl->caul[1].",".$temppl->gold;
			#$tempdata = $tempdata."\n";
			array_push($savedata,$tempdata);
		}
		
		$fp = fopen("user.log", "w");
		foreach($savedata as $data){
			fwrite($fp, $data."\n");
		}
		fclose($fp);
	}

	#�������Υ������ΤΥ����ɲý񤭹���
	function addLog($log){
		if($log == "") return;
		$fp = fopen("gamelog.log", "a");
		fwrite($fp, $log."\n");
		fclose($fp);
		$fp = fopen("logall.log", "a");
		fwrite($fp, $log."\n");
		fclose($fp);
	}

	#�ݡ���������äƤ��뤫�ɤ����ʸĿ��ϴط��ʤ���
	function hasPotion(){
		$potnum = 0;
		for($i=0;$i<POTION_NO;$i++){
			if(!isset($this->player->potion[$i])) return FALSE;
			if($this->player->potion[$i] > 0){
				$potnum++;
			}
		}
		if($potnum > 0) return TRUE;
		return FALSE;
	}
	
	#ID�������äơ�����̾�����֤�
	function getUserName($id){
		foreach($this->userdata as $u){
			if($u->id == $id){
				return $u->name;
			}
		}
		return FALSE;
	}
	
	#ľ��������
	function getChokkan(){
		do{
			$rand1 = mt_rand(0,4);
			$rand2 = mt_rand(0,4);
		}while($rand1 < $rand2);
		if(!isset($this->player->ptable[$rand1][$rand2])){
			$this->player->ptable[$rand1][$rand2] = TRUE;
			$this->player->ptable[$rand2][$rand1] = TRUE;
			return $this->player->id.":open(".$rand1.$rand2.")";
		} else {
			return $this->player->id.":open(none)";
		}
	}
	
	#���Τ��蘆�Υƥ����Ȥ����
	function getUwasa(){
		$rand = mt_rand(0,2);
		
		$pl = $this->userdata[mt_rand(0,USERNUM-1)];
		#�쥷�Ԥο�
		if($rand == 0){
			$tablenum = $pl->getTableNum();
			return "uwasa>".$pl->id.".table(".$tablenum.")";
		#�����
		} else if($rand == 1) { 
			$gold = $pl->gold;
			return "uwasa>".$pl->id.".gold(".$gold.")";
		#�ݡ������λ��Ѿ���
		} else if($rand == 2) {
			$log = array();
			$upot = array();
			$fp = fopen("prevlog.log", "r");
			while($line = fgets($fp)) {
				array_push($log, $line);
			}
			fclose($fp);
			foreach($log as $l){
				if(preg_match('/(.+):use potion\(([0-9]+)\)/',$l,$match)){
					array_push($upot,array("id"=>$match[1],"potion"=>$match[2]));
				}
			}
			if(count($upot) > 0){
				$rand = mt_rand(0,count($upot)-1);
				return "uwasa>".$upot[$rand]["id"].".use(".$upot[$rand]["potion"].")";
			} else {
				return $this->getUwasa();
			}
		}
	}
}

?>