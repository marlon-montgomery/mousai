<?php namespace App\Http\Controllers;

use App;
use App\Channel;
use App\Genre;
use App\Services\Genres\PaginateGenres;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GenreController extends BaseController
{
    /**
     * @var Genre
     */
    private $genre;

    /**
     * @var Request
     */
    private $request;

    public function __construct(
        Genre $genre,
        Request $request
    )
    {
        $this->genre = $genre;
        $this->request = $request;
    }

    public function index()
    {
        $this->authorize('index', Genre::class);

        $pagination = app(PaginateGenres::class)
            ->execute($this->request->all());

        return $this->success(['pagination' => $pagination]);
    }

    public function show(string $name)
    {
        $this->authorize('index', Genre::class);

        $genre = $this->genre->where('name', str_replace('-', ' ', $name))
            ->orWhere('name', $name)
            ->firstOrFail();

        $params = $this->request->all();
        $params['genre'] = $genre;

        $channel = Channel::where('slug', 'genre')->first()->loadContent($params);

        return $this->success(['channel' => $channel, 'genre' => $genre]);
    }

    public function store()
    {
        $this->authorize('store', Genre::class);

        $this->validate($this->request, [
            'name' => 'required|unique:genres',
            'image' => 'string',
            'popularity' => 'nullable|integer|min:1|max:100'
        ]);

        $newGenre = $this->genre->create([
            'name' => slugify($this->request->get('name')),
            'display_name' => $this->request->get('display_name') ?: $this->request->get('name'),
            'image' => $this->request->get('image'),
            'popularity' => $this->request->get('popularity'),
        ]);

        return $this->success(['genre' => $newGenre]);
    }

    public function update(int $id)
    {
        $this->authorize('update', Genre::class);

        $this->validate($this->request, [
            'name' => Rule::unique('genres')->ignore($id),
            'image' => 'string',
            'popularity' => 'nullable|integer|min:1|max:100'
        ]);

        $data = $this->request->all();
        if ($data['name']) {
            $data['name'] = slugify($data['name']);
        }

        $genre = $this->genre
            ->find($id)
            ->update($data);

        return $this->success(['genre' => $genre]);
    }

    public function destroy()
    {
        $this->authorize('destroy', Genre::class);

        $this->validate($this->request, [
            'ids' => 'required|array|exists:genres,id'
        ]);

        $count = $this->genre->destroy($this->request->get('ids'));

        return $this->success(['count' => $count]);
    }
}
