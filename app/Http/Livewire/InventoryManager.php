namespace App\Http\Livewire;
use Livewire\Component;
use App\Models\Product; // Ensure you create this model
use App\Models\Category; // Ensure you create this model

class InventoryManager extends Component {
    public $name, $price, $stock, $category_id;

    public function render() {
        return view('livewire.inventory-manager', [
            'products' => \DB::table('products')->get(),
            'categories' => \DB::table('categories')->get()
        ]);
    }

    public function addProduct() {
        $this->validate(['name' => 'required', 'price' => 'required|numeric', 'stock' => 'required|integer']);
        \DB::table('products')->insert([
            'name' => $this->name, 'price' => $this->price, 
            'stock' => $this->stock, 'category_id' => $this->category_id,
            'created_at' => now(), 'updated_at' => now()
        ]);
        $this->reset();
        session()->flash('status', 'Product added successfully.');
    }
}