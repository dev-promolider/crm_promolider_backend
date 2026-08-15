<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Me;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\GetMyCreatedInfoproductsUseCase;
use Promolider\Application\Infoproducts\UseCases\GetMyPurchasedInfoproductsUseCase;

class GetMyProductsController extends Controller
{
    public function __construct(
        private GetMyCreatedInfoproductsUseCase $getCreatedUseCase,
        private GetMyPurchasedInfoproductsUseCase $getPurchasedUseCase
    ) {}

    public function __invoke(Request $request)
    {
        $userId = $request->user()->id;

        // Paginación
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);

        // Filtros
        $search = $request->query('search');
        $productTypeId = $request->query('product_type_id');
        $status = $request->has('status') && $request->query('status') !== '' && $request->query('status') !== null ? (int)$request->query('status') : null;

        if ($request->query('origin') === 'purchased') {
            $products = $this->getPurchasedUseCase->execute($userId);
            return response()->json($products);
        }

        $products = $this->getCreatedUseCase->execute(
            $userId,
            $page,
            $perPage,
            $search,
            $productTypeId,
            $status
        );

        return response()->json($products);
    }
}
