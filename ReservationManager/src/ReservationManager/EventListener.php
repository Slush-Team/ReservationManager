<?php
declare(strict_types=1);

namespace ReservationManager;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

class EventListener implements Listener
{
  
  protected $plugin;
  
  public function __construct(ReservationManager $plugin)
  {
    $this->plugin = $plugin;
  }
  public function OnJoin(PlayerJoinEvent $event)
  {
    $player = $event->getPlayer();
    $name = $player->getName();
    $tag = "§l§b[사전예약]§r§7 ";
    if (isset ( $this->plugin->pldb [strtolower ( $name )])){
      if ($this->plugin->pldb [strtolower ( $name )] ["사전보상"] == "미수령") {
        foreach($this->plugin->itemsdb as $data => $v){
          $Nbt = Item::jsonDeserialize ( $this->plugin->itemsdb [$data] );
          $player->getInventory ()->addItem ( $Nbt );
        }
        $this->plugin->pldb [strtolower ( $name )] ["사전보상"] = "수령완료";
        $this->plugin->save();
        $player->sendMessage($tag . "사전예약 보상이 지급되었습니다.");
        return true;
      }
    }
  }
  public function onPacket(DataPacketReceiveEvent $event)
  {
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    $name = $player->getName();
    $tag = "§l§b[사전예약]§r§7 ";
    if ($packet instanceof ModalFormResponsePacket) {
      $id = $packet->formId;
      $data = json_decode($packet->formData, true);
      if ($id === 3728293883) {
        if ($data === 0) {
          $this->AddPlayer($player);
          return true;
        }
        if ($data === 1) {
          $this->SetPlayer($player);
          return true;
        }
        if ($data === 2) {
          $this->AddItem($player);
          return true;
        }
        if ($data === 3) {
          $this->SetItem($player);
          return true;
        }
      }
      if ($id === 3728293884) {
        if(!isset($data[0])){
          $player->sendMessage($tag . "플레이어 닉네임을 적어주세요.");
          return true;
        }
        $this->plugin->pldb [strtolower ( $data[0] )] ["사전보상"] = "미수령";
        $this->plugin->save();
        return true;
      }
      if ($id === 3728293885) {
        if($data !== null){
          $arr = [];
          foreach($this->plugin->getPlayerLists() as $AdvanceAPI){
            array_push($arr, $AdvanceAPI);
          }
          $player->sendMessage($tag . "해당 플레이어를 제거 했습니다.");
          unset ($this->plugin->pldb [strtolower ( $arr[$data] )]);
          $this->plugin->save ();
          return true;
        }
      }
      if ($id === 3728293886) {
        if(!isset($data[0])){
          $player->sendMessage($tag . "플레이어 닉네임을 적어주세요.");
          return true;
        }
        if(!isset($data[1])){
          $player->sendMessage($tag . "갯수를 적어주세요.");
          return true;
        }
        if(!is_numeric($data[1])){
          $player->sendMessage($tag . "갯수는 숫자로만 가능합니다.");
          return true;
        }
        $item = $player->getInventory()->getItemInHand();
        $item->setCount((int) $data[1]);
        $nbt = $item->jsonSerialize ();
        $this->plugin->itemsdb [$data[0]] = $nbt;
        return true;
      }
      if ($id === 3728293887) {
        if($data !== null){
          $arr = [];
          foreach($this->plugin->getItemLists() as $AdvanceAPI){
            array_push($arr, $AdvanceAPI);
          }
          $player->sendMessage($tag . "해당 아이템을 제거 했습니다.");
          unset ($this->plugin->itemsdb [$arr[$data]]);
          $this->plugin->save ();
          return true;
        }
      }
    }
  }
  public function AddPlayer(Player $player)
  {
    $encode = [
      'type' => 'custom_form',
      'title' => '§l§b[사전예약]',
      'content' => [
        [
          'type' => 'input',
          'text' => "§r§7추가할 플레이어 이름을 적어주세요."
        ]
      ]
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 3728293884;
    $packet->formData = json_encode($encode);
    $player->sendDataPacket($packet);
    return true;
  }
  public function SetPlayer(Player $player)
  {
    $arr = [];
    foreach($this->plugin->getPlayerLists() as $list){
      array_push($arr, array('text' => '- ' . $list));
    }
    $encode = [
      'type' => 'form',
      'title' => '§l§b[사전예약]',
      'content' => '§r§7제거할 플레이어를 선택해주세요.',
      'buttons' => $arr
    ];
    $packet = new ModalFormRequestPacket();
    $packet->formId = 3728293885;
    $packet->formData = json_encode($encode);
    $player->sendDataPacket($packet);
    return true;
  }
  public function AddItem(Player $player)
  {
    $encode = [
      'type' => 'custom_form',
      'title' => '§l§b[사전예약]',
      'content' => [
        [
          'type' => 'input',
          'text' => "§r§7해당 보상의 이름을 적어주세요."
        ],
        [
          'type' => 'input',
          'text' => "§r§7해당 보상의 갯수를 적어주세요."
        ]
      ]
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 3728293886;
    $packet->formData = json_encode($encode);
    $player->sendDataPacket($packet);
    return true;
  }
  public function SetItem(Player $player)
  {
    $arr = [];
    foreach($this->plugin->getItemLists() as $list){
      array_push($arr, array('text' => '- ' . $list));
    }
    $encode = [
      'type' => 'form',
      'title' => '§l§b[사전예약]',
      'content' => '§r§7제거할 아이템을 선택해주세요.',
      'buttons' => $arr
    ];
    $packet = new ModalFormRequestPacket();
    $packet->formId = 3728293887;
    $packet->formData = json_encode($encode);
    $player->sendDataPacket($packet);
    return true;
  }
}
