<?php

namespace App\Livewire\Article;

use App\Models\Article;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use App\Models\MacroCategory;

class FilterOrder extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $uri;
    public $price;
    public $maxPrice;
    public $maxPriceCategory;
    public $category;
    public $categories;
    public $macroCategory;
    public $macroCategoryId;
    public $maxPriceMacroCategory;

    public function mount(){
        
        $this->maxPrice = Article::where('is_accepted', true)->max('price');
        $this->price = $this->maxPrice;
        if (strpos($this->uri, 'category/') === 0) {
            $categoryId = substr($this->uri, strlen('category/'));
            $this->category = Category::findOrFail($categoryId);
            $this->maxPriceCategory = $this->category->articles()->max('price');
        } else if (strpos($this->uri, 'MacroCategory/') === 0){
            $this->macroCategoryId = substr($this->uri, strlen('MacroCategory/'));
            $this->macroCategory = MacroCategory::findOrFail($this->macroCategoryId);
            $categories = Category::where('macroCategory_id', $this->macroCategoryId)->get();
            $this->maxPriceCategory = Article::whereIn('category_id', $categories->pluck('id'))->max('price');
        }
    }

    public function render()
    {
        $query = Article::where('is_accepted', true);

        if ($this->price) {
            $query->where('price', '<=', $this->price);
            $this->dispatch('filterPrice', $this->price, $this->maxPrice);
        }

        if ($this->category) {
            $articles = $this->category->articles()->where('price', '<=', $this->price)->paginate(10);
        } else if ($this->macroCategory){
            $this->categories = Category::where('macroCategory_id', $this->macroCategoryId)->get();
            $articles = Article::whereIn('category_id', $this->categories->pluck('id'))->where('price', '<=', $this->price)->paginate(10);
        } else {
            $articles = $query->paginate(10);
        }

        return view('livewire.article.filter-order', compact('articles'));
    }    

    public function updatedPrice($value){
        $this->dispatch('priceUpdated', $value);
    }
}
