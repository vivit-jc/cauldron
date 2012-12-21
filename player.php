<?php

class Player{

	var $name;
	var $no;
	var $id;
	var $pass;
	var $action;
	var $caul = array();
	var $material;
	var $potion;
	var $ptable;
	var $selectact;
	var $turnlog;
	var $pagemode;
	var $gold;
	var $winflag;
	var $ugly;
	var $resist;
	var $nego;
	var $conse;
	var $search;
	var $awkward;

	function setNewData($id,$pass,$name) {
		$this->id = $id;
		$this->name = $name;
		$this->pass = $pass;
		$this->action = "action";
		$this->material = array();
		$this->potion = array();
		$this->ptable = array();
		$this->caul = array(2,0);
		$this->gold = 10;
		$this->selectact = "none";
		$this->awkward = 0;
		$this->conse = 0;
		$this->search = 0;
		return;
	}

	#�ץ쥤�䡼�����äƤ����Ǻ�ο���������֤�
	function getMaterial(){
		$rarray = array();
		foreach($this->material as $mat){
			array_push($rarray, $mat);
		}
		return $rarray;
	}

	#Ĵ��
	function compoundMaterial($caul,$double,$mat1, $mat2, $matdata){
		if(($mes = $this->checkCompoundMaterial($caul,$double,$mat1, $mat2, $matdata)) != ""){
			return array($mes);
		}
		if($mat2 >= 10){$tmat2 = $mat2; $mat2 = MIMIZU;}
		else {$tmat2 = $mat2;}
		if($double){
			$this->material[$mat1]--;
			$this->material[$mat2]--;
		}
		$this->material[$mat1]--;
		$this->material[$mat2]--;

		if($mat1 == BEAT || $mat1 == PRIMEVAL){
			return array("k�إ��ꥯ�����٤��������ޤ���",$this->id.":compound(".$caul.",elixir)",TRUE);
		} else {
			if($double) $double = 1;
			else $double = 0;
			return array("k�ݡ��������������ޤ���",$this->id.":compound(".$caul.",".$double.",".$mat1.",".$tmat2.")",TRUE);
		}
	}
	
	#Ĵ����Ǻ�����å�
	function checkCompoundMaterial($caul,$double,$mat1, $mat2, $matdata){
		if($mat2 >= 10){
			$mat2 = MIMIZU;
		}
		if($mat1 == "none" || $mat2 == "none"){
			return "�Ǻब���򤵤�Ƥ��ޤ���";
		}
		#���äƤ��ʤ��Ǻ��Ȥä��Ȥ�
		if($this->material[$mat1] <= 0 || $this->material[$mat2] <= 0) {
			return "���������ϤǤ�";
		}
		
		#������­��ʤ��Ȥ�
		if(($mat1 == $mat2 && $this->material[$mat1] < 2) || 
		($double && $mat1 == $mat2 && $this->material[$mat1] < 4) || 
		($double && ($this->material[$mat1] < 2 || $this->material[$mat2] < 2))){
			return "������­��ޤ���";
		}
		
		#���Ϣ����������
		if(($caul < 0 || $caul > 1) || $this->caul[$caul] == 0 ){
			return "���������ϤǤ�";
		}
		
		#�����򥨥ꥯ�����ʳ����Ȥ߹�碌�ǻȤ����Ȥ����Ȥ�
		if(($mat1 == 6 && $mat2 != 7) || ($mat1 == 7 && $mat2 != 6) || 
		($mat1 != 6 && $mat2 == 7) || ($mat1 != 7 && $mat2 == 6)){
			return "�����Ȥ߹�碌�ǤϤ��ޤ����������ˤ���ޤ���";
		}
		
		#���Ǥ˻ȤäƤ������Ĵ�礷�褦�Ȥ����Ȥ�
		foreach($this->turnlog as $l){
			if(preg_match('/compound\(([01]),[01],[0-9]+,[0-9]+\)/',$l,$match)){
				if($caul == $match[1]) return "������ϴ��˻Ȥ��Ƥ��ޤ�";
			} else if(preg_match('/compound\(([01]),.+\)/',$l,$match)){
				if($caul == $match[1]) return "������ϴ��˻Ȥ��Ƥ��ޤ�";
			}
		}
		
		#����Ĵ�礬�Ǥ��ʤ��Τˤ��褦�Ȥ����Ȥ�
		if($this->caul[$caul] != 3 && $double){
			return "������Ǥ����ܤ�ʬ�̤�Ĵ��Ǥ��ޤ���";
		}
		
		return "";
	}
	
	#�ݡ������Ʊ�Τ�Ĵ�礹��Ȥ��ν����������å���ǽ����¢����
	function compoundPotion($caul,$matdata,$potdata){
		$potarray = array();
		for($i=0;$i<POTION_NO;$i++){
			if(isset($_POST["pot".$i])) array_push($potarray,$i);
		}
		if(count($potarray) == 0){
			return array("�ݡ�����󤬤ҤȤĤ����򤵤�Ƥ��ޤ���");
		} else if(count($potarray) == 1){
			return array("�ݡ�����󤬤ҤȤĤ������򤵤�Ƥ��ޤ���");
		} else if(count($potarray) != 3 && count($potarray) != 4){
			return array("�����Ȥ߹�碌�ǤϤ��ޤ����������ˤ���ޤ���");
		}
		
		#���Ǥ˻ȤäƤ������Ĵ�礷�褦�Ȥ����Ȥ�
		foreach($this->turnlog as $l){
			if(preg_match('/compound\(([01]),/',$l,$match)){
				if($caul == $match[1]) {return array("������ϴ��˻Ȥ��Ƥ��ޤ�");}
			}
		}
		
		if(count($potarray) == 3){
			$flag = array(0,0,0);
			$type = $potdata[$potarray[0]]["type"];
			foreach($potarray as $p) {
				if($type != $potdata[$p]["type"]) break;
				$flag[$potdata[$p]["value"]] = 1;
			}
			if($flag[0] && $flag[1] && $flag[2]){
				$this->potion[$potarray[0]]--;
				$this->potion[$potarray[1]]--;
				$this->potion[$potarray[2]]--;
				return array("k".$matdata[6]["name"]."���������ޤ���",$this->id.":compound(".$caul.",primeval)",TRUE);
			} else {
				return array("�����Ȥ߹�碌�ǤϤ��ޤ����������ˤ���ޤ���");
			}
		} else if(count($potarray) == 4){
			$flag = array(0,0,0,0);
			$value = $potdata[$potarray[0]]["value"];
			foreach($potarray as $p) {
				if($value != $potdata[$p]["value"]) break;
				$flag[$potdata[$p]["type"]] = 1;
			}
			if($flag[0] && $flag[1] && $flag[2] && $flag[3]){
				$this->potion[$potarray[0]]--;
				$this->potion[$potarray[1]]--;
				$this->potion[$potarray[2]]--;
				$this->potion[$potarray[3]]--;
				return array("k".$matdata[7]["name"]."���������ޤ���",$this->id.":compound(".$caul.",beat)",TRUE);
			} else {
				return array("�����Ȥ߹�碌�ǤϤ��ޤ����������ˤ���ޤ���");
			}
		}
	}
	
	#��ο�������å���Ĵ���ǽ����Ĵ�٤�
	function checkCauldronNum(){
		$count = 0;
		$caul = 0;
		foreach($this->turnlog as $l){
			if(preg_match('/compound\(.*\)/', $l)) {$count++;}
		}
		if($this->caul[0] > 0) $caul++;
		if($this->caul[1] > 0) $caul++;
		if($count < $caul) {return TRUE;}
		else {return FALSE;}
	}
	
	#����Υץ쥤�䡼��������Ǻࣲ�Ĥ�Ĵ��쥷�Ԥ�����
	function learnPotionTable($mat1, $mat2){
		$this->ptable[$mat1][$mat2] = TRUE;
		$this->ptable[$mat2][$mat1] = TRUE;
		return;
	}

	#������λ����Ĵ��ν���
	function resolveCompound($log,$potiontable) {
		$rarray = array();
		foreach($log as $l){
			if(preg_match('/compound\(([01]),([01]),([0-9]+),([0-9]+)\)/', $l, $match)){
				$mat1 = $match[3];
				if($match[4] >= 10){$tmat2 = $match[4]; $mat2 = $match[4]-10;}
				else {$mat2 = $match[4]; $tmat2 = $match[4];}
				#���ΤΥ쥷�Ԥξ��
				if(isset($this->ptable[$mat1][$mat2]) && $potiontable[$mat1][$mat2] != 100){
					if(($fail = $this->awkward - $this->conse*2) > 0 && mt_rand(1,6-$fail) == 1){
						array_push($rarray, $this->id.":compound fail(".$mat1.",".$tmat2.")");
					} else {
						if($this->caul[$match[1]] == 3 && $match[2] == "1") $getp=4;
						else if($this->caul[$match[1]] >= 2) $getp=2;
						else $getp=1;
						$this->potion[$potiontable[$mat1][$mat2]]+=$getp;
						array_push($rarray, $this->id.":compound success(".$mat1.",".$tmat2.")");
						array_push($rarray, $this->id.":get potion(".$potiontable[$mat1][$mat2].",".$getp.")");
					}
				} else {
					if($potiontable[$mat1][$mat2] == 100){
						array_push($rarray, $this->id.":compound fail(".$mat1.",".$tmat2.")");
						$this->learnPotionTable($mat1,$mat2);
					} else {
						#1/2�γ�Ψ�Ǽ��Ԥ���+���桢�Դ��ѤΥݡ������ˤ������
						$suc = $this->conse*2 - $this->awkward;
						if(mt_rand(1,6) > 3 - $suc){
							if($this->caul[$match[1]] == 3 && $match[2] == "1") $getp=4;
							else if($this->caul[$match[1]] >= 2) $getp=2;
							else $getp=1;
							$this->potion[$potiontable[$mat1][$mat2]]+=$getp;
							$this->learnPotionTable($mat1,$mat2);
							array_push($rarray, $this->id.":compound success(".$mat1.",".$tmat2.")");
							array_push($rarray, $this->id.":get potion(".$potiontable[$mat1][$mat2].",".$getp.")");
						} else {
							array_push($rarray, $this->id.":compound fail(".$mat1.",".$tmat2.")");
						}
					}
				}
			} else if(preg_match('/compound\([0-9],(.+)\)/', $l, $match)) {
				switch($match[1]){
					case "primeval":
						array_push($rarray, $this->id.":compound success(primeval)");
						array_push($rarray, $this->id.":get material(".PRIMEVAL.")");
						$this->material[PRIMEVAL]++;
						break;
					case "beat":
						array_push($rarray, $this->id.":compound success(beat)");
						array_push($rarray, $this->id.":get material(".BEAT.")");
						$this->material[BEAT]++;
						break;
					case "elixir":
						array_push($rarray, $this->id.":compound success(elixir)");
						array_push($rarray, $this->id.":get material(".ELIXIR.")");
						$this->material[ELIXIR]++;
						break;
				}
			}
		}
		return $rarray;
	}
	
	#������λ���γ��ν���
	function resolveShop($log,$matdata){
		$rarray = array();
		foreach($log as $l){
			if(preg_match('/order\(([0-9]+)\)/', $l, $match)){
				$temp = $this->resolveOrder(intval($match[1]),$matdata);
				foreach($temp as $t){array_push($rarray, $t);}
			}
		}
		return $rarray;
	}
	
	#�����ƥ���㤦����
	function buyItem($item,$int,$matdata,$potdata,$moon){
		if($item == "none") return array("�����ƥ�����򤷤Ƥ�������");
		if($item < 0 || !is_numeric($item)) return array("���������ϤǤ�");
		
		if($item == 100 || $item == 101){
			if($this->caul[$item-100] >= MAX_CAULDRON ) return array("���������ϤǤ�");
			switch($this->caul[$item-100]){
				case 0:$cprice = PRICE_CAULDRON1;break;
				case 1:$cprice = PRICE_CAULDRON2;break;
				case 2:$cprice = PRICE_CAULDRON3;break;
			}
			if($this->gold < $cprice) return array("���⤬­��ޤ���");
			$this->gold -= $cprice;
			$this->caul[$item-100]++;
			if($this->caul[$item-100] == 1){
				return array("k�������ĥ���ޤ���",$this->id.":buy(cauldron".($item-100).")",TRUE);
			} else {
				return array("k��".($item-99)."���礭�����ޤ���",$this->id.":buy(cauldron".($item-100).")",TRUE);
			}
		} else if($item >= 50){
			$titem = $item;
			$item -= 50;
			$buy = "buy";
			if($this->nego || $moon == 0) $buy = "sprice";
			if($this->gold < $matdata[$item][$buy]*$int) return array("���⤬­��ޤ���");
			$this->gold -= $matdata[$item][$buy]*$int;
			$this->material[$item] += $int;
			return array("k".$matdata[$item]["name"]."��".$int."�Ĺ������ޤ���",$this->id.":buy(".$titem.",".$int.")",TRUE);
		} else {
			$buy = "buy";
			if($this->nego || $moon == 0) $buy = "sprice";
			if($this->gold < $potdata[$item][$buy]*$int) return array("���⤬­��ޤ���");
			$this->gold -= $potdata[$item][$buy]*$int;
			$this->potion[$item] += $int;
			return array("k".$potdata[$item]["name"]."�Υݡ�������".$int."�Ĺ������ޤ���",$this->id.":buy(".$item.",".$int.")",TRUE);
		}
		return array("buyItem:��ã��ǽ");
	}
	
	#�����ƥ��������
	function sellItem($item,$int,$matdata,$potdata){
		if($item == "none") return array("�����ƥ�����򤷤Ƥ�������");
		if($item < 0 || !is_numeric($item)) return array("���������ϤǤ�");
		
		if($item >= 50){
			$titem = $item;
			$item -= 50;
			if($this->material[$item] <= 0) return array("���������ϤǤ�");
			if($this->material[$item] < $int) return array("����ʤ˻��äƤ��ޤ���");
			$this->gold += $matdata[$item]["sell"]*$int;
			$this->material[$item] -= $int;
			return array("k".$matdata[$item]["name"]."��".$int."����Ѥ��ޤ���",$this->id.":sell(".$titem.",".$int.")",TRUE);
		} else {
			$sell = "sell";
			if($this->nego) $sell = "sprice";
			if($this->potion[$item] <= 0) return array("���������ϤǤ�");
			if($this->potion[$item] < $int) return array("����ʤ˻��äƤ��ޤ���");
			$this->gold += $potdata[$item][$sell]*$int;
			$this->potion[$item] -= $int;
			return array("k".$potdata[$item]["name"]."�Υݡ�������".$int."����Ѥ��ޤ���",$this->id.":sell(".$item.",".$int.")",TRUE);
		}
		return array("buyItem:��ã��ǽ");
	}
	
	#�Ǻླྀ�������ν���
	function orderGathering($fee){
		if($fee <= 0){
			return array("�ͤ������Ǥ�");
		} else if($fee*PRICE_ORDER > $this->gold){
			return array("���⤬­��ޤ���");
		} else {
			$this->gold -= $fee*PRICE_ORDER;
			return array("k�Ǻླྀ�����ꤷ�ޤ���(������:".$fee.")",$this->id.":order(".$fee.")",TRUE);
		}
	}
	
	#�Ǻླྀ�����ꤷ�Ƥ����Ȥ��ν���
	function resolveOrder($order,$matdata){

		$count = 2;
		$rarray = array();
		if(mt_rand(1,6) == 1){$count++;}
		if(mt_rand(1,6) == 1){$count++;}
		
		$randmax = 0;
		foreach($matdata as $mat){
			if($mat["ordere"] == 0) break;
			$randmax += $mat["ordere"];
		}
		
		for($i=0;$i<$order;$i++){
			for($j=0;$j<$count;$j++){
				$randcount = 0;
				$rand = mt_rand(0,$randmax-1);
				foreach($matdata as $mat){
					if($randcount <= $rand && $rand < $randcount + $mat["ordere"]){
						array_push($rarray,$this->id.":get material(".$mat["no"].")");
						$this->material[$mat["no"]]++;
						break;
					} else {
						$randcount += $mat["ordere"];
					}
				}
			}
		}
		
		return $rarray;
	}
	
	#������λ�����Ǻླྀ��ν���
	function resolveGather($log,$matdata,$moon){
		$count = 3;
		$rarray = array();
		if(mt_rand(1,6) == 1){$count++;}
		if(mt_rand(1,6) == 1){$count++;}
		for($i=0;$i<$this->search;$i++){
			$count += 2;
			if(mt_rand(1,6) == 1){$count++;}
			$matdata[4]["gathere"] += 3;
			$matdata[5]["gathere"] += 2;
		}
		for($i=0;$i<$this->conse;$i++){
			$matdata[4]["gathere"] += 5;
			$matdata[5]["gathere"] += 5;
		}
		for($i=0;$i<$this->awkward;$i++){
			$count -= 2;
		}
		if($moon == 4){
			$matdata[5]["gathere"] += 8;
		}
		if($count <= 0) $count = 1;
		
		$randmax = 0;
		foreach($matdata as $mat){
			if($mat["gathere"] == 0) break;
			$randmax += $mat["gathere"];
		}
		
		for($i=0;$i<$count;$i++){
			$randcount = 0;
			$rand = mt_rand(0,$randmax-1);
			foreach($matdata as $mat){
				if($randcount <= $rand && $rand < $randcount + $mat["gathere"]){
					array_push($rarray,$this->id.":get material(".$mat["no"].")");
					$this->material[$mat["no"]]++;
					break;
				} else {
					$randcount += $mat["gathere"];
				}
			}
		}
		
		return $rarray;
	}

	#�ݡ�������Ȥä��Ȥ��ν���
	function usePotion($pot,$potdata) {
		if($pot == "none"){
			return array("���Ѥ���ݡ����������򤷤Ƥ�������");
		}
		if($pot >= POTION_NO || $pot < 0 || $this->potion[$pot] <= 0){
			return array("���������ϤǤ�");
		}
		$this->potion[$pot]--;
		return array("k".$potdata[$pot]["name"]."�Υݡ���������Ѥ��ޤ���",$this->id.":use potion(".$pot.")",TRUE);
	}
	
	#�ݡ������ν�������֤�
	function getPotionNum(){
		$count = 0;
		for($i=0;$i<POTION_NO;$i++){
			$count += $this->potion[$i];
		}
		return $count;
	}
	
	#���ΤΥ쥷�Ԥο����֤�
	function getTableNum() {
		$count = 0;
		for($i=0;$i<5;$i++){
			for($j=0;$j<5;$j++){
				if($i < $j) continue;
				if(isset($this->ptable[$i][$j])) $count++;
			}
		}
		return $count;
	}

	#˺�Ѥμ����ǥ쥷�Ԥ��ä���Ȥ��ν���
	function lostRecipe() {
		if($this->getTableNum == 0) return FALSE;
		$x = 100;
		$y = 100;
		
		do {
			$rx = mt_rand(0,4);
			$ry = mt_rand(0,4);
			if($rx >= $ry && isset($this->ptable[$rx][$ry])){
				$x = $rx;
				$y = $ry;
			}
		}while($x == 100 || $y == 100);
		
		$this->ptable[$x][$y] = NULL;
		$this->ptable[$y][$x] = NULL;
	}
	
}


?>