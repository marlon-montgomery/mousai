<?php


namespace App;


use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Signature\Signature;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Buffertools;
use Crypt;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Log;

class BitClout
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var string
     */
    protected string $baseUrl;

    protected $lastError = null;

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = config('bitclout.api_url');
    }

    /**
     * @param string $caption
     * @param string $public_key
     * @return string|null
     */
    public function submitPost(string $caption, string $public_key): ?string
    {
        $data = [
            'BodyObj' => [
                'Body' => $caption,
                'ImageURLs' => [],
            ],
            'IsHidden' => false,
            'MinFeeRateNanosPerKB' => 1000,
            'ParentStakeID' => '',
            'PostExtraData' => [],
            'PostHashHexToModify' => '',
            'RecloutedPostHashHex' => '',
            'Sub' => '',
            'Title' => '',
            'UpdaterPublicKeyBase58Check' => $public_key,
        ];

        try {
            $response = $this->client->post("{$this->baseUrl}submit-post", [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json'
                ],
                RequestOptions::BODY => str_replace('"PostExtraData":[]', '"PostExtraData": {}', json_encode($data))
            ]);
            $object = json_decode($response->getBody()->getContents());

            return $object->TransactionHex ?? null;
        } catch (GuzzleException $exception) {
            $response = $exception->getResponse();
            $this->lastError = json_decode($response->getBody()->getContents(), true);

            Log::error($exception);
            return null;
        }
    }

    /**
     * @param string $signedTransactionHex
     * @throws GuzzleException
     */
    public function submitTransaction(string $signedTransactionHex)
    {
        $this->client->post("{$this->baseUrl}submit-transaction", [
            RequestOptions::JSON => [
                'TransactionHex' => $signedTransactionHex
            ]
        ]);
    }

    /**
     * @return array
     * @throws Exception
     */
    protected static function Account(): array
    {
        $seed = config('bitclout.account.seed');
        $passphrase = config('bitclout.account.passphrase');

        if ($seed === null)
            throw new Exception('You must provide a seed phrase for notification service to function.');

        $keychain = Crypto::mnemonicToKeychain($seed, $passphrase);
        $private_key = Crypto::keychainToSeedHex($keychain);
        $private_key = Crypto::seedHexToPrivateKey($private_key);

        return [Crypto::privateKeyToBitcloutPublicKey($private_key), $private_key];
    }

    /**
     * @param string $caption
     * @throws GuzzleException
     * @throws Exception
     */
    public function submit(string $caption)
    {
        /** @var PrivateKeyInterface $private_key */
        list($public_key, $privateKey) = self::Account();

        $transactionHex = $this->submitPost($caption, $public_key);
        $signedTransactionHex = $this->signTransactionHex($transactionHex, $privateKey);

        $this->submitTransaction($signedTransactionHex);
    }

    /**
     * @param string $transactionHex
     * @param PrivateKeyInterface $privateKey
     * @return string
     * @throws Exception
     */
    public function signTransactionHex(string $transactionHex, PrivateKeyInterface $privateKey): string
    {
        $transactionBytes = Buffer::hex($transactionHex);
        $transactionHash = Hash::sha256(Hash::sha256($transactionBytes));

        /** @var Signature $signature */
        $signature = $privateKey->sign($transactionHash);
        $signatureBytes = $signature->getBuffer();
        $signatureLength = Buffertools::numToVarInt($signatureBytes->getSize());

        /** @var Buffer $buffer */
        $buffer = Buffertools::concat($transactionBytes->slice(0, -1), $signatureLength);
        $buffer = Buffertools::concat($buffer, $signatureBytes);

        return $buffer->getHex();
    }
}
