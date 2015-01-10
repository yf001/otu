<?php

namespace otu;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class jail{

	private static $obj = null;
	
	public function __construct(){
		if(!self::$obj instanceof Jail){
			self::$obj = $this;
		}
		$this->old = array();
		$this->pos = array();
		$this->plugin = Server::getInstance()->getPluginManager()->getPlugin("otu");
		$this->jail = new Config($this->plugin->getDataFolder() . "jail.yml", Config::YAML);
		$datas = $this->jail->getAll();
		if(count($datas) > 0){
			foreach($datas as $name => $blocks){
				$this->jailtype[$name] = $blocks;
			}
		}
	}
	
	public static function init(){
		if(!self::$obj instanceof Jail){
			self::$obj = new self;
		}
	}
	
	public static function getInstance(){
		if(!self::$obj instanceof Jail){
			self::$obj = new self;
		}
		return self::$obj;
	}
	
	//プレーヤーを牢屋に入れる//$player プレーヤーのオブジェクト
    public function playerJail($player,$sender,$type = null){
		$x = round($player->getX());
		$y = round($player->getY());
		$z = round($player->getZ());
		$data = array();
		//$blocks = $this->getJailStructure();
		$blocks = $this->getJailType($type);
		$level = $player->getLevel();
		foreach($blocks as $key => $val){
			//[0] X座標,[1] Y座標,[2] Z座標,[3] BlockID,[4] メタ値
			$bx = $x + $val[0];
			$by = $y + $val[1];
			$bz = $z + $val[2];
			$id = $val[3];
			$mata = $val[4];
			$this->plugin->getLogger()->info("key." . $key . " x." . $x . " y." . $y . " z." . $z . " id." .$id . " mata." . $mata);
			$data[] = array($bx, $by, $bz, $level->getBlockIdAt($bx, $by ,$bz),
			$level->getBlockDataAt($bx, $by ,$bz));
			$block = Block::get($id,$mata);
			$pos = new Vector3($bx, $by, $bz);
			$level->setBlock($pos, $block);
		}
		$pos = new Position($x + 0.5, $y, $z + 0.5,$level);
		$player->teleport($pos);
		var_dump($blocks);
		$this->old[$sender->getName()] = $data;
		return true;
	}
	
	//牢屋を戻す
    public function unJail($player){
		if(isset($this->old[$player->getName()])){
			$data = $this->old[$player->getName()];
			if($player instanceof Player){
            	$level = $player->getLevel();
            }else{
            	$level = $this->getServer()->getDefaultLevel();
            }
			foreach($data as $val){
				$block = Block::get($val[3], $val[4]);
				$pos = new Vector3($val[0], $val[1], $val[2]);
				$level->setBlock($pos, $block);
			}
			unset($this->old[$player->getName()]);
			return true;
		}else{
			return false;
		}
	}
	
	public function getJailType($type = null){//to-do 読み込まれた地形データを選べるようにする
		if(isset($this->jailtype[$type])){
			return $this->jailtype[$type];
		}else{
			return $this->getJailStructure();
		}
	}
	
	public function craftJail($player, $jailName){//to-do 牢屋自体を作成できるように
		$blocks = $this->jailBlocks($player);
		if($blocks !== false){
			$this->jailLoad($blocks, $jailName);
			return true;
		}else{
			return false;
		}
	}
	
	public function jailLoad($blocks, $name){//to-do 牢屋の地形データを読み込めるように
		if(!$this->jail->exists($name)){
	        $this->jailRegister($blocks, $name);
        }
		$this->jailtype[$name] = $blocks;
		return true;
	}
	
	public function jailRegister($blocks, $name){//to-do 牢屋の地形データを登録
		$this->jail->set($name,$blocks);
		$this->jail->save();
		return true;
	}
	
	public function jailBlocks($player){
		if(isset(Jail::getInstance()->pos[$player->getName()][1]) 
			and isset(Jail::getInstance()->pos[$player->getName()][2]) 
				and isset(Jail::getInstance()->pos[$player->getName()][3])){
			$pos = $this->pos[$player->getName()];
			$level = $player->getLevel();
			$sx = min($pos[1]["x"], $pos[2]["x"]);
			$sy = min($pos[1]["y"], $pos[2]["y"]);
			$sz = min($pos[1]["z"], $pos[2]["z"]);
			$ex = max($pos[1]["x"], $pos[2]["x"]);
			$ey = max($pos[1]["y"], $pos[2]["y"]);
			$ez = max($pos[1]["z"], $pos[2]["z"]);
			$px = round($pos[3]);
			$py = round($pos[3]);
			$pz = round($pos[3]);
			$data = array();
			$c = 0;
			for($x = $sx; $x <= $ex; ++$x){
				for($y = $sy; $y <= $ey; ++$y){
					for($z = $sz; $z <= $ez; ++$z){
						$cx = $x - $px;
						$cy = $y - $py;
						$cz = $z - $pz;
						$data = array_merge($data, array("{$c}" => array("0" => $cx, "1" => $cy, "2" => $cz, "3" => $level->getBlockIdAt($x, $y ,$z), "4" => $level->getBlockDataAt($x, $y ,$z))));
						$c++;
					}
				}
			}
			return $data;
		}else{
			return false;
		}
	}

	public function getJailStructure(){
    	/*$blocks = array(
			array(0, 0, 0, 0, 0),
			
			array(1, 0, 0, 61, 0),
			
			array(1, 0, 1, 58, 0),
			
			array(0, 0, 1, 0, 0),
			
			array(-1, 0, 1, 0, 0),
			
			array(-1, 0, 0, 0, 0),
			
			array(-1, 0, -1, 0, 0),
			
			array(0, 0, -1, 0, 0),
			
			array(1, 0, -1, 47, 0),
			
			array(0, 1, 0, 0, 0),
			
			array(1, 1, 0, 0, 0),
			
			array(1, 1, 1, 0, 0),
			
			array(0, 1, 1, 0, 0),
			
			array(-1, 1, 1, 0, 0),
			
			array(-1, 1, 0, 0, 0),
			
			array(-1, 1, -1, 0, 0),
			
			array(0, 1, -1, 0, 0),
			
			array(1, 1, -1, 47, 0),
			
			array(0, -1, 0, 43, 0),
			
			array(1, -1, 0, 43, 0),
			
			array(1, -1, 1, 43, 0),
			
			array(0, -1, 1, 43, 0),
			
			array(-1, -1, 1, 43, 0),
			
			array(-1, -1, 0, 43, 0),
			
			array(-1, -1, -1, 43, 0),
			
			array(0, -1, -1, 43, 0),
			
			array(1, -1, -1, 43, 0),
			
			array(0, 3, 0, 89, 0),
			
			array(1, 3, 0, 17, 0),
			
			array(1, 3, 1, 17, 0),
			
			array(0, 3, 1, 17, 0),
			
			array(-1, 3, 1, 17, 0),
			
			array(-1, 3, 0, 17, 0),
			
			array(-1, 3, -1, 17, 0),
			
			array(0, 3, -1, 17, 0),
			
			array(1, 3, -1, 17, 0),
			
			array(0, 2, 0, 0, 0),
			
			array(1, 2, 0, 0, 0),
			
			array(1, 2, 1, 0, 0),
			
			array(0, 2, 1, 0, 0),
			
			array(-1, 2, 1, 0, 0),
			
			array(-1, 2, 0, 0, 0),
			
			array(-1, 2, -1, 0, 0),
			
			array(0, 2, -1, 0, 0),
			
			array(1, 2, -1, 0, 0),
			
			array(2, 0, -1, 5, 0),
			
			array(2, 0, 0, 5, 0),
			
			array(2, 0, 1, 5, 0),
			
			array(1, 0, 2, 5, 0),
			 array(0, 0, 2, 5, 0),
			 array(-1, 0, 2, 5, 0),
			 array(-2, 0, 1, 5, 0),
			 array(-2, 0, 0, 5, 0),
			 array(-2, 0, -1, 0, 0),
			 array(1, 0, -2, 5, 0),
			 array(0, 0, -2, 5, 0),
			 array(-1, 0, -2, 5, 0),
			array(2, 0, 2, 17, 0),
			 array(-2, 0, 2, 17, 0),
			 array(-2, 0, -2, 17, 0),
			 array(2, 0, -2, 17, 0),
			array(2, 1, -1, 5, 0),
			 array(2, 1, 0, 102, 0),
			 array(2, 1, 1, 5, 0),
			 array(1, 1, 2, 5, 0),
			 array(0, 1, 2, 102, 0),
			 array(-1, 1, 2, 5, 0),
			 array(-2, 1, 1, 20, 0),
			 array(-2, 1, 0, 5, 0),
			 array(-2, 1, -1, 0, 0),
			 array(1, 1, -2, 5, 0),
			 array(0, 1, -2, 102, 0),
			 array(-1, 1, -2, 5, 0),
			array(2, 1, 2, 17, 0),
			 array(-2, 1, 2, 17, 0),
			 array(-2, 1, -2, 17, 0),
			 array(2, 1, -2, 17, 0),
			array(2, 2, -1, 5, 0),
			 array(2, 2, 0, 5, 0),
			 array(2, 2, 1, 5, 0),
			 array(1, 2, 2, 5, 0),
			 array(0, 2, 2, 5, 0),
			 array(-1, 2, 2, 5, 0),
			 array(-2, 2, 1, 5, 0),
			 array(-2, 2, 0, 5, 0),
			 array(-2, 2, -1, 5, 0),
			 array(1, 2, -2, 5, 0),
			 array(0, 2, -2, 5, 0),
			 array(-1, 2, -2, 5, 0),
			array(2, 2, 2, 17, 0),
			 array(-2, 2, 2, 17, 0),
			 array(-2, 2, -2, 17, 0),
			 array(2, 2, -2, 17, 0),
			array(2, 3, -1, 17, 0),
			 array(2, 3, 0, 17, 0),
			 array(2, 3, 1, 17, 0),
			 array(1, 3, 2, 17, 0),
			 array(0, 3, 2, 17, 0),
			 array(-1, 3, 2, 17, 0),
			 array(-2, 3, 1, 17, 0),
			 array(-2, 3, 0, 17, 0),
			 array(-2, 3, -1, 17, 0),
			 array(1, 3, -2, 17, 0),
			 array(0, 3, -2, 17, 0),
			 array(-1, 3, -2, 17, 0),
			array(-2, -1, 1, 4, 0),
			 array(-2, -1, 0, 4, 0),
			 array(-2, -1, -1, 4, 0),
			array(2, -1, 2, 17, 0),
			 array(-2, -1, 2, 17, 0),
			 array(-2, -1, -2, 17, 0),
			 array(2, -1, -2, 17, 0),
			array(2, 3, 2, 17, 0),
			 array(-2, 3, 2, 17, 0),
			 array(-2, 3, -2, 17, 0),
			 array(2, 3, -2, 17, 0),
			 );*/
		$blocks  = array(
			//一段目
			array(0, -1, 0, Block::BEDROCK),
			
			array(0, -1, -1, Block::BEDROCK),
			
			array(0, -1, 1, Block::BEDROCK),
			
			array(-1, -1, 0, Block::BEDROCK),
			
			array(1, -1, 0, Block::BEDROCK),
			
			array(-1, -1, -1, Block::BEDROCK),
			
			array(1, -1, -1, Block::BEDROCK),
			
			array(-1, -1, 1, Block::BEDROCK),
			
			array(1, -1, 1, Block::BEDROCK),
			
			//二段目
			array(0, 0, 0, Block::TORCH),
			
			array(0, 0,-1, Block::BEDROCK),
			
			array(0, 0,1, Block::BEDROCK),
			
			array(-1, 0, 0, Block::BEDROCK),
			
			array(1, 0, 0, Block::BEDROCK),
			
			array(-1, 0,-1, Block::BEDROCK),
			
			array(1, 0,-1, Block::BEDROCK),
			
			array(-1, 0,1, Block::BEDROCK),
			
			array(1, 0,1, Block::BEDROCK),
			
			//三段目
            array(0, -1, 0, Block::AIR),
			
			array(0, 1, -1, Block::IRON_BAR),
			
			array(0, 1, 1, Block::IRON_BAR),
			
			array(-1, 1, 0, Block::IRON_BAR),
			
			array(1, 1, 0, Block::IRON_BAR),
			
			array(-1, 1, -1, Block::BEDROCK),
			
			array(1, 1, -1, Block::BEDROCK),
			
			array(-1, 1, 1, Block::BEDROCK),
			
			array(1, 1, 1, Block::BEDROCK),
			
			//四段目
			array(0, 2, 0, Block::BEDROCK),
			
			array(0, 2, -1, Block::BEDROCK),
			
			array(0, 2, 1, Block::BEDROCK),
			
			array(-1, 2, 0, Block::BEDROCK),
			
			array(1, 2, 0, Block::BEDROCK),
			
			array(-1, 2, -1, Block::BEDROCK),
			
			array(1, 2, -1, Block::BEDROCK),
			
			array(-1, 2, 1, Block::BEDROCK),
			
			array(1, 2, 1, Block::BEDROCK),
			
		);
		return $blocks;
	}
}