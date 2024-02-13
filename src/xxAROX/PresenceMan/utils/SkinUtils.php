<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * Only people with the explicit permission from Jan Sohn are allowed to modify, share or distribute this code.
 *
 * For dummies:
 *  - You are NOT allowed to do any kind of modification to this code without the explicit permission from Jan Sohn.
 *  - You are NOT allowed to share this code with others without the explicit permission from Jan Sohn.
 *  - You are NOT allowed to run this code on your server without the explicit permission from Jan Sohn.
 *  - You are NOT allowed to run this compiled-code on your server without the explicit permission from Jan Sohn.
 *
 *
 *  IF YOU STEAL ANYTHING OF THIS CONTENT YOU CAN SUCK MY BALLS AND YOU ARE A LITTLE KID THAT SUCKS
 *
 *  ~xxAROX aka. Jan Sohn
 *
 */
declare(strict_types=1);
namespace xxAROX\PresenceMan\utils;
use GdImage;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use pocketmine\player\Player;
use pocketmine\utils\Filesystem;
use xxAROX\PresenceMan\PresenceMan;


/**
 * Class SkinUtils
 * @package xxAROX\PMBridge\util
 * @author Jan Sohn / xxAROX
 * @date 04. Mai, 2023 - 16:40
 * @ide PhpStorm
 * @project xxCLOUD-Bridge
 */
final class SkinUtils{
	public static function getSkin(Player $player, ?Skin $skin): ?string{
		$image = self::fromSkinToImage($skin);
		if (!$image instanceof GdImage) return null;
		$xuid = $player->getXuid();
		$tmp_file = PresenceMan::getInstance()->getDataFolder() . ".cache-" . $xuid;
		@imagepng($image, $tmp_file);
		@imagedestroy($image);
		$data = base64_encode(Filesystem::fileGetContents($tmp_file));
		@Filesystem::recursiveUnlink($tmp_file);
		return $data;
	}
	private static function fromSkinToImage(Skin $skin): GdImage|bool{
		$skinImage = SkinImage::fromLegacy($skin->getSkinData());
		return self::toImage($skin->getSkinData(), $skinImage->getHeight(), $skinImage->getWidth());
	}
	private static function toImage(string $data, int $height, int $width): GdImage|bool{
		$pixelArray = str_split(bin2hex($data), 8);
		$image = imagecreatetruecolor($width, $height);
		imagealphablending($image, false);
		imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
		imagesavealpha($image, true);
		$position = count($pixelArray) - 1;
		while (!empty($pixelArray)) {
			$x = $position % $width;
			$color = array_map(fn(string $val) => hexdec($val), str_split(array_pop($pixelArray), 2));
			$color[] = ((~((int)array_pop($color))) & 0xff) >> 1;
			imagesetpixel($image, $x, ($position - $x) / $height, imagecolorallocatealpha($image, ...$color));
			$position--;
		}
		return $image;
	}
}
