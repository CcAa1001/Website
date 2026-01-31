<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $categoryId, $name, $description, $parent_id, $image, $currentImage;
    public $sort_order = 0;
    public $is_active = true;
    
    // UI States
    public $search = '';
    public $showModal = false;
    public $isEditing = false;
    
    // State untuk Accordion (Menyimpan ID kategori yang sedang terbuka)
    public $expanded = []; 

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function render()
    {
        $user = auth()->user();
        
        // Ambil hanya kategori Induk (Level 0)
        $query = Category::where('tenant_id', $user->tenant_id)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('sort_order')->orderBy('name');
            }, 'products']); // Eager load children & products count

        if ($this->search) {
            // Jika search aktif, kita cari di semua level, struktur tree mungkin sedikit berubah
            // tapi untuk UX search biasanya kita tampilkan list flat atau filter parent
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('children', function($sub) {
                      $sub->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(10);

        // Opsi untuk Dropdown Parent di Modal
        $parentOptions = Category::where('tenant_id', $user->tenant_id)
            ->whereNull('parent_id')
            ->when($this->categoryId, fn($q) => $q->where('id', '!=', $this->categoryId))
            ->orderBy('name')
            ->get();

        return view('livewire.category-manager', [
            'categories' => $categories,
            'parentOptions' => $parentOptions
        ])->layout('layouts.app', ['activePage' => 'products', 'titlePage' => 'Kategori Produk']);
    }

    // --- LOGIC BARU: ACCORDION ---
    public function toggleExpand($id)
    {
        if (in_array($id, $this->expanded)) {
            $this->expanded = array_diff($this->expanded, [$id]); // Tutup
        } else {
            $this->expanded[] = $id; // Buka
        }
    }

    // --- LOGIC BARU: ADD CHILD SHORTCUT ---
    public function addChild($parentId)
    {
        $this->resetForm();
        $this->parent_id = $parentId; // Otomatis set Bapaknya
        $this->isEditing = false;
        $this->showModal = true;
    }

    // --- STANDARD CRUD ACTIONS ---

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $cat = Category::findOrFail($id);
        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->description = $cat->description;
        $this->parent_id = $cat->parent_id;
        $this->currentImage = $cat->image_url;
        $this->sort_order = $cat->sort_order;
        $this->is_active = $cat->is_active;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'min:2', Rule::unique('categories')->ignore($this->categoryId)->where('tenant_id', auth()->user()->tenant_id)],
            'image' => 'nullable|image|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        if ($this->categoryId && $this->parent_id == $this->categoryId) {
            $this->addError('parent_id', 'Circular dependency detected.'); return;
        }

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'parent_id' => $this->parent_id ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->image) {
            if ($this->isEditing && $this->currentImage) Storage::disk('public')->delete($this->currentImage);
            $data['image_url'] = $this->image->store('categories', 'public');
        }

        if ($this->categoryId) {
            Category::find($this->categoryId)->update($data);
            
            // Jika kita mengedit anak, pastikan parentnya terbuka di UI agar user lihat perubahannya
            if($this->parent_id && !in_array($this->parent_id, $this->expanded)){
                $this->expanded[] = $this->parent_id;
            }
            
            session()->flash('message', 'Kategori diperbarui.');
        } else {
            Category::create($data);
            
            // Jika menambah anak, otomatis expand bapaknya
            if($this->parent_id && !in_array($this->parent_id, $this->expanded)){
                $this->expanded[] = $this->parent_id;
            }

            session()->flash('message', 'Kategori dibuat.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $cat = Category::with('products', 'children')->findOrFail($id);
        
        if($cat->children->count() > 0) {
            session()->flash('error', 'Hapus sub-kategori di dalamnya terlebih dahulu.'); return;
        }
        if($cat->products->count() > 0) {
            session()->flash('error', 'Pindahkan produk dari kategori ini sebelum menghapus.'); return;
        }

        if ($cat->image_url) Storage::disk('public')->delete($cat->image_url);
        $cat->delete();
        session()->flash('message', 'Kategori dihapus.');
    }

    public function closeModal() { $this->showModal = false; $this->resetForm(); }
    
    public function removeImage() {
        if ($this->currentImage) {
            Storage::disk('public')->delete($this->currentImage);
            if ($this->categoryId) Category::find($this->categoryId)->update(['image_url' => null]);
            $this->currentImage = null;
        }
    }

    private function resetForm() {
        $this->reset(['categoryId', 'name', 'description', 'parent_id', 'image', 'currentImage', 'sort_order', 'isEditing']);
        $this->is_active = true;
    }
}