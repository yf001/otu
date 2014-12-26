<?php

namespace otu;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\permission\ServerOperator;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;


class otu extends PluginBase implements Listener {
    
    //サーバー開始時の処理//プラグインが有効になると実行されるメソッド
    public function onEnable() {
		$this->saveDefaultConfig();
        $this->reloadConfig();
		@mkdir($this->getDataFolder(), 0755, true);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->otu = new Config($this->getDataFolder() . "otu.yml", Config::YAML, array());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);//イベント登録
    }
    //サーバー停止時の処理//プラグインが無効になると実行されるメソッド
    public function onDisable() {
    }
	
	//コマンド処理
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch (strtolower($command->getName())) {
			case "otu"://otuコマンド実行時の処理
				if(!isset($args[0])){return false;}//例外回避
				$player = $this->getServer()->getPlayer($args[0]);//プレーヤー名取得
				if($player instanceof Player){//プレーヤーが存在するかをチェック
					if(!$this->otu->exists($player->getName())){//otuされてるかを確認!
						$this->otu->set($player->getName(),"true");//otuリストに追加!
						$this->otu->save();//セーブ
						$xyz = explode(',', $this->config->get("xyz"));//x,x,xを配列に変換
						$v = new Vector3($xyz[0], $xyz[1], $xyz[2]);//座標指定
						$player->teleport($v);//ターゲットを指定した座標へtp!
						$sender->sendMessage("[乙] ( ﾟω^ )ゝ " . $player->getName() . "さんを牢屋へTP!しました");//コマンド実行者にメッセージ
						$player->sendMessage("( ﾟω^ )ゝ 荒らし乙であります！");//ターゲットへのめっせーじ
					}else{//セットされていれば以下の処理
						$this->otu->remove($player->getName());//otuリストから削除
						$this->otu->save();//セーブ
						$sender->sendMessage("[乙] ( ﾟω^ )ゝ {$player->getName()}さんをotuリストから削除しました!");//コマンド実行者にメッセージ送信
					}
				}else{
					$sender->sendMessage("[乙] プレーヤーが存在しません");//コマンド実行者にメッセージ送信
				}
				return true;
			break;
			case "otup"://otupコマンド実行時の処理
				if(!($sender instanceof Player)){$sender->sendMessage("[乙] ゲーム内で実行してください");}
					$x = round($sender->getX(), 1);//コマンド実行者のX座標取得&四捨五入
					$y = round($sender->getY(), 1);//コマンド実行者のY座標取得&四捨五入
					$z = round($sender->getZ(), 1);//コマンド実行者のZ座標取得&四捨五入
					$this->config->set("xyz",$x . "," . $y ."," . $z);//設定ファイルに座標を設定
					$this->config->save();//セーブ
					//コマンド実行者へ設定完了メッセージを送信
					$sender->sendMessage("[乙] 牢屋の座標を x:" . $x . " y:" . $y . " z:" . $z . "に設定しました");
				return true;
			break;
			case "runa"://runaコマンド実行時の処理
				if(!isset($args[0])){return false;}//例外回避
				$player = $this->getServer()->getPlayer($args[0]);//プレーヤー名取得
				if($player instanceof Player){//プレーヤーが存在するかをチェック
					if(!$this->otu->exists($player->getName())){//outされてるかを確認!
						$sender->sendMessage("[乙] otuされていません");//コマンド実行者にメッセージ送信
					}else{//セットされていれば以下の処理
						if($this->otu->get($player->getName()) == "blocked"){//blockedになってるか
							$this->otu->set($player->getName(),"true");//runaリストから削除
							$this->otu->save();//セーブ
							$sender->sendMessage("[乙] ( ﾟω^ )ゝ {$player->getName()}さんをrunaリストから削除しました!");//コマンド実行者にメッセージ送信
						}else{//なっていなければ以下の処理
							$this->otu->set($player->getName(),"blocked");//runa化
							$this->otu->save();//セーブ
							$sender->sendMessage("[乙] ( ﾟω^ )ゝ " . $player->getName() . "さんを動けなくしました");//コマンド実行者にメッセージ
							$player->sendMessage("[乙] 動くと罪が重くなりますよ!");//ターゲットへのめっせーじ
						}
					}
				}else{
					$sender->sendMessage("[乙] プレーヤーが存在しません");//コマンド実行者にメッセージ送信
				}
				return true;
			break;
		}
		return false;
	}
	
	//コマンド制限
	public function onPlayerCommand(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		if($this->otu->exists($player->getName())){
			$s = strpos($event->getMessage(), '/');
			if($s == 0 and $s !== false){
				$event->setCancelled(true);
			}
		}
    }
	//移動制限
	public function onPlayerMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		if($this->otu->exists($player->getName())){
			if($this->otu->get($player->getName()) == "blocked"){
				$event->setCancelled();
			}
		}
    }
	
	//ブロックタッチ制限
    public function onPlayerInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($this->otu->exists($player->getName())){
			$event->setCancelled();
		}
    }
	
	//ブロック破壊制限
    public function onBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if($this->otu->exists($player->getName())){
			$event->setCancelled();
		}
    }

	//ブロック設置制限
    public function onBlockPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if($this->otu->exists($player->getName())){
			$event->setCancelled();
		}
    }
}