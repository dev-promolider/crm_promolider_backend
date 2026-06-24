<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdvertisementResource;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdvertisementsController extends Controller
{
    public function __construct(){
        $this->middleware('can:advertisements')->only('index');
        $this->middleware('can:action-add-advertisements')->only('Add');
        $this->middleware('can:action-edit-advertisements')->only('Edit');
        $this->middleware('can:action-delete-advertisements')->only('Delete');
        $this->middleware('can:action-list-advertisements')->only('List');
        $this->middleware('can:action-detail-advertisements')->only('Detail');
    }
    public function index()
    {
        return view('content.config.advertisement');
    }

    public function Add(Request $request)
    {
        $advertisement = new Advertisement();
        $advertisement->message = $request->message;
        $advertisement->status = '0';

        $result = new AdvertisementResource($advertisement);

        if ($advertisement->save()) :
            return ($result)->response()->setStatusCode(200);
        endif;

        return $result->response()->setStatusCode(400);
    }

    public function Edit(Request $request, $id)
    {
        $advertisement = Advertisement::findOrFail($id);
        $result = new AdvertisementResource($advertisement);

        if ($advertisement == null) {
            return ($result)->response()->setStatusCode(404);
        }

        $advertisement->message = $request->message ?? $advertisement->message;

        if ($advertisement->save()) {
            return ($result)->response()->setStatusCode(200);
        };

        return $result->response()->setStatusCode(400);
    }

    public function Delete(Request $request, $id)
    {
        $advertisement = Advertisement::findOrFail($id);
        $result = new AdvertisementResource($advertisement);

        if ($advertisement == null) {
            return ($result)->response()->setStatusCode(404);
        }

        $advertisement->status = $request->status ?? $advertisement->status;

        if ($advertisement->save()) {
            return ($result)->response()->setStatusCode(200);
        }

        return $result->response()->setStatusCode(400);
    }

    public function List(Request $request): AnonymousResourceCollection
    {
        $advertisements = Advertisement::paginate(10);
        return AdvertisementResource::collection($advertisements);
    }

    public function Detail($id)
    {
        $advertisement = Advertisement::findOrFail($id);
        $result = new AdvertisementResource($advertisement);

        if ($advertisement == null) {
            return ($result)->response()->setStatusCode(404);
        }
        return ($result)->response()->setStatusCode(200);
    }
}
