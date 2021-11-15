<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;

class_exists(InstalledVersions::class);

/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'laravel/laravel';

    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS          = array (
  'algolia/algoliasearch-client-php' => '3.1.0@42793a97c99a68adfe8e39eadadd970b5196f208',
  'asm89/stack-cors' => 'v2.0.3@9cb795bf30988e8c96dd3c40623c48a877bc6714',
  'aws/aws-crt-php' => 'v1.0.2@3942776a8c99209908ee0b287746263725685732',
  'aws/aws-sdk-php' => '3.197.1@e3353c1d908295b4a804e13da6b2d6bfeec2732b',
  'brick/math' => '0.9.3@ca57d18f028f84f777b2168cd1911b0dee2343ae',
  'clue/stream-filter' => 'v1.5.0@aeb7d8ea49c7963d3b581378955dbf5bc49aa320',
  'cocur/slugify' => 'v4.0.0@3f1ffc300f164f23abe8b64ffb3f92d35cec8307',
  'composer/package-versions-deprecated' => '1.11.99.4@b174585d1fe49ceed21928a945138948cb394600',
  'doctrine/cache' => '2.1.1@331b4d5dbaeab3827976273e9356b3b453c300ce',
  'doctrine/dbal' => '3.1.3@96b0053775a544b4a6ab47654dac0621be8b4cf8',
  'doctrine/deprecations' => 'v0.5.3@9504165960a1f83cc1480e2be1dd0a0478561314',
  'doctrine/event-manager' => '1.1.1@41370af6a30faa9dc0368c4a6814d596e81aba7f',
  'doctrine/inflector' => '2.0.3@9cf661f4eb38f7c881cac67c75ea9b00bf97b210',
  'doctrine/lexer' => '1.2.1@e864bbf5904cb8f5bb334f99209b48018522f042',
  'dragonmantank/cron-expression' => 'v3.1.0@7a8c6e56ab3ffcc538d05e8155bb42269abf1a0c',
  'egulias/email-validator' => '2.1.25@0dbf5d78455d4d6a41d186da50adc1122ec066f4',
  'elasticsearch/elasticsearch' => 'v7.15.0@77a4ade87aef8e8e6b84bafb6704cd35ac15742a',
  'ezimuel/guzzlestreams' => '3.0.1@abe3791d231167f14eb80d413420d1eab91163a8',
  'ezimuel/ringphp' => '1.1.2@0b78f89d8e0bb9e380046c31adfa40347e9f663b',
  'fideloper/proxy' => '4.4.1@c073b2bd04d1c90e04dc1b787662b558dd65ade0',
  'firebase/php-jwt' => 'v5.4.0@d2113d9b2e0e349796e72d2a63cf9319100382d2',
  'fruitcake/laravel-cors' => 'v2.0.4@a8ccedc7ca95189ead0e407c43b530dc17791d6a',
  'gliterd/backblaze-b2' => '1.5.1@568b9513a040712f3a8a737b7d107cce3c45c2e7',
  'google/apiclient' => 'v2.11.0@7db9eb40c8ba887e81c0fe84f2888a967396cdfb',
  'google/apiclient-services' => 'v0.215.0@1d4d488c09a9bb5f361f8d7ddd8dbb37cb7786ac',
  'google/auth' => 'v1.18.0@21dd478e77b0634ed9e3a68613f74ed250ca9347',
  'graham-campbell/guzzle-factory' => 'v5.0.0@5f6eae0dba2f2a02d72d42f11f3ff9f9f61e1cc8',
  'graham-campbell/result-type' => 'v1.0.2@84afea85c6841deeea872f36249a206e878a5de0',
  'guzzle/guzzle' => 'v3.8.1@4de0618a01b34aa1c8c33a3f13f396dcd3882eba',
  'guzzlehttp/guzzle' => '7.3.0@7008573787b430c1c1f650e3722d9bba59967628',
  'guzzlehttp/promises' => '1.4.1@8e7d04f1f6450fef59366c399cfad4b9383aa30d',
  'guzzlehttp/psr7' => '1.8.3@1afdd860a2566ed3c2b0b4a3de6e23434a79ec85',
  'http-interop/http-factory-guzzle' => '1.2.0@8f06e92b95405216b237521cc64c804dd44c4a81',
  'intervention/image' => '2.7.0@9a8cc99d30415ec0b3f7649e1647d03a55698545',
  'james-heinrich/getid3' => 'v1.9.21@36f5dabb1325415a4b07a401113f8db2eb81eca1',
  'jaybizzle/crawler-detect' => 'v1.2.106@78bf6792cbf9c569dc0bf2465481978fd2ed0de9',
  'jean85/pretty-package-versions' => '2.0.4@694492c653c518456af2805f04eec445b997ed1f',
  'jenssegers/agent' => 'v2.6.4@daa11c43729510b3700bc34d414664966b03bffe',
  'jetbrains/phpstorm-stubs' => 'v2019.3@883b6facd78e01c0743b554af86fa590c2573f40',
  'laravel/framework' => 'v8.63.0@8f3d280f36a427730c8c8fa34316c79eed38781e',
  'laravel/horizon' => 'v5.7.13@367c48b57af776a3e195a16989655fa89fa052e3',
  'laravel/sanctum' => 'v2.11.2@b21e65cbe13896946986cb0868180cd69e1bd5d3',
  'laravel/scout' => 'v9.3.0@db9f4b955ee57434b2b38767734f08ea746aaa94',
  'laravel/serializable-closure' => 'v1.0.2@679e24d36ff8b9be0e36f5222244ec8602e18867',
  'laravel/slack-notification-channel' => 'v2.3.1@f428e76b8d0a0a2ff413ab225eeb829b9a8ffc20',
  'laravel/socialite' => 'v5.2.5@fd0f6a3dd963ca480b598649b54f92d81a43617f',
  'laravel/tinker' => 'v2.6.2@c808a7227f97ecfd9219fbf913bad842ea854ddc',
  'laravel/ui' => 'v3.3.0@07d725813350c695c779382cbd6dac0ab8665537',
  'league/color-extractor' => '0.3.2@837086ec60f50c84c611c613963e4ad2e2aec806',
  'league/commonmark' => '1.6.6@c4228d11e30d7493c6836d20872f9582d8ba6dcf',
  'league/flysystem' => '1.1.5@18634df356bfd4119fe3d6156bdb990c414c14ea',
  'league/flysystem-aws-s3-v3' => '1.0.29@4e25cc0582a36a786c31115e419c6e40498f6972',
  'league/flysystem-rackspace' => '1.0.5@ba877e837f5dce60e78a0555de37eb9bfc7dd6b9',
  'league/mime-type-detection' => '1.8.0@b38b25d7b372e9fddb00335400467b223349fd7e',
  'league/oauth1-client' => 'v1.10.0@88dd16b0cff68eb9167bfc849707d2c40ad91ddc',
  'league/omnipay' => 'v3.2.1@38f66a0cc043ed51d6edf7956d6439a2f263501f',
  'maennchen/zipstream-php' => '2.1.0@c4c5803cc1f93df3d2448478ef79394a5981cc58',
  'matchish/laravel-scout-elasticsearch' => '4.0.9@77914fed635a5884c85b8fa3647a5fb26a960a01',
  'meilisearch/meilisearch-php' => 'v0.19.1@ad359d6a7ec391435375d91e22e1e84b01586c2d',
  'mhetreramesh/flysystem-backblaze' => '1.6.1@9bbe64c161519c20e508d2c924d507d892999e5d',
  'mikemccabe/json-patch-php' => '0.1.0@b3af30a6aec7f6467c773cd49b2d974a70f7c0d4',
  'mikey179/vfsstream' => 'v1.6.10@250c0825537d501e327df879fb3d4cd751933b85',
  'mobiledetect/mobiledetectlib' => '2.8.37@9841e3c46f5bd0739b53aed8ac677fa712943df7',
  'moneyphp/money' => 'v3.3.1@122664c2621a95180a13c1ac81fea1d2ef20781e',
  'monolog/monolog' => '2.3.5@fd4380d6fc37626e2f799f29d91195040137eba9',
  'mtdowling/jmespath.php' => '2.6.1@9b87907a81b87bc76d19a7fb2d61e61486ee9edb',
  'myclabs/php-enum' => '1.8.3@b942d263c641ddb5190929ff840c68f78713e937',
  'nesbot/carbon' => '2.53.1@f4655858a784988f880c1b8c7feabbf02dfdf045',
  'nikic/php-parser' => 'v4.13.0@50953a2691a922aa1769461637869a0a2faa3f53',
  'nyholm/psr7' => '1.4.1@2212385b47153ea71b1c1b1374f8cb5e4f7892ec',
  'omnipay/common' => 'v3.1.2@5b16387ec5ab1b9ff86bdf0f20415088693b9948',
  'omnipay/paypal' => 'v3.0.2@519db61b32ff0c1e56cbec94762b970ee9674f65',
  'omnipay/stripe' => 'v3.1.0@37df2a791e8feab45543125f4c5f22d5d305096d',
  'ongr/elasticsearch-dsl' => 'v7.2.1@8c75b819a32a422c393272295fe5e81e8cbd7ec7',
  'opis/closure' => '3.6.2@06e2ebd25f2869e54a306dda991f7db58066f7f6',
  'paragonie/constant_time_encoding' => 'v2.4.0@f34c2b11eb9d2c9318e13540a1dbc2a3afbd939c',
  'paragonie/random_compat' => 'v9.99.100@996434e5492cb4c3edcb9168db6fbb1359ef965a',
  'paragonie/sodium_compat' => 'v1.17.0@c59cac21abbcc0df06a3dd18076450ea4797b321',
  'pda/pheanstalk' => 'v4.0.3@6165573aad525d39b3ac8ae916214cb483a61263',
  'php-http/client-common' => '2.4.0@29e0c60d982f04017069483e832b92074d0a90b2',
  'php-http/discovery' => '1.14.1@de90ab2b41d7d61609f504e031339776bc8c7223',
  'php-http/guzzle7-adapter' => '1.0.0@fb075a71dbfa4847cf0c2938c4e5a9c478ef8b01',
  'php-http/httplug' => '2.2.0@191a0a1b41ed026b717421931f8d3bd2514ffbf9',
  'php-http/message' => '1.12.0@39eb7548be982a81085fe5a6e2a44268cd586291',
  'php-http/message-factory' => 'v1.0.2@a478cb11f66a6ac48d8954216cfed9aa06a501a1',
  'php-http/promise' => '1.1.0@4c4c1f9b7289a2ec57cde7f1e9762a5789506f88',
  'phpdocumentor/reflection-common' => '2.2.0@1d01c49d4ed62f25aa84a747ad35d5a16924662b',
  'phpdocumentor/reflection-docblock' => '5.2.2@069a785b2141f5bcf49f3e353548dc1cce6df556',
  'phpdocumentor/type-resolver' => '1.5.1@a12f7e301eb7258bb68acd89d4aefa05c2906cae',
  'phpoption/phpoption' => '1.8.0@5455cb38aed4523f99977c4a12ef19da4bfe2a28',
  'phpseclib/phpseclib' => '3.0.10@62fcc5a94ac83b1506f52d7558d828617fac9187',
  'predis/predis' => 'v1.1.9@c50c3393bb9f47fa012d0cdfb727a266b0818259',
  'psr/cache' => '1.0.1@d11b50ad223250cf17b86e38383413f5a6764bf8',
  'psr/container' => '1.1.1@8622567409010282b7aeebe4bb841fe98b58dcaf',
  'psr/event-dispatcher' => '1.0.0@dbefd12671e8a14ec7f180cab83036ed26714bb0',
  'psr/http-client' => '1.0.1@2dfb5f6c5eff0e91e20e913f8c5452ed95b86621',
  'psr/http-factory' => '1.0.1@12ac7fcd07e5b077433f5f2bee95b3a771bf61be',
  'psr/http-message' => '1.0.1@f6561bf28d520154e4b0ec72be95418abe6d9363',
  'psr/log' => '1.1.4@d49695b909c3b7628b6289db5479a1c204601f11',
  'psr/simple-cache' => '1.0.1@408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
  'psy/psysh' => 'v0.10.8@e4573f47750dd6c92dca5aee543fa77513cbd8d3',
  'pusher/pusher-php-server' => 'v4.1.5@251f22602320c1b1aff84798fe74f3f7ee0504a9',
  'rackspace/php-opencloud' => 'v1.16.0@d6b71feed7f9e7a4b52e0240a79f06473ba69c8c',
  'ralouphie/getallheaders' => '3.0.3@120b605dfeb996808c31b6477290a714d356e822',
  'ramsey/collection' => '1.2.1@eaca1dc1054ddd10cbd83c1461907bee6fb528fa',
  'ramsey/uuid' => '4.2.3@fc9bb7fb5388691fd7373cd44dcb4d63bbcf24df',
  'react/promise' => 'v2.8.0@f3cff96a19736714524ca0dd1d4130de73dbbbc4',
  'roave/better-reflection' => '4.3.0@aa017e698b47feed410721f3d20e2bacfcba59d5',
  'roave/signature' => '1.2.0@577ee14d2abaeb315a5cd2d86d7e3df46a7dac56',
  'sentry/sdk' => '3.1.0@f03133b067fdf03fed09ff03daf3f1d68f5f3673',
  'sentry/sentry' => '3.3.3@9a7b6d84ac8fa370397336028e760c71accac1dc',
  'sentry/sentry-laravel' => '2.9.0@3acb930f1abeb67046097c3912c7b4b9b4303f08',
  'spatie/color' => 'v1.3.0@fafbf8900541230e10abb2b165990e4758c28024',
  'spatie/dropbox-api' => '1.19.1@0ea6d08445b339241d21b833db111d371e61ed4f',
  'spatie/flysystem-dropbox' => '1.2.3@8b6b072f217343b875316ca6a4203dd59f04207a',
  'spatie/laravel-analytics' => '3.11.0@6ce4610eea86e59446866504f71dcb17ddc8c496',
  'swiftmailer/swiftmailer' => 'v6.2.7@15f7faf8508e04471f666633addacf54c0ab5933',
  'symfony/cache' => 'v5.3.8@945bcebfef0aeef105de61843dd14105633ae38f',
  'symfony/cache-contracts' => 'v2.4.0@c0446463729b89dd4fa62e9aeecc80287323615d',
  'symfony/console' => 'v5.3.7@8b1008344647462ae6ec57559da166c2bfa5e16a',
  'symfony/css-selector' => 'v5.3.4@7fb120adc7f600a59027775b224c13a33530dd90',
  'symfony/deprecation-contracts' => 'v2.4.0@5f38c8804a9e97d23e0c8d63341088cd8a22d627',
  'symfony/dom-crawler' => 'v5.3.7@c7eef3a60ccfdd8eafe07f81652e769ac9c7146c',
  'symfony/error-handler' => 'v5.3.7@3bc60d0fba00ae8d1eaa9eb5ab11a2bbdd1fc321',
  'symfony/event-dispatcher' => 'v5.3.7@ce7b20d69c66a20939d8952b617506a44d102130',
  'symfony/event-dispatcher-contracts' => 'v2.4.0@69fee1ad2332a7cbab3aca13591953da9cdb7a11',
  'symfony/finder' => 'v5.3.7@a10000ada1e600d109a6c7632e9ac42e8bf2fb93',
  'symfony/http-client' => 'v5.3.8@c6370fe2c0a445aedc08f592a6a3149da1fea4c7',
  'symfony/http-client-contracts' => 'v2.4.0@7e82f6084d7cae521a75ef2cb5c9457bbda785f4',
  'symfony/http-foundation' => 'v5.3.7@e36c8e5502b4f3f0190c675f1c1f1248a64f04e5',
  'symfony/http-kernel' => 'v5.3.9@ceaf46a992f60e90645e7279825a830f733a17c5',
  'symfony/mime' => 'v5.3.8@a756033d0a7e53db389618653ae991eba5a19a11',
  'symfony/options-resolver' => 'v5.3.7@4b78e55b179003a42523a362cc0e8327f7a69b5e',
  'symfony/polyfill-ctype' => 'v1.23.0@46cd95797e9df938fdd2b03693b5fca5e64b01ce',
  'symfony/polyfill-iconv' => 'v1.23.0@63b5bb7db83e5673936d6e3b8b3e022ff6474933',
  'symfony/polyfill-intl-grapheme' => 'v1.23.1@16880ba9c5ebe3642d1995ab866db29270b36535',
  'symfony/polyfill-intl-idn' => 'v1.23.0@65bd267525e82759e7d8c4e8ceea44f398838e65',
  'symfony/polyfill-intl-normalizer' => 'v1.23.0@8590a5f561694770bdcd3f9b5c69dde6945028e8',
  'symfony/polyfill-mbstring' => 'v1.23.1@9174a3d80210dca8daa7f31fec659150bbeabfc6',
  'symfony/polyfill-php72' => 'v1.23.0@9a142215a36a3888e30d0a9eeea9766764e96976',
  'symfony/polyfill-php73' => 'v1.23.0@fba8933c384d6476ab14fb7b8526e5287ca7e010',
  'symfony/polyfill-php80' => 'v1.23.1@1100343ed1a92e3a38f9ae122fc0eb21602547be',
  'symfony/polyfill-php81' => 'v1.23.0@e66119f3de95efc359483f810c4c3e6436279436',
  'symfony/polyfill-uuid' => 'v1.23.0@9165effa2eb8a31bb3fa608df9d529920d21ddd9',
  'symfony/process' => 'v5.3.7@38f26c7d6ed535217ea393e05634cb0b244a1967',
  'symfony/psr-http-message-bridge' => 'v2.1.1@c9012994c4b4fb23e7c57dd86b763a417a04feba',
  'symfony/routing' => 'v5.3.7@be865017746fe869007d94220ad3f5297951811b',
  'symfony/serializer' => 'v5.3.8@a877799b1e94f792208bea68295f6675027c92be',
  'symfony/service-contracts' => 'v2.4.0@f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb',
  'symfony/string' => 'v5.3.7@8d224396e28d30f81969f083a58763b8b9ceb0a5',
  'symfony/translation' => 'v5.3.9@6e69f3551c1a3356cf6ea8d019bf039a0f8b6886',
  'symfony/translation-contracts' => 'v2.4.0@95c812666f3e91db75385749fe219c5e494c7f95',
  'symfony/var-dumper' => 'v5.3.8@eaaea4098be1c90c8285543e1356a09c8aa5c8da',
  'symfony/var-exporter' => 'v5.3.8@a7604de14bcf472fe8e33f758e9e5b7bf07d3b91',
  'teamtnt/laravel-scout-tntsearch-driver' => 'v11.5.0@ea962275ee5b977af81dccc138a0fa87d062492b',
  'teamtnt/tntsearch' => 'v2.7.0@c7d0f67070ea22e835bb1416b85dee0f74780fdc',
  'tijsverkoyen/css-to-inline-styles' => '2.2.3@b43b05cf43c1b6d849478965062b6ef73e223bb5',
  'torann/geoip' => '3.0.2@f16d5df66ecb6ba4ffaef52abef519fbc19596d3',
  'vlucas/phpdotenv' => 'v5.3.1@accaddf133651d4b5cf81a119f25296736ffc850',
  'voku/portable-ascii' => '1.5.6@80953678b19901e5165c56752d087fc11526017c',
  'webmozart/assert' => '1.10.0@6964c76c7804814a842473e0c8fd15bab0f18e25',
  'willdurand/email-reply-parser' => '2.9.0@642bec19af70c2bf2f2611301349107fe2e6dd08',
  'willdurand/negotiation' => '3.0.0@04e14f38d4edfcc974114a07d2777d90c98f3d9c',
  'zbateson/mail-mime-parser' => '1.3.2@8eb590750772849189c7fa30ed52bcdcd2c9d1ef',
  'zbateson/mb-wrapper' => '1.0.1@721b3dfbf7ab75fee5ac60a542d7923ffe59ef6d',
  'zbateson/stream-decorators' => '1.0.6@3403c4323bd1cd15fe54348b031b26b064c706af',
  'laravel/laravel' => 'dev-master@916b23d46508afd700e434b85b59064df8f738a1',
);

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!self::composer2ApiUsable()) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName): string
    {
        if (self::composer2ApiUsable()) {
            return InstalledVersions::getPrettyVersion($packageName)
                . '@' . InstalledVersions::getReference($packageName);
        }

        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }

    private static function composer2ApiUsable(): bool
    {
        if (!class_exists(InstalledVersions::class, false)) {
            return false;
        }

        if (method_exists(InstalledVersions::class, 'getAllRawData')) {
            $rawData = InstalledVersions::getAllRawData();
            if (count($rawData) === 1 && count($rawData[0]) === 0) {
                return false;
            }
        } else {
            $rawData = InstalledVersions::getRawData();
            if ($rawData === null || $rawData === []) {
                return false;
            }
        }

        return true;
    }
}