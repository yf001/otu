<?php

namespace otu;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest;

class jail{

	private static $obj = null;
	
	//コンストラクタ//このクラスが呼び出されると実行されるメソッド
	public function __construct(){
		if(!self::$obj instanceof Jail){
			self::$obj = $this;
		}
		$this->old = array();//変数を初期化
		$this->pos = array();//変数を初期化
		//メインクラスのオブジェクトをプラグインマネージャーから名前で取得
		$this->plugin = Server::getInstance()->getPluginManager()->getPlugin("otu");
		//牢屋のコンフィグファイルの生成
		$this->jail = new Config($this->plugin->getDataFolder() . "jail.yml", Config::YAML);
		$data = $this->jail->getAll();//牢屋のコンフィグファイルからすべてのデータを配列で取得
		$blocksc = array();//変数を初期化
		if(count($data) > 0){//変数に配列があるか
			foreach($data as $name => $blocks){//取得したデータを取り出して以下の処理を行う
				foreach($blocks as $val){
					foreach($val as $val2){
						$blocksc[] = explode(",",$val2);// , で区切って配列にする
					}
				}
				//ブロックデータを , で区切って配列化
				$this->jailtype[$name] = $blocksc;
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
		//$blocks = $this->getJailStructure();
		$blocks = $this->getJailType($type);
		$level = $player->getLevel();
		$data = array("level" => $level->getName());
		foreach($blocks as $key => $val){
			//[0] X座標,[1] Y座標,[2] Z座標,[3] BlockID,[4] メタ値
			$bx = $x + $val[0];//プレーヤーのいる場所からブロックを設置する座標を決める
			$by = $y + $val[1];//〃Y
			$bz = $z + $val[2];//〃Z
			$id = $val[3];//ブロックのid
			$bid = $level->getBlockIdAt($bx, $by ,$bz);
			//$mata = $val[4];
			$mata = (isset($val[4])) ? $val[4]:0;
			//デバッグ用//
			$this->plugin->getLogger()->info("key." . $key . " x." . $x . " y." . $y . " z." . $z . " id." .$id . " mata." . $mata);
			if($bid == Block::WALL_SIGN or $bid == Block::SIGN_POST or $bid == Block::CHEST){
				$tile = $player->getLevel()->getTile(new Vector3($bx, $by, $bz));
				if($tile instanceof Tile){
					if($tile instanceof Sign){
						$text = $tile->getText();
						$data[] = array($bx, $by, $bz, $level->getBlockIdAt($bx, $by ,$bz),$level->getBlockDataAt($bx, $by ,$bz),$text);
					}elseif($tile instanceof Chest){
						$slot = $tile->getInventory()->getContents();
						$data[] = array($bx, $by, $bz, $level->getBlockIdAt($bx, $by ,$bz),$level->getBlockDataAt($bx, $by ,$bz),$slot);
					}
				}
			}else{
				$data[] = array($bx, $by, $bz, $level->getBlockIdAt($bx, $by ,$bz),$level->getBlockDataAt($bx, $by ,$bz));
			}
			$block = Block::get($id,$mata);
			$pos = new Vector3($bx, $by, $bz);
			$level->setBlock($pos, $block);
		}
		$pos = new Position($x + 0.5, $y, $z + 0.5,$level);
		$player->teleport($pos);
		//デバッグ用//
		var_dump($blocks);
		var_dump($data);
		$this->old[$sender->getName()][] = $data;
		return true;
	}
	
	//牢屋を元に戻す
	public function unJail($player){
		if($player instanceof Player){//$playerがプレーヤーの場合は名前に変換
			$player = $player->getName();
		}
		if(isset($this->old[$player])){
			//[0] X座標,[1] Y座標,[2] Z座標,[3] BlockID,[4] メタ値,[5] 看板のテキスト
			$c = count($this->old[$player]);
			if($c <= 0){unset($this->old[$player]);return false;}//配列がない場合は終了
			$data = end($this->old[$player]);//最後の方にある配列を取り出す
			if(isset($data["level"])){
				$level = Server::getInstance()->getLevelByName($data["level"]);
				if(!($level instanceof Level)){
					$level = $this->getServer()->getDefaultLevel();
				}
			}else{
				$level = $this->getServer()->getDefaultLevel();
			}
			foreach($data as $key => $val){
				if($key === "level"){continue;}//キーの名前がlevelの場合はこの処理をスキップ
				$block = Block::get($val[3], $val[4]);
				$pos = new Vector3($val[0], $val[1], $val[2]);
				$level->setBlock($pos, $block);
				if(isset($val[5])){
					if($block->getId() == Block::SIGN_POST or $block->getId() == Block::WALL_SIGN){
						$sign = $level->getTile(new Vector3($val[0], $val[1], $val[2]));
						if($sign instanceof Tile){
							$sign->setText($val[5][0], $val[5][1], $val[5][2], $val[5][3]);
							$sign->saveNBT();
						}
					}elseif($block->getId() == Block::CHEST){
						$chest = $level->getTile(new Vector3($val[0], $val[1], $val[2]));
						if($chest instanceof Tile){
							$chest->getInventory()->setContents($val[5]);
							$chest->saveNBT();
						}
					}
					
				}
				//デバッグ用//
				//var_dump($val);
			}
			unset($data);
			return true;
		}else{
			return false;
		}
	}

	//すべての牢屋を元に戻す
	public function unJailAll(){
		$olddata = $this->old;
		foreach($olddata as $key => $val){
			$this->unjail($key);
		}
		return true;
	}
	
	public function getJailType($type = null){//読み込まれたブロックデータを選べるようにする
		if(isset($this->jailtype[$type])){
			return $this->jailtype[$type];
		}else{
			return $this->getJailStructure();
		}
	}
	
	public function craftJail($player, $name = null){//牢屋自体を作成できるように
    	if(!isset($name)){return false;}
		$blocks = $this->jailBlocks($player);
		if(($blocks !== false) and !($this->jail->exists($name))){
			if(!$this->jail->exists($name)){
				$this->jail->set($name,$blocks);
				$this->jail->save();
			}
			$this->jailtype[$name] = $blocks;
			return true;
		}else{
			return false;
		}
	}
	
	//指定された範囲のブロックをデータ化
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
			$px = round($pos[3]["x"] - 0.5);
			$py = round($pos[3]["y"] - 0.5);
			$pz = round($pos[3]["z"] - 0.5);
			$data = array();
			$c = 0;
			for($x = $sx; $x <= $ex; ++$x){
				for($y = $sy; $y <= $ey; ++$y){
					for($z = $sz; $z <= $ez; ++$z){
						$cx = $px - $x;
						$cy = $py - $y;
						$cz = $pz - $z;
						//data変数にブロックデータを入れる//変数の前に-つけて符号を反転
						$data[] = array($c => -$cx . "," . -$cy . "," . -$cz . "," . $level->getBlockIdAt($x, $y ,$z) . "," . $level->getBlockDataAt($x, $y ,$z));
						$c++;
					}
				}
			}
			return $data;
		}else{
			return false;
		}
	}
	
	//通常の牢屋
	public function getJailStructure(){
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
			array(0, 1, 0, Block::AIR),
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