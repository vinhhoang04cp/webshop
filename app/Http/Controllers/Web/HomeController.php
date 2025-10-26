<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    public function index()
    {
        $categories = $this->homeService->getCategories();
        $featuredProducts = $this->homeService->getFeaturedProducts(8);
        $newProducts = $this->homeService->getNewProducts(8);
        $cartCount = $this->homeService->getCartCount(Auth::user());

        return view('home', compact('categories', 'featuredProducts', 'newProducts', 'cartCount'));
    }
}
