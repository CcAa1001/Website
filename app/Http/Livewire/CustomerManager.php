<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerManager extends Component {
    public $name, $phone, $email;

    public function addCustomer() {
        $this->validate(['name' => 'required', 'phone' => 'required']);
        Customer::create(['user_id' => Auth::id(), 'name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]);
        $this->reset();
    }

    public function render() {
        return view('livewire.customer-manager', ['customers' => Customer::where('user_id', Auth::id())->latest()->get()]);
    }
}