<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe;

use Closure;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\BinaryStream;
use function log;
use function spl_object_id;
use function strlen;

final class NetworkBroadcastUtils{

	private function __construct(){
		//NOOP
	}

	/**
	 * @param NetworkSession[]    $recipients
	 * @param ClientboundPacket[] $packets
	 *
	 * Expecting StandardPacketBroadcaster
	 */
	public static function broadcastPacketsToSession(array $recipients, array $packets, bool $callEvent = true) : void{
		if(empty($recipients)){
			return;
		}

		if($callEvent && DataPacketSendEvent::hasHandlers()){
			$ev = new DataPacketSendEvent($recipients, $packets);
			$ev->call();
			if($ev->isCancelled()){
				return;
			}
			$packets = $ev->getPackets();
		}

		$compressor = ZlibCompressor::getInstance(); // pmmp hardcode gaming

		$totalLength = 0;
		$batchBuffer = new BinaryStream();
		$packetBuffers = [];
		foreach($packets as $pk){
			$buffer = NetworkSession::encodePacketTimed(PacketSerializer::encoder(), $pk);
			$bufferLen = strlen($buffer);
			$totalLength += 2 + $bufferLen;

			// encodeRaw
			$batchBuffer->putUnsignedVarInt($bufferLen);
			$batchBuffer->put($buffer);
			$packetBuffers[] = $buffer;
		}

		$threshold = $compressor->getCompressionThreshold();

		if($threshold !== null && $totalLength >= $threshold){
			$batch = Server::getInstance()->prepareBatch($batchBuffer->getBuffer(), $compressor, timings: Timings::$playerNetworkSendCompressBroadcast);
			foreach($recipients as $recipient){
				if(!$recipient->isConnected()){
					continue;
				}
				$recipient->queueCompressed($batch);
			}
		}else{
			foreach($recipients as $recipient){
				if(!$recipient->isConnected()){
					continue;
				}
				foreach($packetBuffers as $buffer){
					$recipient->addToSendBuffer($buffer);
				}
			}
		}
	}

	/**
	 * @param Player[]            $recipients
	 * @param ClientboundPacket[] $packets
	 *
	 * Expecting StandardPacketBroadcaster
	 */
	public static function broadcastPackets(array $recipients, array $packets, bool $callEvent = true) : void{
		if(empty($recipients)){
			return;
		}

		if($callEvent && DataPacketSendEvent::hasHandlers()){
			$sessions = [];
			foreach($recipients as $player){
				$sessions[] = $player->getNetworkSession();
			}
			$ev = new DataPacketSendEvent($sessions, $packets);
			$ev->call();
			if($ev->isCancelled()){
				return;
			}
			$packets = $ev->getPackets();
		}

		$compressor = ZlibCompressor::getInstance(); // pmmp hardcode gaming

		$totalLength = 0;
		$batchBuffer = new BinaryStream();
		$packetBuffers = [];
		foreach($packets as $pk){
			$buffer = NetworkSession::encodePacketTimed(PacketSerializer::encoder(), $pk);
			$bufferLen = strlen($buffer);
			$totalLength += 2 + $bufferLen;

			// encodeRaw
			$batchBuffer->putUnsignedVarInt($bufferLen);
			$batchBuffer->put($buffer);
			$packetBuffers[] = $buffer;
		}

		$threshold = $compressor->getCompressionThreshold();

		if($threshold !== null && $totalLength >= $threshold){
			$batch = Server::getInstance()->prepareBatch($batchBuffer->getBuffer(), $compressor, timings: Timings::$playerNetworkSendCompressBroadcast);
			foreach($recipients as $recipient){
				if(!$recipient->isOnline()){
					continue;
				}
				$recipient->getNetworkSession()->queueCompressed($batch);
			}
		}else{
			foreach($recipients as $recipient){
				if(!$recipient->isOnline()){
					continue;
				}
				foreach($packetBuffers as $buffer){
					$recipient->getNetworkSession()->addToSendBuffer($buffer);
				}
			}
		}
	}

	/**
	 * @param Player[] $recipients
	 *
	 * @phpstan-param Closure(EntityEventBroadcaster, array<int, NetworkSession>) : void $callback
	 */
	public static function broadcastEntityEvent(array $recipients, Closure $callback) : void{
		$uniqueBroadcasters = [];
		$broadcasterTargets = [];

		foreach($recipients as $recipient){
			$session = $recipient->getNetworkSession();
			$broadcaster = $session->getEntityEventBroadcaster();
			$uniqueBroadcasters[spl_object_id($broadcaster)] = $broadcaster;
			$broadcasterTargets[spl_object_id($broadcaster)][spl_object_id($session)] = $session;
		}

		foreach($uniqueBroadcasters as $k => $broadcaster){
			$callback($broadcaster, $broadcasterTargets[$k]);
		}
	}

	/**
	 * @param NetworkSession[] $recipients
	 *
	 * @phpstan-param Closure(EntityEventBroadcaster, array<int, NetworkSession>) : void $callback
	 */
	public static function broadcastEntityEventToSession(array $recipients, Closure $callback) : void{
		$broadcaster = new StandardEntityEventBroadcaster(new StandardPacketBroadcaster(Server::getInstance()), TypeConverter::getInstance());

		$callback($broadcaster, $recipients);
	}
}
