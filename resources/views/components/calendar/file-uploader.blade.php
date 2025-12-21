@props(['tempPhotos', 'existingImages', 'photos', 'uploadIteration'])

<div class="space-y-2 rounded-xl bg-gray-50 p-3 dark:bg-gray-900">
    <div class="flex items-center justify-between">
        <label class="text-xs font-bold text-gray-500">Attachments (Images & Files)</label>
    </div>
    <div class="flex flex-col gap-3">
        <input
            type="file"
            wire:model="temp_photos"
            id="upload-{{ $uploadIteration }}"
            multiple
            class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 dark:file:bg-gray-800 dark:file:text-purple-400"
        >

        @if(count($existingImages) > 0 || count($photos) > 0)
            <div class="grid grid-cols-4 gap-2">
                @foreach($existingImages as $index => $url)
                    <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                        @php $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION)); @endphp
                        @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                            <img src="{{ $url }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center w-full h-full bg-gray-100 text-xs font-bold uppercase text-gray-500">{{ $ext }}</div>
                        @endif
                        <button type="button" wire:click="removeExistingImage({{ $index }})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><x-heroicon-o-x-mark class="w-3 h-3" /></button>
                    </div>
                @endforeach
                @foreach($photos as $index => $photo)
                    <div class="relative group aspect-square rounded-lg overflow-hidden border border-purple-200 ring-2 ring-purple-400">
                        @if(in_array($photo->guessExtension(), ['jpg','jpeg','png','gif','webp']))
                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover opacity-80">
                        @else
                            <div class="flex items-center justify-center w-full h-full bg-gray-100 text-xs font-bold uppercase text-gray-500">{{ $photo->guessExtension() }}</div>
                        @endif
                        <button type="button" wire:click="removePhoto({{ $index }})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><x-heroicon-o-x-mark class="w-3 h-3" /></button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
