<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryManager extends Component
{
    use WithFileUploads;

    // Form Properties
    public $categoryId;
    public $name;
    public $description;
    public $parent_id;
    public $image;
    public $currentImage;
    public $sort_order = 0;
    public $is_active = true;
    public $isEditing = false;

    // UI Properties
    public $search = '';
    public $showInactive = false;

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'description' => 'nullable|max:500',
        'parent_id' => 'nullable|exists:categories,id',
        'image' => 'nullable|image|max:2048', // 2MB max
        'sort_order' => 'integer|min:0',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi',
        'name.min' => 'Nama kategori minimal 2 karakter',
        'image.image' => 'File harus berupa gambar',
        'image.max' => 'Ukuran gambar maksimal 2MB',
    ];

    public function render()
    {
        $user = auth()->user();
        
        // Build query
        $query = Category::where('tenant_id', $user->tenant_id)
            ->with('parent');

        // Apply filters
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if (!$this->showInactive) {
            $query->where('is_active', true);
        }

        // Get categories with parent relationship
        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get parent category options (exclude current category to prevent circular reference)
        $parentOptions = Category::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->when($this->categoryId, function($q) {
                $q->where('id', '!=', $this->categoryId);
            })
            ->orderBy('name')
            ->get();

        return view('livewire.category-manager', [
            'categories' => $categories,
            'parentOptions' => $parentOptions
        ]);
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        // Prepare data
        $data = [
            'tenant_id' => $user->tenant_id,
            'name' => $this->name,
            'slug' => Str::slug($this->name) . '-' . Str::random(5),
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        // Handle image upload
        if ($this->image) {
            // Delete old image if editing
            if ($this->isEditing && $this->currentImage) {
                Storage::disk('public')->delete($this->currentImage);
            }
            
            $data['image_url'] = $this->image->store('categories', 'public');
        }

        // Save or update
        if ($this->categoryId) {
            $category = Category::where('id', $this->categoryId)
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();
            
            // Don't update slug and tenant_id
            unset($data['slug'], $data['tenant_id']);
            
            $category->update($data);
            session()->flash('message', 'Kategori berhasil diupdate!');
        } else {
            Category::create($data);
            session()->flash('message', 'Kategori berhasil ditambahkan!');
        }

        $this->resetForm();
    }

    public function edit($id)
    {
        $category = Category::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->parent_id = $category->parent_id;
        $this->currentImage = $category->image_url;
        $this->sort_order = $category->sort_order;
        $this->is_active = $category->is_active;
        $this->isEditing = true;
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function delete($id)
    {
        $category = Category::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        // Check if category has products
        if ($category->products()->count() > 0) {
            session()->flash('error', 'Kategori tidak dapat dihapus karena masih memiliki produk!');
            return;
        }

        // Check if category has child categories
        $hasChildren = Category::where('parent_id', $id)->exists();
        if ($hasChildren) {
            session()->flash('error', 'Kategori tidak dapat dihapus karena masih memiliki sub-kategori!');
            return;
        }

        // Delete image if exists
        if ($category->image_url) {
            Storage::disk('public')->delete($category->image_url);
        }

        $category->delete();
        session()->flash('message', 'Kategori berhasil dihapus!');
    }

    public function toggleStatus($id)
    {
        $category = Category::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $category->update(['is_active' => !$category->is_active]);
        
        session()->flash('message', 'Status kategori berhasil diubah!');
    }

    public function removeImage()
    {
        if ($this->currentImage) {
            Storage::disk('public')->delete($this->currentImage);
            
            if ($this->categoryId) {
                $category = Category::find($this->categoryId);
                $category->update(['image_url' => null]);
            }
            
            $this->currentImage = null;
            session()->flash('message', 'Gambar berhasil dihapus!');
        }
    }

    private function resetForm()
    {
        $this->reset([
            'categoryId', 'name', 'description', 'parent_id',
            'image', 'currentImage', 'sort_order', 'isEditing'
        ]);
        $this->is_active = true;
        $this->sort_order = 0;
    }

    // Computed property for form title
    public function getFormTitleProperty()
    {
        return $this->isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru';
    }
}