<?php
declare(strict_types=1);

namespace ReservationManager;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ReservationManager\Commands\MainCommand;
use pocketmine\scheduler\Task;
use pocketmine\Server;
//한글깨짐방지
class ReservationManager extends PluginBase
{
  protected $config;
  public $db;
  private static $instance = null;
  
  public static function getInstance(): ReservationManager
  {
    return static::$instance;
  }
  
  public function onLoad()
  {
    self::$instance = $this;
  }
  
  public function onEnable()
  {
    $this->player = new Config ($this->getDataFolder() . "players.yml", Config::YAML);
    $this->pldb = $this->player->getAll();
    $this->item = new Config ($this->getDataFolder() . "item.yml", Config::YAML);
    $this->itemsdb = $this->item->getAll();
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    $this->getServer()->getCommandMap()->register('ReservationManager', new MainCommand($this));
  }
  public function getPlayerLists() : array{
    $arr = [];
    foreach($this->pldb as $ReservationManager => $v){
      array_push($arr, $ReservationManager);
    }
    return $arr;
  }
  public function getItemLists() : array{
    $arr = [];
    foreach($this->itemdb as $ReservationManager => $v){
      array_push($arr, $ReservationManager);
    }
    return $arr;
  }
  public function onDisable()
  {
    $this->save();
  }
  
  public function save()
  {
    $this->player->setAll($this->pldb);
    $this->player->save();
    $this->item->setAll($this->itemsdb);
    $this->item->save();
  }
}
