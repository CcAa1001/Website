<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class InventoryManager extends Component {
    public $name, $price, $stock, $category_id, $cat_name;

    public function deleteProduct($id) {
        Product::where('id', $id)->where('user_id', Auth::id())->delete();
        session()->flash('status', 'Product deleted.');
    }

    public function addProduct() {
        $this->validate(['name' => 'required', 'price' => 'required|numeric', 'stock' => 'required|integer', 'category_id' => 'required']);
        Product::create([
            'name' => $this->name, 'price' => $this->price, 'stock' => $this->stock,
            'category_id' => $this->category_id, 'user_id' => Auth::id()
        ]);
        $this->reset(['name', 'price', 'stock']);
    }

    public function render() {
        return view('livewire.inventory-manager', [
            'products' => Product::where('user_id', Auth::id())->with('category')->get(),
            'categories' => Category::where('user_id', Auth::id())->get()
        ]);
    }
}