<?php

use pocketmine\utils\BinaryStream;

include __DIR__ . '/vendor/autoload.php';

class CompiledScript {
	private string $code;
	private array $start = [];
	private array $end = [];
	private string $byteCode;
	
	/**
	 * @throws Exception
	 */
	public function __construct(string $byteCode) {
		$this->byteCode = $byteCode;
		$stream = new BinaryStream($byteCode);
		if ($stream->getByte() !== 1) {
			throw new Exception('Unknown Script Version');
		}
		
		$this->code = $stream->get($stream->getUnsignedVarLong());
		for ($t = 0, $max = $stream->getUnsignedVarLong(); $t < $max; $t++) {
			$start = $stream->getUnsignedVarLong();
			$end = $stream->getUnsignedVarLong();
			$this->start[$start] = $end;
			$this->end[$end] = $start;
		}
	}
	
	/**
	 * @throws Exception
	 */
	public static function compile(string $code, array $goto) : CompiledScript {
		$stream = new BinaryStream();
		$stream->putByte(1);
		$stream->putUnsignedVarLong(strlen($code));
		$stream->put($code);
		$stream->putUnsignedVarLong(count($goto));
		foreach ($goto as $entry) {
			$stream->putUnsignedVarLong($entry->start);
			$stream->putUnsignedVarLong($entry->end);
		}
		return new CompiledScript($stream->getBuffer());
	}
	
	public function getByteCode() : string {
		return $this->byteCode;
	}
	
	public function getCode() : string {
		return $this->code;
	}
	
	public function getStart() : array {
		return $this->start;
	}
	
	public function getEnd() : array {
		return $this->end;
	}
}
