<?php
declare(strict_types=1);

namespace ReservationManager\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use ReservationManager\ReservationManager;

class MainCommand extends Command
{

    protected $plugin;

    public function __construct(ReservationManager $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct('사전예약', '사전예약 명령어.', '/사전예약');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $encode = [
            'type' => 'form',
            'title' => '§l§b[사전예약]',
            'content' => '§r§7버튼을 눌러주세요.',
            'buttons' => [
                [
                    'text' => '§l§b[플레이어추가]'
                ],
                [
                    'text' => '§l§b[플레이어제거]'
                ],
                [
                    'text' => '§l§b[아이템추가]'
                ],
                [
                    'text' => '§l§b[아이템제거]'
                ]
            ]
        ];
        $packet = new ModalFormRequestPacket ();
        $packet->formId = 3728293883;
        $packet->formData = json_encode($encode);
        $sender->sendDataPacket($packet);
    }
}
