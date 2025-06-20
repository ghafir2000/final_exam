<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ServiceService
{
    protected $imageService;
    public function __construct(ImageService $imageService) {
        $this->imageService = $imageService;
    }

    
    public function all($data = [], $paginated = true, $withes = [])
    {
        $query = Service::query()->with('servicable') // Eager load the polymorphic relation
            ->with($withes); // Eager load other specified relations

        // Filter by servicable_type (e.g., "App\Models\Partner")
        // This filters the 'servicable_type' column on the 'services' table.
        $query->when(isset($data['servicable_type']), function ($q) use ($data) {
            $q->where('servicable_type', $data['servicable_type']);
        });

        // Filter by servicable_id (e.g., partner_id = 28)
        // This filters based on the 'id' of the related polymorphic model (e.g., Partner).
        $query->when(isset($data['servicable_id']), function ($q) use ($data) {
            // This assumes 'servicable' is the morphTo relation name on the Service model.
            $q->whereHas('servicable', function ($q_servicable) use ($data) {
                $q_servicable->where('id', $data['servicable_id']);
            });
        });

        // Filter by animal_id (Service -> Breed -> Animal)
        $query->when(isset($data['animal_id']), function ($q) use ($data) {
            // We are checking if the service's breeds has an animal with the given animal_id
            $q->whereHas('breeds.animal', function ($q_animal) use ($data) {
                $q_animal->where('id', $data['animal_id']);
            });
        });

        // Filter by breed_id (Service -> Breed)
        // This can be combined with animal_id filter if both are present.
        // If animal_id is set, this breed_id filter acts as an additional constraint.
        $query->when(isset($data['breed_id']), function ($q) use ($data) {
            $q->whereHas('breeds', function ($q_breed) use ($data) {
                $q_breed->where('breed_id', $data['breed_id']);
            });
        });

        // Filter by search term (on service name)
        $query->when(isset($data['search']), function ($q) use ($data) {
            $q->where('name', 'like', "%{$data['search']}%");
        });

        // Filter by price range
        $query->when(!empty($data['price_range']) && is_array($data['price_range']), function ($q) use ($data) {
            if (isset($data['price_range']['min']) && is_numeric($data['price_range']['min'])) {
                $q->where('price', '>=',(int)  $data['price_range']['min']);
            }
            if (isset($data['price_range']['max']) && is_numeric($data['price_range']['max'])) {
                $q->where('price', '<=', (int) $data['price_range']['max']);
            }
        });

        // Order by Price or default to latest
        if (isset($data['price'])) {
            $query->orderByRaw('CAST(price AS UNSIGNED) ' . $data['price']);
        } else {
            $query->latest(); // Default order (created_at desc) if no price sort specified
        }

        if ($paginated) {
            $paginator = $query->paginate(10); // Or your preferred number, e.g., config('pagination.default', 15)
            $paginator->appends($data);

            return $paginator;
        }

        return $query->get();
    }


    public function find($id, $withTrashed = false, $withes = [])
    {

        return Service::with($withes)
            ->withTrashed($withTrashed)
            ->find($id);
    }

    public function store($data)
    {
        $service = Service::create(Arr::except($data, 'service_picture'));
        if (isset($data['service_picture'])) {
            $this->imageService->store($service, $data['service_picture'], 'service_picture');
        }
        if (isset($data['breeds'])) {
            $breedIds = array_map('intval', $data['breeds']);
            $service->breeds()->sync($breedIds);
        }
        return $service;
    }

    public function update($id, $data)
    {
        $service = Service::find($id);

        if (!$service) {
            throw new \Exception("Service not found");
        }

        if ($service->servicable_id != auth()->user()->userable_id ||
            $service->servicable_type != auth()->user()->userable_type) {
            throw new \Exception("You don't have permissions to update this service");
        }
        $service->update($data);
        return $service;
    }

    public function destroy($id)
    {
        $service = Service::withTrashed()->find($id);
        if (!$service) {
            throw new \Exception("Service not found");
        }
        if ($service->servicable_id != auth()->user()->userable_id ||
            $service->servicable_type != auth()->user()->userable_type) {
            throw new \Exception("You don't have permissions to delete this service");
        }
        $service->delete();
        return $service;
    }

    public function restore($id)
    {
        $service = Service::withTrashed()->find($id);

        if (!$service) {
            throw new \Exception("Service not found");
        }

        $service->restore();
    }
}

