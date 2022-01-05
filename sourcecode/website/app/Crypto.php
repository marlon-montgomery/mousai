<?php

namespace App;

use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BitWasp\Bitcoin\Key\Deterministic\HierarchicalKey;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use Exception;
use FurqanSiddiqui\Base58\Base58Check;

class Crypto
{
    const PUBLIC_KEY_PREFIXES = [
        'mainnet' => [
            'bitcoin' => [0x00],
            'bitclout' => [0xcd, 0x14, 0x0],
        ],
        'testnet' => [
            'bitcoin' => [0x6f],
            'bitclout' => [0x11, 0xc2, 0x0],
        ]
    ];

    /**
     * @param string $mnemonic
     * @param string|null $extraText
     * @return HierarchicalKey
     * @throws Exception
     */
    public static function mnemonicToKeychain(string $mnemonic, string $extraText = null): HierarchicalKey
    {
        $extraText = $extraText ?? '';

        $generator = new Bip39SeedGenerator();
        $seed = $generator->getSeed($mnemonic, $extraText);

        $factory = new HierarchicalKeyFactory();
        return $factory->fromEntropy($seed)->derivePath("44'/0'/0'/0/0");
    }

    /**
     * @param HierarchicalKey $keychain
     * @return string
     */
    public static function keychainToSeedHex(HierarchicalKey $keychain): string
    {
        return $keychain->getPrivateKey()->getHex();
    }

    /**
     * @param string $seedHex
     * @return PrivateKeyInterface
     * @throws Exception
     */
    public static function seedHexToPrivateKey(string $seedHex): PrivateKeyInterface
    {
        $factory = new PrivateKeyFactory();
        return $factory->fromHexCompressed($seedHex);
    }

    /**
     * @param string $hex
     * @return array
     */
    protected static function hex2ByteArray(string $hex): array
    {
        $string = hex2bin($hex);
        return unpack('C*', $string);
    }

    /**
     * @param array $byte
     * @return string
     */
    protected static function byteArray2Hex(array $byte): string
    {
        $chars = array_map("chr", $byte);
        $bin = join($chars);
        return bin2hex($bin);
    }

    /**
     * @param PrivateKeyInterface $privateKey
     * @param string $network
     * @return string
     * @throws Exception
     */
    public static function privateKeyToBitcloutPublicKey(PrivateKeyInterface $privateKey, string $network = 'mainnet'): string
    {
        $prefixKey = [
            ...self::PUBLIC_KEY_PREFIXES[$network]['bitclout'],
            ...self::hex2ByteArray($privateKey->getPublicKey()->getHex())
        ];

        return (new Base58Check)->encode(self::byteArray2Hex($prefixKey))->value();
    }
}
