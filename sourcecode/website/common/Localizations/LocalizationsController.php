<?php namespace Common\Localizations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class LocalizationsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var LocalizationsRepository
     */
    private $repository;

    public function __construct(Request $request, LocalizationsRepository $repository)
    {
        $this->request = $request;
        $this->repository = $repository;
    }

    /**
     * Return all user created localizations.
     *
     * @return JsonResponse|Response
     */
    public function index()
    {
        $this->authorize('index', Localization::class);

        return $this->success(['localizations' =>  $this->repository->all()]);
    }

    /**
     * Get localization by specified name.
     *
     * @param string $name
     * @return JsonResponse|Response
     */
    public function show($name)
    {
        $this->authorize('show', Localization::class);

        return $this->success(['localization' => $this->repository->getByNameOrCode($name)]);
    }

    /**
     * @param integer $id
     * @return JsonResponse|Response
     */
    public function update($id)
    {
        $this->authorize('update', Localization::class);

        $this->validate($this->request, [
            'name'  => 'string|min:1',
            'language' => 'string|min:2|max:5',
            'lines' => 'array|min:1'
        ]);

        $localization = $this->repository->update($id, $this->request->all(), true);
        return $this->success(['localization' => $localization]);
    }

    /**
     * @return JsonResponse|Response
     */
    public function store()
    {
        $this->authorize('store', Localization::class);

        $this->validate($this->request, [
            'name' => 'required|unique:localizations',
            'language' => 'string|min:2|max:5',
        ]);

        $localization = $this->repository->create($this->request->all());
        return $this->success(['localization' => $localization]);
    }

    /**
     * Delete specified language.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('destroy', Localization::class);

        $this->repository->delete($id);

        return $this->success();
    }
}
