<?php

namespace ethaniccc\Mockingbird\detections\combat\aimassist;

use ethaniccc\Mockingbird\detections\Detection;
use ethaniccc\Mockingbird\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;

class AimAssistA extends Detection{

    public function __construct(string $name, ?array $settings){
        parent::__construct($name, $settings);
    }

    public function handle(DataPacket $packet, User $user): void{
        if($packet instanceof PlayerAuthInputPacket){
            $yawDelta = $user->moveData->yawDelta;
            $pitchDelta = $user->moveData->pitchDelta;
            if($user->timeSinceAttack <= 10 && $user->moveData->rotated){
                // (almost) impossible for pitch delta to be so low in this case
                if($yawDelta > 1 && $yawDelta < 5 && $pitchDelta === 0.0 && abs($user->moveData->pitch) < 85){
                    if(++$this->preVL >= 5){
                        $this->fail($user, "yawDelta=$yawDelta pitchDelta=$pitchDelta");
                    }
                } else {
                    $this->preVL -= $this->preVL > 0 ? 1 : 0;
                    $this->reward($user, 0.999);
                }
                if($this->isDebug($user)){
                    $user->sendMessage("yaw=$yawDelta pitch=$pitchDelta buff={$this->preVL}");
                }
            }
        }
    }

}