<?php
namespace App\Livewire\Components;


use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Livewire\Component;
use Livewire\WithFileUploads;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ImageUploader extends Component
{
    use WithFileUploads;


    public $images = [];
    public $previews = [];
    public $progress = [];


    public $folder = 'uploads';
    public $value; // wire:model binding for parent
    public $multiple = true;


    protected $rules = [
        'images.*' => 'image|max:5120',
    ];


    protected $listeners = ['removeExistingImage'];


    public function mount($folder = null, $value = null)
    {
        if($folder) {
            $this->folder = trim($folder, '/');
        }


        if($value !== null) {
            $this->value = $value;
            if(is_string($value)) {
                $this->multiple = false;
                $this->images = [$value];
                $this->previews = [$value];
            } elseif(is_array($value)) {
                $this->multiple = true;
                $this->images = $value;
                $this->previews = $value;
            }
        }
    }


    public function updatedImages()
    {
        $this->validate();
        $this->generatePreviews();
    }


    protected function generatePreviews()
    {
//        $manager = new ImageManager('imagick');
        $manager = new ImageManager(new GdDriver());
        $newPreviews = [];


        foreach ($this->images as $index => $image) {
            if(is_string($image)) {
                $newPreviews[] = $image; // existing image
                continue;
            }


            $temp = $manager->read($image->getRealPath())
                            ->scale(width: 600)
                            ->toWebp(quality: 75);


            $filename = 'tmp/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($filename, $temp);


            $newPreviews[] = Storage::url($filename);
            $this->progress[$index] = 0;
        }


        $this->previews = $newPreviews;
    }


    public function removePreview($index)
    {
        if (isset($this->previews[$index])) {
            $path = str_replace('/storage/', '', $this->previews[$index]);
            Storage::disk('public')->delete($path);
        }


        unset($this->previews[$index]);
        unset($this->images[$index]);
        unset($this->progress[$index]);


        $this->previews = array_values($this->previews);
        $this->images = array_values($this->images);
        $this->progress = array_values($this->progress);


        $this->value = $this->multiple ? $this->images : ($this->images[0] ?? null);


// emit to parent to auto-save deletion
        $this->emitUp('imageUpdated', $this->value);
    }


    public function removeExistingImage($url)
    {
        if(($key = array_search($url, $this->previews)) !== false) {
            $this->removePreview($key);
        }
    }


    public function save($folder = null)
    {
        if ($folder) {
            $this->folder = trim($folder, '/');
        }
        $this->validate();


        $manager = new ImageManager(['driver' => 'gd']);
        $saved = [];


        foreach ($this->images as $index => $image) {
            if(is_string($image)) {
                $saved[] = $image; // existing image
                continue;
            }


            $processed = $manager->read($image->getRealPath())
                                 ->scale(width: 1800)
                                 ->toWebp(quality: 80);


            $filename = $this->folder . '/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($filename, $processed);


            $this->progress[$index] = 100;


            $saved[] = Storage::url($filename);
        }


        $this->value = $this->multiple ? $saved : ($saved[0] ?? null);


// emit to parent for auto-save
        $this->emitUp('imageUpdated', $this->value);
    }


    public function render()
    {
        return view('livewire.components.image-uploader');
    }
}
