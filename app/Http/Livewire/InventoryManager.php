<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class InventoryManager extends Component {
    public $name, $price, $stock, $category_id;

    public function addProduct() {
        $this->validate(['name' => 'required', 'price' => 'required|numeric', 'stock' => 'required|integer', 'category_id' => 'required']);
        Product::create([
            'name' => $this->name, 'price' => $this->price, 'stock' => $this->stock,
            'category_id' => $this->category_id, 'user_id' => Auth::id()
        ]);
        $this->reset();
        session()->flash('status', 'Produk berhasil ditambahkan ke katalog!');
    }

    public function render() {
        return view('livewire.inventory-manager', [
            'products' => Product::where('user_id', Auth::id())->get(),
            'categories' => Category::where('user_id', Auth::id())->get()
        ]);
    }
    public function addCategory() {
        $this->validate(['cat_name' => 'required|min:3']);
        Category::create(['name' => $this->cat_name, 'user_id' => Auth::id()]);
        $this->cat_name = '';
        session()->flash('status', 'Category created!');
    }

    public function incrementStock($id) {
        Product::where('id', $id)->where('user_id', auth()->id())->increment('stock', 10);
        session()->flash('status', 'Stok berhasil ditambah!');
    }

    public function deleteProduct($id) {
        Product::where('id', $id)->where('user_id', Auth::id())->delete();
    }

}