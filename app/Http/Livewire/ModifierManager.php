<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ModifierGroup;
use App\Models\Modifier;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ModifierManager extends Component
{
    // Modifier Group Properties
    public $groupId;
    public $groupName;
    public $product_id;
    public $is_required = false;
    public $max_selections;
    public $group_sort_order = 0;
    public $isEditingGroup = false;

    // Modifier Properties
    public $modifierId;
    public $modifierName;
    public $modifierPrice = 0;
    public $modifier_sort_order = 0;
    public $isEditingModifier = false;

    // UI State
    public $selectedGroupId;
    public $showModifierForm = false;
    public $searchProduct = '';

    protected function groupRules()
    {
        return [
            'groupName' => 'required|min:2|max:255',
            'product_id' => 'nullable|exists:products,id',
            'is_required' => 'boolean',
            'max_selections' => 'nullable|integer|min:1',
            'group_sort_order' => 'integer|min:0',
        ];
    }

    protected function modifierRules()
    {
        return [
            'modifierName' => 'required|min:2|max:255',
            'modifierPrice' => 'required|numeric|min:0',
            'modifier_sort_order' => 'integer|min:0',
        ];
    }

    public function render()
    {
        $user = auth()->user();

        // Get all modifier groups for this tenant
        $query = ModifierGroup::query()
            ->with(['product', 'modifiers' => function($q) {
                $q->orderBy('sort_order')->orderBy('name');
            }]);

        // If groups belong directly to products, filter by tenant through products
        if ($this->searchProduct) {
            $query->whereHas('product', function($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id)
                  ->where('name', 'like', '%' . $this->searchProduct . '%');
            });
        } else {
            $query->whereHas('product', function($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            });
        }

        $modifierGroups = $query->orderBy('sort_order')->get();

        // Get selected group details
        $selectedGroup = null;
        if ($this->selectedGroupId) {
            $selectedGroup = ModifierGroup::with('modifiers')->find($this->selectedGroupId);
        }

        // Get products for dropdown
        $products = Product::where('tenant_id', $user->tenant_id)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('livewire.modifier-manager', [
            'modifierGroups' => $modifierGroups,
            'selectedGroup' => $selectedGroup,
            'products' => $products,
        ]);
    }

    // ==================== MODIFIER GROUP METHODS ====================

    public function saveGroup()
    {
        $this->validate($this->groupRules());

        $data = [
            'name' => $this->groupName,
            'product_id' => $this->product_id,
            'is_required' => $this->is_required,
            'max_selections' => $this->max_selections,
            'sort_order' => $this->group_sort_order,
        ];

        if ($this->groupId) {
            $group = ModifierGroup::findOrFail($this->groupId);
            
            // Verify ownership through product
            if ($group->product && $group->product->tenant_id !== auth()->user()->tenant_id) {
                session()->flash('error', 'Unauthorized action!');
                return;
            }
            
            $group->update($data);
            session()->flash('message', 'Modifier group berhasil diupdate!');
        } else {
            // Verify product ownership
            if ($this->product_id) {
                $product = Product::where('id', $this->product_id)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->first();
                
                if (!$product) {
                    session()->flash('error', 'Produk tidak ditemukan!');
                    return;
                }
            }
            
            $group = ModifierGroup::create($data);
            $this->selectedGroupId = $group->id;
            session()->flash('message', 'Modifier group berhasil ditambahkan!');
        }

        $this->resetGroupForm();
    }

    public function editGroup($id)
    {
        $group = ModifierGroup::findOrFail($id);

        // Verify ownership through product
        if ($group->product && $group->product->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Unauthorized action!');
            return;
        }

        $this->groupId = $group->id;
        $this->groupName = $group->name;
        $this->product_id = $group->product_id;
        $this->is_required = $group->is_required;
        $this->max_selections = $group->max_selections;
        $this->group_sort_order = $group->sort_order;
        $this->isEditingGroup = true;
    }

    public function deleteGroup($id)
    {
        $group = ModifierGroup::findOrFail($id);

        // Verify ownership through product
        if ($group->product && $group->product->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Unauthorized action!');
            return;
        }

        // Check if group has modifiers
        if ($group->modifiers()->count() > 0) {
            session()->flash('error', 'Hapus semua modifier terlebih dahulu sebelum menghapus group!');
            return;
        }

        $group->delete();

        if ($this->selectedGroupId === $id) {
            $this->selectedGroupId = null;
        }

        session()->flash('message', 'Modifier group berhasil dihapus!');
    }

    public function duplicateGroup($id)
    {
        $group = ModifierGroup::with('modifiers')->findOrFail($id);

        // Verify ownership
        if ($group->product && $group->product->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Unauthorized action!');
            return;
        }

        DB::beginTransaction();
        try {
            // Create new group
            $newGroup = ModifierGroup::create([
                'name' => $group->name . ' (Copy)',
                'product_id' => $group->product_id,
                'is_required' => $group->is_required,
                'max_selections' => $group->max_selections,
                'sort_order' => $group->sort_order,
            ]);

            // Copy modifiers
            foreach ($group->modifiers as $modifier) {
                Modifier::create([
                    'modifier_group_id' => $newGroup->id,
                    'name' => $modifier->name,
                    'price' => $modifier->price,
                    'sort_order' => $modifier->sort_order,
                ]);
            }

            DB::commit();
            session()->flash('message', 'Modifier group berhasil diduplikasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menduplikasi group: ' . $e->getMessage());
        }
    }

    public function selectGroup($id)
    {
        $this->selectedGroupId = $id;
        $this->showModifierForm = false;
        $this->resetModifierForm();
    }

    public function cancelGroupEdit()
    {
        $this->resetGroupForm();
    }

    // ==================== MODIFIER METHODS ====================

    public function showAddModifier($groupId)
    {
        $this->selectedGroupId = $groupId;
        $this->showModifierForm = true;
        $this->resetModifierForm();
    }

    public function saveModifier()
    {
        $this->validate($this->modifierRules());

        if (!$this->selectedGroupId) {
            session()->flash('error', 'Pilih modifier group terlebih dahulu!');
            return;
        }

        // Verify group ownership
        $group = ModifierGroup::with('product')->findOrFail($this->selectedGroupId);
        if ($group->product && $group->product->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Unauthorized action!');
            return;
        }

        $data = [
            'modifier_group_id' => $this->selectedGroupId,
            'name' => $this->modifierName,
            'price' => $this->modifierPrice,
            'sort_order' => $this->modifier_sort_order,
        ];

        if ($this->modifierId) {
            $modifier = Modifier::where('id', $this->modifierId)
                ->where('modifier_group_id', $this->selectedGroupId)
                ->firstOrFail();
            
            $modifier->update($data);
            session()->flash('message', 'Modifier berhasil diupdate!');
        } else {
            Modifier::create($data);
            session()->flash('message', 'Modifier berhasil ditambahkan!');
        }

        $this->resetModifierForm();
        $this->showModifierForm = false;
    }

    public function editModifier($id)
    {
        $modifier = Modifier::with('group.product')->findOrFail($id);

        // Verify ownership
        if ($modifier->group->product && $modifier->group->product->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Unauthorized action!');
            return;
        }

        $this->modifierId = $modifier->id;
        $this->modifierName = $modifier->name;
        $this->modifierPrice = $modifier->price;
        $this->modifier_sort_order = $modifier->sort_order;
        $this->isEditingModifier = true;
        $this->showModifierForm = true;
        $this->selectedGroupId = $modifier->modifier_group_id;
    }

    public function deleteModifier($id)
    {
        $modifier = Modifier::with('group.product')->findOrFail($id);

        // Verify ownership
        if ($modifier->group->product && $modifier->group->product->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Unauthorized action!');
            return;
        }

        $modifier->delete();
        session()->flash('message', 'Modifier berhasil dihapus!');
    }

    public function cancelModifierEdit()
    {
        $this->resetModifierForm();
        $this->showModifierForm = false;
    }

    // ==================== HELPER METHODS ====================

    private function resetGroupForm()
    {
        $this->reset([
            'groupId', 'groupName', 'product_id', 'is_required',
            'max_selections', 'group_sort_order', 'isEditingGroup'
        ]);
        $this->is_required = false;
        $this->group_sort_order = 0;
    }

    private function resetModifierForm()
    {
        $this->reset([
            'modifierId', 'modifierName', 'modifierPrice',
            'modifier_sort_order', 'isEditingModifier'
        ]);
        $this->modifierPrice = 0;
        $this->modifier_sort_order = 0;
    }

    public function getGroupFormTitleProperty()
    {
        return $this->isEditingGroup ? 'Edit Modifier Group' : 'Tambah Modifier Group';
    }

    public function getModifierFormTitleProperty()
    {
        return $this->isEditingModifier ? 'Edit Modifier' : 'Tambah Modifier';
    }
}