<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    /**
     * Handles POST request for search, convert form input to query string and redirects to searchShow
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function search(Request $request) {
        $searchQuery = $request->search == null ? '' : $request->search;
        $orderMethods = [
            'price_asc',
            'price_desc',
            'newest'
        ];
        if (!in_array($request->order_by, $orderMethods)) {
            $orderBy = 'newest';
        } else {
            $orderBy = $request->order_by;
        }
        return redirect()->route('search.show', [
            'query' => $searchQuery,
            'category' => $request->category,
            'type' => $request->product_type,
            'price_min' => $request->minimum_price,
            'price_max' => $request->maximum_price,
            'user' => $request->user,
            'order_by' => $orderBy,
        ]);
    }

    /**
     * Applying all search parameters from query string and returns a view
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function searchShow(Request $request) {
        $searchQuery = $request->get('query');

        $start = microtime(true);

        // The database/collection Scout engines cannot filter on the virtual
        // fields (category, user, type) that only exist in the search index,
        // so use a plain Eloquent query for them.
        if (in_array(config('scout.driver'), ['database', 'collection', null], true)) {
            return $this->searchWithEloquent($request, $searchQuery, $start);
        }

        $query = Product::search($searchQuery);

        $query->limit = 1000;
        /**
         * Limit search by parameters
         */
        //user
        $userQuery = $request->get('user');
        if ($userQuery!== null){
            $query->where('user',$userQuery);

        }
        //category
        $categoryQuery = $request->get('category');
        if ($categoryQuery !== null && $categoryQuery !== 'any') {
            $query->where('category', $categoryQuery);
        }
        //type
        $typeQuery = $request->get('type');
        $supportedTypes = ['digital', 'physical'];
        if ($typeQuery !== null && in_array($typeQuery, $supportedTypes)) {
            $query->where('type', $typeQuery);
        }
        // perform search
        $perPage = config('marketplace.products_per_page');
        $results = $query->get();

        //ordering
        $orderQuery = $request->get('order_by');
        $results = $this->order($results,$orderQuery);

        //price filter
        $minPriceQuery = $request->get('price_min');
        $maxPriceQuery = $request->get('price_max');
        $results = $this->priceFilter($results,$minPriceQuery,$maxPriceQuery);

        $finalResult = $this -> paginate($results,$perPage);
        $finalResult->setPath($request->fullUrl());


        $end = (microtime(true) - $start);

        $end = round($end, 5);

        return view('results', [
            'productsView' => session() -> get('products_view'),
            'products' => $finalResult,
            'categories' => Category::roots(),
            'query' => $searchQuery,
            'time' => $end,
            'results_count' => $results->count()
        ]);
    }

    /**
     * Search without an external search index.
     */
    private function searchWithEloquent(Request $request, $searchQuery, $start)
    {
        $query = Product::query()->where('active', 1);

        if (is_string($searchQuery) && trim($searchQuery) !== '') {
            $like = '%' . addcslashes(trim($searchQuery), '%_\\') . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('description', 'like', $like)
                  ->orWhere('rules', 'like', $like);
            });
        }

        $userQuery = $request->get('user');
        if ($userQuery !== null && $userQuery !== '') {
            $query->whereHas('user', function ($q) use ($userQuery) {
                $q->where('username', $userQuery);
            });
        }

        $categoryQuery = $request->get('category');
        if ($categoryQuery !== null && $categoryQuery !== '' && $categoryQuery !== 'any') {
            $category = Category::where('name', $categoryQuery)->first();
            $ids = $category ? array_merge([$category->id], $category->allChildrenIds()) : [];
            $query->whereIn('category_id', $ids);
        }

        $typeQuery = $request->get('type');
        if ($typeQuery === 'digital') {
            $query->whereHas('digital');
        } elseif ($typeQuery === 'physical') {
            $query->whereHas('physical');
        }

        $results = $query->get();
        $results = $this->order($results, $request->get('order_by'));
        $results = $this->priceFilter($results, $request->get('price_min'), $request->get('price_max'));

        $perPage = config('marketplace.products_per_page');
        $finalResult = $this->paginate($results, $perPage);
        $finalResult->setPath($request->fullUrl());

        return view('results', [
            'productsView' => session()->get('products_view'),
            'products' => $finalResult,
            'categories' => Category::roots(),
            'query' => $searchQuery,
            'time' => round(microtime(true) - $start, 5),
            'results_count' => $results->count()
        ]);
    }

    /**
     * Paginates a collection
     *
     * @param $items
     * @param int $perPage
     * @param null $page
     * @param array $options
     * @return LengthAwarePaginator
     */
    private function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
     * Accepts collection of products and orders them based on provided $orderQuery
     *
     * @param $collection
     * @param $orderQuery
     * @return mixed
     */
    private function order($collection,$orderQuery){
        $ordered = $collection;
        if ($orderQuery !== null) {
            if ($orderQuery == 'price_asc') {
                $ordered = $collection->sortBy(function ($product) {
                    return (float)$product->price_from;
                });
            }
            if ($orderQuery == 'price_desc') {

                $ordered = $collection->sortByDesc(function ($product) {
                    return $product->price_from;
                });
            }
            if ($orderQuery == 'newest') {
                $ordered = $collection->sortByDesc(function ($product) {
                    return $product->created_at;
                });
            }
        }
        return $ordered;
    }

    private function priceFilter($collection,$minPriceQuery,$maxPriceQuery){
        //min price
        $filteredCollection = $collection;
        if ($minPriceQuery !== null && floatval($minPriceQuery) > 0){
            $minPrice = floatval($minPriceQuery);
            $filteredCollection = $collection->filter(function ($product) use ($minPrice) {
                return $product->price_from >= floatval($minPrice);
            });
        }
        //max price
        if ($maxPriceQuery !== null && floatval($maxPriceQuery) > 0){
            $maxPrice = floatval($maxPriceQuery);
            $filteredCollection = $filteredCollection->filter(function ($product) use ($maxPrice) {
                return $product->price_from <= floatval($maxPrice);
            });
        }

        return $filteredCollection;
    }

}
