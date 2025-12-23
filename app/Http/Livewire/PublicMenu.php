<?php
namespace App\Http\Livewire;
use Livewire\Component;
use App\Models\Product;
use App\Models\User;

class PublicMenu extends Component {
    public $vendor;

    public function mount($userId) {
        $this->vendor = User::findOrFail($userId);
    }

    public function render() {
        return view('livewire.public-menu', [
            'products' => Product::where('user_id', $this->vendor->id)->get()
        ])->layout('layouts.base');
    }
}