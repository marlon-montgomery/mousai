<?php namespace App\Services\Search;

use Illuminate\Support\Collection;

interface SearchInterface {
    public function search(string $q, int $limit, array $modelTypes): array;
    public function artists(): Collection;
    public function albums(): Collection;
    public function tracks(): Collection;
    public function playlists(): Collection;
    public function users(): Collection;
    public function channels(): Collection;
    public function genres(): Collection;
    public function tags(): Collection;
}
