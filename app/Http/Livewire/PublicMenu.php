<?php
namespace App\Http\Livewire;
use Livewire\Component;
use App\Models\Product;
use App\Models\User;

class PublicMenu extends Component {
    public $vendorId;
    public function mount($userId) { $this->vendorId = $userId; }
    public function render() {
        return view('livewire.public-menu', [
            'products' => Product::where('user_id', $this->vendorId)->get(),
            'vendor' => User::find($this->vendorId)
        ])->layout('layouts.base');
    }
}