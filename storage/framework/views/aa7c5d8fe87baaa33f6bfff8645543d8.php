<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'wireModel' => 'images',
    'maxFiles' => 5,
    'maxSize' => 5120, // KB
    'preview' => true,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'wireModel' => 'images',
    'maxFiles' => 5,
    'maxSize' => 5120, // KB
    'preview' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="imageUploader()" class="image-uploader">
    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
        <input 
            type="file" 
            wire:model="<?php echo e($wireModel); ?>"
            accept="image/*"
            multiple
            max="<?php echo e($maxFiles); ?>"
            class="hidden"
            x-ref="fileInput"
            @change="handleFiles($event)">
        
        <div class="space-y-4">
            <div class="flex justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            
            <div>
                <button 
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                    Choose Images
                </button>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    or drag and drop images here
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    PNG, JPG, GIF up to <?php echo e($maxSize / 1024); ?>MB (max <?php echo e($maxFiles); ?> files)
                </p>
            </div>
        </div>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($preview): ?>
        <div x-show="previews.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <template x-for="(preview, index) in previews" :key="index">
                <div class="relative group">
                    <img :src="preview" class="w-full h-32 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700">
                    <button 
                        type="button"
                        @click="removeImage(index)"
                        class="absolute top-2 right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>

<script>
function imageUploader() {
    return {
        previews: [],
        
        handleFiles(event) {
            const files = event.target.files;
            this.previews = [];
            
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews.push(e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
        },
        
        removeImage(index) {
            this.previews.splice(index, 1);
        }
    }
}
</script>
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/components/image-uploader.blade.php ENDPATH**/ ?>