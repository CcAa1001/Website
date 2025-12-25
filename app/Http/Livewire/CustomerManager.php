<?php
namespace App\Http\Livewire;
use Livewire\Component;
use App\Models\Customer;

class CustomerManager extends Component {
    public $name, $email, $phone;

    public function addCustomer() {
        $this->validate(['name' => 'required', 'phone' => 'nullable|numeric']);
        Customer::create([
            'name' => $this->name, 'email' => $this->email, 
            'phone' => $this->phone, 'user_id' => auth()->id()
        ]);
        $this->reset();
    }

    public function render() {
        return view('livewire.customer-manager', [
            'customers' => Customer::where('user_id', auth()->id())->get()
        ]);
    }
}
