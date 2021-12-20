<?php namespace App\Http\Controllers;

use App;
use App\Actions\Track\DeleteTracks;
use App\Artist;
use App\Http\Requests\ModifyArtists;
use App\Jobs\IncrementModelViews;
use App\Services\Albums\DeleteAlbums;
use App\Services\Artists\CrupdateArtist;
use App\Services\Artists\LoadArtist;
use App\Services\Artists\PaginateArtists;
use App\UserProfile;
use Common\Core\BaseController;
use Common\Files\Actions\Deletion\DeleteEntries;
use DB;
use Illuminate\Http\Request;

class ArtistController extends BaseController {

    /**
     * @var Request
     */
    private $request;

	public function __construct(Request $request)
	{
        $this->request = $request;
    }

	public function index()
	{
        $this->authorize('index', Artist::class);

        $pagination = app(PaginateArtists::class)->execute($this->request->all());

        $pagination->makeVisible(['updated_at', 'views', 'plays', 'verified']);

	    return $this->success(['pagination' => $pagination]);
	}

    public function show(Artist $artist)
    {
        $this->authorize('show', $artist);
        $artistExtra = $this->getBitcloutData($artist->bitclout);

        $artist->coin_price = $artistExtra["coin_price"];
        $artist->coin_circulation = $artistExtra["coin_circulation"];
        $artist->total_usd_locked = $artistExtra["total_usd_locked"];
        $artist->usd_market_cap = $artistExtra["usd_market_cap"];
        $artist->usd_per_coin = $artistExtra["usd_per_coin"];
        $artist->coin_basic_point = $artistExtra["coin_basic_point"];

        $response = app(LoadArtist::class)->execute($artist, $this->request->all(), $this->request->has('autoUpdate'));

        dispatch(new IncrementModelViews($artist->id, 'artist'));

        return $this->success($response);
    }

    public function store(ModifyArtists $validate)
    {
        $this->authorize('store', Artist::class);

        $artist = app(CrupdateArtist::class)->execute($this->request->all());

        return $this->success(['artist' => $artist]);
    }

	public function update(Artist $artist, ModifyArtists $validate)
	{
		$this->authorize('update', $artist);

        $artist = app(CrupdateArtist::class)->execute($this->request->all(), $artist);

        return $this->success(['artist' => $artist]);
	}

	public function destroy()
	{
        $artistIds = $this->request->get('ids');
		$this->authorize('destroy', [Artist::class, $artistIds]);

	    $this->validate($this->request, [
		    'ids'   => 'required|array',
		    'ids.*' => 'required|integer'
        ]);

        $artists = Artist::whereIn('id', $artistIds)->get();
        $imagePaths = $artists->pluck('image_small')
            ->concat($artists->pluck('image_large'))
            ->filter();
        app(DeleteEntries::class)->execute([
            'paths' => $imagePaths->toArray()
        ]);
        Artist::destroy($artists->pluck('id'));
        app(DeleteAlbums::class)->execute(
            DB::table('artist_album')->whereIn('artist_id', $artistIds)->where('primary', true)->pluck('album_id')
        );
        app(DeleteTracks::class)->execute(
            DB::table('artist_track')->whereIn('artist_id', $artistIds)->where('primary', true)->pluck('track_id')->toArray()
        );
        DB::table('user_artist')->whereIn('artist_id', $artistIds)->delete();
        DB::table('likes')->where('likeable_type', Artist::class)->whereIn('likeable_id', $artistIds)->delete();
        UserProfile::whereIn('artist_id', $artistIds)->delete();

		return $this->success();
	}
    protected function getBitcloutData($username){
        $output = ["coin_price" => "","coin_circulation" => "","total_usd_locked" => "","usd_market_cap" => "","usd_per_coin" => "","coin_basic_point" => ""];
        $url = 'https://bitclout.com/api/v0/get-single-profile';
        $url1 = 'https://bitclout.com/api/v0/get-exchange-rate';
        $arg = ["PublicKeyBase58Check" => "","Username" => trim($username)];
        $response = $this->postCurlResponseData($url,$arg);
        $response1 = $this->getCurlResponseData($url1);
        if(isset($response->Profile)){
            $coinPrice = number_format(($response->Profile->CoinPriceBitCloutNanos/1000000000),2);
            $coinCirculation = number_format(($response->Profile->CoinEntry->CoinsInCirculationNanos/1000000000),4);
            $totalLocked = number_format(($response->Profile->CoinEntry->BitCloutLockedNanos/1000000000),2);
            $marketCap = number_format(($coinPrice*$coinCirculation),2);
            $basicPoint = number_format(($response->Profile->CoinEntry->CreatorBasisPoints/100),2);
            $perCoin = $response1->USDCentsPerBitCloutExchangeRate ? "$".number_format(($response1->USDCentsPerBitCloutExchangeRate/100),2) : null;
            $output = [
                "coin_price" => "$".$coinPrice,
                "coin_circulation" => $coinCirculation,
                "total_usd_locked" => "$".$totalLocked,
                "usd_market_cap" => "$".$marketCap,
                "usd_per_coin" => $perCoin,
                "coin_basic_point" => $basicPoint."%"
            ];
        }
        return $output;
    }
    protected function postCurlResponseData($url,$data){
        $curl = curl_init();
        curl_setopt_array($curl,array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    protected function getCurlResponseData($url){
        $curl = curl_init();
        curl_setopt_array($curl,array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}
