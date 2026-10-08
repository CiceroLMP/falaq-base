<button
    type="submit"
    {{ $attributes->merge([
        'class' => 'bg-red-600 text-white px-4 py-2 rounded-md! hover:bg-red-700'
    ]) }}
>
    {{ $slot }}
</button>