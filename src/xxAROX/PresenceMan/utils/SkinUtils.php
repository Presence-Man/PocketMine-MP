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
	public static function getHead(Player $player, ?Skin $skin): ?string{
		$head = self::getFace($skin);
		if (!$head instanceof GdImage) return null;

		$ip = $player->getNetworkSession()->getIp();
		$xuid = $player->getXuid();
		$tmp_file = PresenceMan::getInstance()->getDataFolder() . ".cache-" - $xuid;
		@imagepng($head, $tmp_file);
		@imagedestroy($head);
		$data = base64_encode(Filesystem::fileGetContents($tmp_file));
		@Filesystem::recursiveUnlink($tmp_file);
		return $data;
	}
	private static function fromSkinToImage(Skin $skin): GdImage|bool{
		return self::toImage($skin->getSkinData(), self::getHeight($skin), self::getWidth($skin));
	}
	private static function getHeight(Skin $skin): int{
		return SkinImage::fromLegacy($skin->getSkinData())->getHeight();
	}
	private static function getWidth(Skin $skin): int{
		return SkinImage::fromLegacy($skin->getSkinData())->getWidth();
	}
	private static function getImageSize(GdImage $image): array{
		return [ imagesx($image), imagesy($image) ];
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
	private static function getFace(Skin|GdImage|string $image): GdImage|bool{
		if ($image instanceof Skin) $image = self::fromSkinToImage($image);
		if (is_string($image)) $image = imagecreatefrompng($image);
		[ $width, $height ] = self::getImageSize($image);
		$face = imagecreatetruecolor($height, $width);
		imagefill($face, 0, 0, imagecolorallocatealpha($face, 0, 0, 0, 127));
		imagecolordeallocate($face, imagecolorallocate($face, 0, 0, 0));
		imagesavealpha($face, true);
		switch ([ $width, $height ]) {
			case [ 32, 32 ]:
				[ $xy, $w, $h, $x, $y ] = [ 16, 8, 8, 40, 8 ];
				break;
			case [ 128, 128 ]:
				$rgb = imagecolorat($image, 8, 8);
				$colors = imagecolorsforindex($image, $rgb);
				if (!($colors["red"] == 0 && $colors["green"] == 0 && $colors["blue"] == 0 && $colors["alpha"] == 0)) {
					[ $xy, $w, $h, $x, $y ] = [ 8, 8, 8, 40, 8 ];
				}
				else {
					[ $xy, $w, $h, $x, $y ] = [ 16, 16, 16, 80, 16 ];
				}
				break;
			default:
				[ $xy, $w, $h, $x, $y ] = [ 8, 8, 8, 40, 8 ];
		}
		imagecopyresized($face, $image, 0, 0, $xy, $xy, $height, $width, $w, $h);
		if (!($height == 32 && $width == 64)) imagecopyresized($face, $image, 0, 0, $x, $y, $height, $width, $w, $h);
		return $face;
	}
}